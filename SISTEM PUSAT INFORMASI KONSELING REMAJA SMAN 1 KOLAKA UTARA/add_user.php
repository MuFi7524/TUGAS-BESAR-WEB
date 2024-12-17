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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nisn = $_POST['nisn']; // Misalnya, NISN yang diambil dari form
    $name = $_POST['name'];
    $birthplace = $_POST['birthplace'];
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Periksa apakah NISN sudah ada di database
    $sql_check = "SELECT * FROM students WHERE nisn = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('s', $nisn);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // NISN sudah ada, tampilkan pesan error dan reset form
        $error_message = "Pengguna dengan NISN tersebut sudah ada. Silakan gunakan NISN lain.";
    } else {
        // Insert data siswa ke tabel students
        $sql_student = "INSERT INTO students (nisn, name, birthplace, birthdate, address, email, phone_number)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_student = $conn->prepare($sql_student);
        $stmt_student->bind_param('sssssss', $nisn, $name, $birthplace, $birthdate, $address, $email, $phone_number);

        if ($stmt_student->execute()) {
            // Insert data user ke tabel users dengan nisn sebagai foreign key
            $password = $nisn; // Set password dengan nisn (password awal)
            $role = 'user'; // Role default adalah 'user'

            // Insert data user ke tabel users dengan nisn
            $sql_user = "INSERT INTO users (username, password, role, nisn) VALUES (?, ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param('ssss', $nisn, $password, $role, $nisn);

            if ($stmt_user->execute()) {
                echo "Pengguna berhasil ditambahkan.";
                // Reset form jika sukses
                header('Location: add_user.php'); // Redirect ke halaman ini untuk form kosong
                exit();
            } else {
                echo "Gagal menambahkan pengguna ke tabel users.";
            }
        } else {
            echo "Gagal menambahkan siswa.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna</title>
    <link rel="stylesheet" href="add_user.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin_dashboard.php">Layanan Konseling</a></li>
                <li><a href="manage_users.php">Kelola Pengguna</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Tambah Pengguna</h1>

            <!-- Tampilkan pesan error jika ada -->
            <?php if (isset($error_message)): ?>
                <div class="error-message" style="color: red; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label for="nisn">NISN</label>
                <input type="text" name="nisn" required>

                <label for="name">Nama</label>
                <input type="text" name="name" required>

                <label for="birthplace">Tempat Lahir</label>
                <input type="text" name="birthplace" required>

                <label for="birthdate">Tanggal Lahir</label>
                <input type="date" name="birthdate" required>

                <label for="address">Alamat</label>
                <input type="text" name="address" required>

                <label for="email">Email</label>
                <input type="email" name="email" required>

                <label for="phone_number">Nomor Handphone</label>
                <input type="text" name="phone_number" required>

                <button type="submit">Tambah Pengguna</button>
                <a href="manage_users.php" class="btn">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>
