<!DOCTYPE html>
<html>
<head>
<title>Category Management</title>
<style>
body{font-family:Arial;background:#f5f5f5;padding:20px;}
.container{max-width:600px;margin:auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,0.1);}
table{width:100%;border-collapse:collapse;}
th,td{padding:8px;border:1px solid #ccc;text-align:left;}
th{background:#007BFF;color:#fff;}
form{margin-bottom:15px;}
input[type=text]{width:80%;padding:5px;}
button{padding:5px 10px;margin-left:2px;}
</style>
</head>
<body>
<div class="container">
<h2>Category Management (Admin)</h2>

<form method="POST" action="CategoryController.php?action=create">
<input type="text" name="name" placeholder="New category" required>
<button type="submit">Add</button>
</form>

<table>
<tr><th>ID</th><th>Name</th><th>Actions</th></tr>
<?php foreach($categories as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= htmlspecialchars($c['name']) ?></td>
<td>
<form method="POST" action="CategoryController.php?action=edit&id=<?= $c['id'] ?>" style="display:inline">
<input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required>
<button type="submit">Edit</button>
</form>
<a href="CategoryController.php?action=delete&id=<?= $c['id'] ?>" onclick="return confirm('Delete?');">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body>
</html>