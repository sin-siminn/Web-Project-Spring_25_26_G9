<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

// Verified seller check
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['seller_verified']) ||
    (int)$_SESSION['seller_verified'] !== 1
) {
    header('Location: ../views/login.php');
    exit;
}

// Student 4: close expired auctions before seller creates/edits auction data.
close_expired_auctions($conn);

$seller_id = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? 'create';

switch ($action) {

    // ================= CREATE AUCTION =================
    case 'create':
        $errors = [];
        $mode = 'create';
        $auction = null;

        $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
        $has_reserve_price = auction_column_exists($conn, 'auctions', 'reserve_price');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $new_category = trim($_POST['new_category'] ?? '');
            $starting_price = (float)($_POST['starting_price'] ?? 0);
            $reserve_price_raw = trim((string)($_POST['reserve_price'] ?? ''));
            $reserve_price = $reserve_price_raw === '' ? null : (float)$reserve_price_raw;
            $end_time = $_POST['end_time'] ?? '';

            if ($title === '') {
                $errors[] = 'Title is required.';
            }

            if ($description === '') {
                $errors[] = 'Description is required.';
            }

            if ($starting_price <= 0) {
                $errors[] = 'Starting price must be positive.';
            }

            if ($has_reserve_price && $reserve_price !== null && $reserve_price < $starting_price) {
                $errors[] = 'Reserve price must be equal to or higher than the starting price.';
            }

            if ($end_time === '') {
                $errors[] = 'End date and time is required.';
            } else {
                $end_timestamp = strtotime($end_time);
                if ($end_timestamp === false || $end_timestamp < time() + 3600) {
                    $errors[] = 'End date and time must be at least 1 hour from now.';
                }
            }

            // Category handling
            if (($_POST['category_id'] ?? '') === 'new') {
                if ($new_category === '') {
                    $errors[] = 'Please write a new category name.';
                } else {
                    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
                    $stmt->bind_param("s", $new_category);
                    $stmt->execute();
                    $existing = $stmt->get_result()->fetch_assoc();

                    if ($existing) {
                        $category_id = (int)$existing['id'];
                    } else {
                        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                        $stmt->bind_param("s", $new_category);
                        $stmt->execute();
                        $category_id = (int)$conn->insert_id;
                    }
                }
            } elseif ($category_id <= 0) {
                $errors[] = 'Please select a category.';
            }

            // Image validation only. Your current SQL has no image_path column.
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Invalid image type. Only JPG, JPEG, and PNG are allowed.';
                }

                if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                    $errors[] = 'Image size must be 3 MB or less.';
                }

                if (empty($errors)) {
                    $uploadDir = __DIR__ . '/../public/uploads/listings/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid('auction_', true) . '.' . $ext;
                    $target = $uploadDir . $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], $target);
                }
            }

            if (empty($errors)) {
                $current_price = $starting_price;

                if ($has_reserve_price) {
                    $stmt = $conn->prepare("
                        INSERT INTO auctions
                        (seller_id, title, description, starting_price, reserve_price, current_price, end_time, status, created_at, category_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), ?)
                    ");

                    $stmt->bind_param(
                        "issdddsi",
                        $seller_id,
                        $title,
                        $description,
                        $starting_price,
                        $reserve_price,
                        $current_price,
                        $end_time,
                        $category_id
                    );
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO auctions
                        (seller_id, title, description, starting_price, current_price, end_time, status, created_at, category_id)
                        VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), ?)
                    ");

                    $stmt->bind_param(
                        "issddsi",
                        $seller_id,
                        $title,
                        $description,
                        $starting_price,
                        $current_price,
                        $end_time,
                        $category_id
                    );
                }

                $stmt->execute();

                header('Location: SellerDashboardController.php');
                exit;
            }
        }

        include __DIR__ . '/../views/create_listing.php';
        break;


    // ================= EDIT AUCTION =================
    case 'edit':
        $errors = [];
        $mode = 'edit';
        $auction_id = (int)($_GET['id'] ?? 0);

        if ($auction_id <= 0) {
            header('Location: SellerDashboardController.php');
            exit;
        }

        $stmt = $conn->prepare("
            SELECT a.*, c.name AS category_name
            FROM auctions a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.auction_id = ? AND a.seller_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $auction_id, $seller_id);
        $stmt->execute();
        $auction = $stmt->get_result()->fetch_assoc();

        if (!$auction) {
            header('Location: SellerDashboardController.php');
            exit;
        }

        $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
        $has_reserve_price = auction_column_exists($conn, 'auctions', 'reserve_price');

        // Check if bids exist
        $stmt = $conn->prepare("SELECT COUNT(*) AS bid_count FROM bids WHERE auction_id = ?");
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $bid_count = (int)$stmt->get_result()->fetch_assoc()['bid_count'];

        if ($bid_count > 0 || $auction['status'] !== 'active') {
            $errors[] = 'This auction cannot be edited because it already has bids or is no longer active.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($title === '') {
                $errors[] = 'Title is required.';
            }

            if ($description === '') {
                $errors[] = 'Description is required.';
            }

            // Image validation only. Your current SQL has no image_path column.
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Invalid image type. Only JPG, JPEG, and PNG are allowed.';
                }

                if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                    $errors[] = 'Image size must be 3 MB or less.';
                }

                if (empty($errors)) {
                    $uploadDir = __DIR__ . '/../public/uploads/listings/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid('auction_', true) . '.' . $ext;
                    $target = $uploadDir . $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], $target);
                }
            }

            if ($bid_count === 0 && $auction['status'] === 'active' && empty($errors)) {
                $stmt = $conn->prepare("
                    UPDATE auctions
                    SET title = ?, description = ?
                    WHERE auction_id = ? AND seller_id = ?
                ");
                $stmt->bind_param("ssii", $title, $description, $auction_id, $seller_id);
                $stmt->execute();

                header('Location: SellerDashboardController.php');
                exit;
            }
        }

        include __DIR__ . '/../views/create_listing.php';
        break;


    default:
        header('Location: ListingController.php?action=create');
        exit;
}