<?php
session_start();

// Hapus sesi
session_unset();
session_destroy();

// Hapus cookie
setcookie('username', '', time() - 3600, "/");
setcookie('role', '', time() - 3600, "/");
setcookie('nisn', '', time() - 3600, "/");

// Redirect ke halaman dashboard
header("Location: index.html");
exit();
?>
