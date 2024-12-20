<?php
session_start();

include('db_connection.php'); // Koneksi ke database

// Periksa apakah cookie masih ada dan role adalah user
if (!isset($_COOKIE['username']) || !isset($_COOKIE['role']) || $_COOKIE['role'] !== 'user') {
    // Hapus sesi jika cookie habis atau role salah
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

// Cek apakah session valid
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Ambil data layanan konseling
$sql_services = "SELECT * FROM services";
$result_services = $conn->query($sql_services);
$services = $result_services->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Konseling</title>
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>User Dashboard</h2>
            <ul>
                <li><a href="profile.php">Profil Saya</a></li>
                <li><a href="services.php">Layanan Konseling</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Layanan Konseling</h1>
            <table>
                <thead>
                    <tr>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['service_name']; ?></td>
                            <td><?php echo $service['description']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
