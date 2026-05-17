<?php
$isEdit = isset($mode) && $mode === 'edit';
$pageTitle = $isEdit ? 'Edit Auction Listing' : 'Create New Auction Listing';

$titleValue = $isEdit ? ($auction['title'] ?? '') : '';
$descriptionValue = $isEdit ? ($auction['description'] ?? '') : '';
$categoryValue = $isEdit ? (int)($auction['category_id'] ?? 0) : '';
$startingPriceValue = $isEdit ? ($auction['starting_price'] ?? '') : '';
$endTimeValue = $isEdit && !empty($auction['end_time'])
    ? date('Y-m-d\TH:i', strtotime($auction['end_time']))
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
body {
    font-family: Arial;
    background: #f5f5f5;
    padding: 20px;
}
.container {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
input, select, textarea {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}
textarea {
    resize: vertical;
}
button {
    margin-top: 20px;
    width: 100%;
    padding: 10px;
    background: #007BFF;
    color: #fff;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: #0056b3;
}
.error {
    color: red;
    margin-top: 10px;
}
.note {
    background: #fff3cd;
    color: #856404;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.back-link {
    display: inline-block;
    margin-bottom: 15px;
    text-decoration: none;
    color: #007BFF;
}
</style>
</head>
<body>

<div class="container">
    <a href="SellerDashboardController.php" class="back-link">← Back to Seller Dashboard</a>

    <h2><?= htmlspecialchars($pageTitle) ?></h2>

    <?php if ($isEdit): ?>
        <div class="note">
            Only title, description, and image can be changed. If this auction has any bid, editing is blocked.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Title</label>
        <input 
            type="text" 
            name="title" 
            value="<?= htmlspecialchars($titleValue) ?>" 
            required
        >

        <label>Description</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($descriptionValue) ?></textarea>

        <?php if (!$isEdit): ?>
            <label>Category</label>
            <select name="category_id" id="category" required onchange="toggleNewCategory(this)">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>">
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
                <option value="new">Other (Write your own)</option>
            </select>

            <input 
                type="text" 
                name="new_category" 
                id="new_category" 
                placeholder="Enter new category" 
                style="display:none; margin-top:5px;"
            >

            <label>Starting Price</label>
            <input 
                type="number" 
                name="starting_price" 
                min="1" 
                step="0.01" 
                required
            >

            <?php if (!empty($has_reserve_price)): ?>
                <label>Reserve Price (Optional)</label>
                <input 
                    type="number" 
                    name="reserve_price" 
                    min="1" 
                    step="0.01" 
                    placeholder="Must be equal to or higher than starting price if used"
                >
            <?php endif; ?>

            <label>End Date & Time</label>
            <input 
                type="datetime-local" 
                name="end_time" 
                required
            >
        <?php else: ?>
            <label>Category</label>
            <input 
                type="text" 
                value="<?= htmlspecialchars($auction['category_name'] ?? 'Uncategorized') ?>" 
                disabled
            >

            <label>Starting Price</label>
            <input 
                type="text" 
                value="<?= number_format((float)$startingPriceValue, 2) ?>" 
                disabled
            >

            <label>End Date & Time</label>
            <input 
                type="datetime-local" 
                value="<?= htmlspecialchars($endTimeValue) ?>" 
                disabled
            >
        <?php endif; ?>

        <label>Upload Image (JPEG/PNG ≤ 3MB)</label>
        <input type="file" name="image">

        <button type="submit">
            <?= $isEdit ? 'Update Listing' : 'Create Listing' ?>
        </button>
    </form>
</div>

<script>
function toggleNewCategory(sel) {
    const input = document.getElementById('new_category');

    if (sel.value === 'new') {
        input.style.display = 'block';
        input.required = true;
    } else {
        input.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
</script>

</body>
</html>