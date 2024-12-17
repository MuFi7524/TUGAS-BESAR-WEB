<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error_message = ''; // Variabel untuk menyimpan pesan error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];

    // Koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'konselingdb');
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Cek apakah layanan sudah ada
    $sql_check = "SELECT * FROM services WHERE service_name = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('s', $service_name);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Layanan sudah ada, tampilkan pesan error
        $error_message = "Layanan dengan nama tersebut sudah ada. Silakan coba nama lain.";
    } else {
        // Query untuk menambahkan layanan
        $sql = "INSERT INTO services (service_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $service_name, $description);

        if ($stmt->execute()) {
            header('Location: admin_dashboard.php');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Layanan</title>
    <link rel="stylesheet" href="add_service.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin_dashboard.php">Layanan Konseling</a></li>
                <li><a href="manage_users.php">Kelola Pengguna</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Tambah Layanan Konseling</h1>
            <form action="add_service.php" method="POST">
                <h2>Form Tambah Layanan</h2>

                <!-- Tampilkan pesan error jika ada -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 20px;">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <label for="service_name">Nama Layanan</label>
                <input type="text" name="service_name" id="service_name" placeholder="Masukkan nama layanan" required>

                <label for="description">Deskripsi Layanan</label>
                <textarea name="description" id="description" placeholder="Masukkan deskripsi layanan" required></textarea>

                <button type="submit">Simpan</button>
                <a href="admin_dashboard.php" class="btn">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>
