<?php
$servername = "sql209.byethost7.com";
$username = "b7_37938808"; // Ganti dengan username database Anda
$password = "752004aff"; // Ganti dengan password database Anda
$dbname = "b7_37938808_konselingdb"; // Ganti dengan nama database Anda

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
