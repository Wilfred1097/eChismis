<?php
// Start the session
session_start();

// Destroy the session to log out the user
session_destroy();

// Delete the user_id cookie by setting its expiration time to a past date
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/'); // Expire the cookie
}

// Redirect to the login page
header("Location: login.php");
exit();
?>
