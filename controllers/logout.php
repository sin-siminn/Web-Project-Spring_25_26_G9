<?php
// 1. Identify the current session
session_start();

// 2. Remove all session variables
session_unset();

// 3. Destroy the session entirely
session_destroy();

// 4. Redirect the user back to the login page
header("Location: ../views/login.php");
exit();
?>