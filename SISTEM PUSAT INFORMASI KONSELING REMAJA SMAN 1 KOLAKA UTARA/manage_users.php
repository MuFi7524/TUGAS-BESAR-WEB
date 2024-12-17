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

// Inisialisasi variabel
$search = "";
$students = [];

// Menangani pencarian
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM students WHERE 
            nisn LIKE '%$search%' OR 
            name LIKE '%$search%' OR 
            email LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM students";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Pengguna</title>
    <link rel="stylesheet" href="manage_users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function confirmDelete() {
            return confirm("Apakah Anda yakin ingin menghapus pengguna ini?");
        }
    </script>
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
            <h1>Kelola Pengguna</h1>
            <!-- Modern Search Form -->
            <form method="GET" action="manage_users.php" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    value="<?php echo htmlspecialchars($search); ?>" 
                    placeholder="Cari berdasarkan NISN, nama, atau email..." 
                />
                <button type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
            <a href="add_user.php" class="btn">Tambah Pengguna</a>
            <!-- Table of students -->
            <table>
                <thead>
                    <tr>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Tempat, Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Email</th>
                        <th>Nomor Handphone</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['nisn']; ?></td>
                            <td><?php echo $student['name']; ?></td>
                            <td><?php echo $student['birthplace'] . ', ' . $student['birthdate']; ?></td>
                            <td><?php echo $student['address']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['phone_number']; ?></td>
                            <td>
                                <a href="edit_user.php?nisn=<?php echo $student['nisn']; ?>">Edit</a> | 
                                <a href="delete_user.php?nisn=<?php echo $student['nisn']; ?>" onclick="return confirmDelete()">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
