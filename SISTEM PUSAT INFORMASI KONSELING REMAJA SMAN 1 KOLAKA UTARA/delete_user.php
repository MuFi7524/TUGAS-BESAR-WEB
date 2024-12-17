<?php
session_start();
// Cek apakah admin sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'konselingdb');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
