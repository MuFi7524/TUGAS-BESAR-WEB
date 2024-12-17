<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'konselingdb');
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek jika input kosong, maka data lama yang digunakan
    $service_name = !empty(trim($_POST['service_name'])) ? $_POST['service_name'] : $row['service_name'];
    $description = !empty(trim($_POST['description'])) ? $_POST['description'] : $row['description'];

    // Query untuk update data layanan
    $sql = "UPDATE services SET service_name='$service_name', description='$description' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Ambil data layanan berdasarkan ID
$sql = "SELECT * FROM services WHERE id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan</title>
    <link rel="stylesheet" href="edit_service.css">
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
            <h1>Edit Layanan Konseling</h1>
            <form action="edit_service.php?id=<?php echo $id; ?>" method="POST">
                <h2>Form Edit Layanan</h2>
                <label for="service_name">Nama Layanan</label>
                <input type="text" name="service_name" id="service_name" value="<?php echo htmlspecialchars($row['service_name']); ?>" required>
                
                <label for="description">Deskripsi Layanan</label>
                <textarea name="description" id="description" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                
                <button type="submit">Update</button>
                <a href="admin_dashboard.php" class="btn">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>
