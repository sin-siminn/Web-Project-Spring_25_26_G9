<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create New Auction Listing</title>
<style>
body { font-family: Arial; background: #f5f5f5; padding:20px; }
.container { max-width:600px; margin:0 auto; background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; }
label { display:block; margin-top:15px; font-weight:bold; }
input, select, textarea { width:100%; padding:8px; margin-top:5px; border-radius:4px; border:1px solid #ccc; box-sizing:border-box; }
textarea { resize: vertical; }
button { margin-top:20px; width:100%; padding:10px; background:#007BFF; color:#fff; font-size:16px; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
.error { color:red; margin-top:10px; }
</style>
</head>
<body>
<div class="container">
<h2>Create New Auction Listing</h2>

<?php if(!empty($errors)): ?>
<div class="error">
<?php foreach($errors as $err) echo "<p>$err</p>"; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<label>Title</label>
<input type="text" name="title" required>

<label>Description</label>
<textarea name="description" rows="4" required></textarea>

<label>Category</label>
<select name="category_id" id="category" required onchange="toggleNewCategory(this)">
<option value="">-- Select Category --</option>
<?php foreach($categories as $c): ?>
<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
<?php endforeach; ?>
<option value="new">Other (Write your own)</option>
</select>
<input type="text" name="new_category" id="new_category" placeholder="Enter new category" style="display:none; margin-top:5px;">

<label>Starting Price</label>
<input type="number" name="starting_price" min="0" required>

<label>Reserve Price (optional)</label>
<input type="number" name="reserve_price" min="0">

<label>Upload Image (JPEG/PNG ≤ 3MB)</label>
<input type="file" name="image">

<label>End Date & Time</label>
<input type="datetime-local" name="end_datetime" required>

<button type="submit">Create Listing</button>
</form>
</div>

<script>
function toggleNewCategory(sel) {
    const input = document.getElementById('new_category');
    if(sel.value==='new'){ input.style.display='block'; input.required=true; }
    else{ input.style.display='none'; input.required=false; input.value=''; }
}
</script>
</body>
</html>