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

// Pastikan parameter nisn diberikan
if (isset($_GET['nisn'])) {
    $nisn = $_GET['nisn'];

    // Ambil data siswa dari tabel students berdasarkan NISN
    $sql = "SELECT * FROM students WHERE nisn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $nisn);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika data siswa ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Data siswa tidak ditemukan.";
        exit();
    }

    // Proses pembaruan data
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $name = $_POST['name'];
        $birthplace = $_POST['birthplace'];
        $birthdate = $_POST['birthdate'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];

        // Update data siswa di tabel students
        $sql_update_student = "UPDATE students SET name = ?, birthplace = ?, birthdate = ?, address = ?, email = ?, phone_number = ? WHERE nisn = ?";
        $stmt_update_student = $conn->prepare($sql_update_student);
        $stmt_update_student->bind_param('sssssss', $name, $birthplace, $birthdate, $address, $email, $phone_number, $nisn);
        if ($stmt_update_student->execute()) {
            // Update data password di tabel users jika password diubah
            if (!empty($_POST['password'])) {
                $password = $_POST['password'];
                $sql_update_user = "UPDATE users SET password = ? WHERE username = ?";
                $stmt_update_user = $conn->prepare($sql_update_user);
                $stmt_update_user->bind_param('ss', $password, $nisn);
                if ($stmt_update_user->execute()) {
                    // Redirect setelah berhasil update
                    header('Location: manage_users.php');
                    exit();
                } else {
                    echo "Error saat update password pengguna: " . $conn->error;
                }
            } else {
                // Redirect tanpa update password jika password kosong
                header('Location: manage_users.php');
                exit();
            }
        } else {
            echo "Error saat update data siswa: " . $conn->error;
        }
    }
} else {
    echo "NISN tidak diberikan.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna</title>
    <link rel="stylesheet" href="edit_user.css">
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
            <h1>Edit Pengguna</h1>
            <form method="POST">
                <label for="nisn">NISN</label>
                <input type="text" name="nisn" value="<?php echo $row['nisn']; ?>" required readonly>

                <label for="name">Nama</label>
                <input type="text" name="name" value="<?php echo $row['name']; ?>" required>

                <label for="birthplace">Tempat Lahir</label>
                <input type="text" name="birthplace" value="<?php echo $row['birthplace']; ?>" required>

                <label for="birthdate">Tanggal Lahir</label>
                <input type="date" name="birthdate" value="<?php echo $row['birthdate']; ?>" required>

                <label for="address">Alamat</label>
                <input type="text" name="address" value="<?php echo $row['address']; ?>" required>

                <label for="email">Email</label>
                <input type="email" name="email" value="<?php echo $row['email']; ?>" required>

                <label for="phone_number">Nomor Handphone</label>
                <input type="text" name="phone_number" value="<?php echo $row['phone_number']; ?>" required>

                <button type="submit">Update Pengguna</button>
                <a href="manage_users.php" class="btn">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>
