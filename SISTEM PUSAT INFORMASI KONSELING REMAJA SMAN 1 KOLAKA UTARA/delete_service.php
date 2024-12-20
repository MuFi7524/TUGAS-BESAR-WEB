<?php
session_start();
include('db_connection.php'); // Koneksi ke database

// Periksa apakah cookie masih ada
if (!isset($_COOKIE['username']) || !isset($_COOKIE['role']) || $_COOKIE['role'] !== 'admin') {
    // Hapus sesi jika cookie habis
    session_unset();
    session_destroy();

    // Redirect ke halaman login
    header('Location: login.php');
    exit();
}

// Sinkronisasi cookie dengan session jika cookie masih valid
if (isset($_COOKIE['username']) && isset($_COOKIE['role'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['role'] = $_COOKIE['role'];
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();
header('Location: admin_dashboard.php');
exit();
?>
