<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Listing.php';

// Verified seller check
if(!isset($_SESSION['user_id']) || !isset($_SESSION['seller_verified']) || $_SESSION['seller_verified'] != 1){
    header('Location: ../views/login.php');
    exit;
}

$listing = new Listing($conn);
$action = $_GET['action'] ?? 'create';

switch($action){

    // ================= CREATE LISTING =================
    case 'create':
        $errors = [];

        // Load categories for dropdown
        $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $title = $_POST['title'] ?? '';
            $desc = $_POST['description'] ?? '';
            $start_price = $_POST['starting_price'] ?? 0;
            $reserve_price = $_POST['reserve_price'] ?? 0;
            $end_datetime = $_POST['end_datetime'] ?? '';
            $image_path = null;

            // ================= IMAGE UPLOAD =================
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg','jpeg','png'];
                if(!in_array(strtolower($ext), $allowed)) $errors[] = "Invalid image type (jpg, jpeg, png only).";
                if($_FILES['image']['size'] > 3*1024*1024) $errors[] = "File too large (max 3MB).";

                if(empty($errors)){
                    $uploadDir = __DIR__ . '/../public/uploads/listings/';
                    if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $fileName = uniqid('listing_', true) . '.' . strtolower($ext);
                    $target = $uploadDir . $fileName;
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
                        $image_path = 'uploads/listings/' . $fileName;
                    } else {
                        $errors[] = "Image upload failed.";
                    }
                }
            }

            // ================= CATEGORY HANDLING =================
            $category_id_input = $_POST['category_id'] ?? '';
            $new_category = trim($_POST['new_category'] ?? '');
            $category_id = 0;

            if($category_id_input === 'new'){
                if($new_category==='') $errors[] = "Please write a new category name";
                else {
                    // Check if exists
                    $stmt = $conn->prepare("SELECT id FROM categories WHERE name=?");
                    $stmt->bind_param("s",$new_category);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if($res->num_rows>0){
                        $category_id = $res->fetch_assoc()['id'];
                    } else {
                        // Insert new category
                        $stmt = $conn->prepare("INSERT INTO categories(name) VALUES(?)");
                        $stmt->bind_param("s",$new_category);
                        $stmt->execute();
                        $category_id = $conn->insert_id;
                    }
                }
            } else {
                $category_id = (int)$category_id_input;
                if($category_id<=0) $errors[]="Please select a category";
            }

            // ================= INSERT LISTING =================
            if(empty($errors)){
                $listing->create(
                    $_SESSION['user_id'],
                    $category_id,
                    $title,
                    $desc,
                    $start_price,
                    $reserve_price ?: $start_price,
                    $image_path,
                    $end_datetime
                );

                // Redirect to dashboard after successful creation
                header('Location: ListingController.php?action=dashboard');
                exit;
            }
        }

        include __DIR__ . '/../views/create_listing.php';
        break;

    // ================= SELLER DASHBOARD =================
    case 'dashboard':
        // Fetch auctions + bid counts
        $stmt = $conn->prepare("
    SELECT a.*, c.name as category_name, 
           (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.auction_id) as bid_count
    FROM auctions a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.seller_id = ?
    ORDER BY a.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        include __DIR__ . '/../views/seller_dashboard.php';
        break;

    // ================= CANCEL LISTING =================
    case 'cancel':
        $auction_id = (int)($_GET['id'] ?? 0);

        // Check if bids exist
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM bids WHERE auction_id=?");
        $stmt->bind_param("i",$auction_id); $stmt->execute();
        $bid_count = $stmt->get_result()->fetch_assoc()['cnt'];

        if($bid_count == 0){
            $stmt = $conn->prepare("UPDATE auctions SET status='cancelled' WHERE auction_id=?");
            $stmt->bind_param("i",$auction_id); $stmt->execute();
        }

        header('Location: ListingController.php?action=dashboard');
        exit;
        break;

    default:
        header('Location: ListingController.php?action=create');
        exit;
}