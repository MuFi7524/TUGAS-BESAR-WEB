<?php
session_start();
include('db_connection.php'); // Koneksi ke database

// Periksa sesi atau cookie
if ((!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') &&
    (!isset($_COOKIE['username']) || $_COOKIE['role'] !== 'admin')) {
    header('Location: login.php');
    exit();
}

// Jika cookie ada, sinkronkan dengan session
if (isset($_COOKIE['username']) && isset($_COOKIE['role'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['role'] = $_COOKIE['role'];
    $_SESSION['nisn'] = $_COOKIE['nisn'];
}

// Ambil data user berdasarkan id
if (isset($_GET['nisn'])) {
    $nisn = $_GET['nisn'];
    
    // Hapus data siswa dari tabel students
    $sql = "DELETE FROM students WHERE nisn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $nisn);
    
    if ($stmt->execute()) {
        // Data siswa berhasil dihapus, data pada tabel users juga akan terhapus otomatis
        echo "Data siswa berhasil dihapus.";
        header('Location: manage_users.php');  // Redirect kembali ke halaman pengguna
        exit();
    } else {
        echo "Gagal menghapus data siswa.";
    }
}


$conn->close();
?>
