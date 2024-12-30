<?php
session_start();
include('db_connection.php'); // Koneksi ke database

// Inisialisasi variabel notifikasi
$edit_profile_message = "";
$update_credentials_message = "";
$delete_account_message = "";

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

// Cek NISN dari sesi
if (!isset($_SESSION['nisn'])) {
    echo "Akses ditolak. NISN tidak ditemukan.";
    exit();
}

$nisn = $_SESSION['nisn'];

// Validasi NISN dari tabel users
$sql_check_user = "SELECT * FROM users WHERE nisn = ?";
$stmt_check_user = $conn->prepare($sql_check_user);
$stmt_check_user->bind_param("s", $nisn);
$stmt_check_user->execute();
$result_check_user = $stmt_check_user->get_result();

if ($result_check_user->num_rows === 0) {
    echo "Akses ditolak. NISN tidak valid.";
    exit();
}

// Ambil data profil dari tabel students
$sql_profile = "SELECT * FROM students WHERE nisn = ?";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("s", $nisn);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();

if ($result_profile->num_rows > 0) {
    $profile = $result_profile->fetch_assoc();
} else {
    echo "Profil tidak ditemukan.";
    exit();
}

// Handle Edit Profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update profil
    if (isset($_POST['edit_profile'])) {
        $name = $_POST['name'];
        $birthplace = $_POST['birthplace'];
        $birthdate = $_POST['birthdate'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];

        $sql_update = "UPDATE students SET name = ?, birthplace = ?, birthdate = ?, address = ?, email = ?, phone_number = ? WHERE nisn = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('sssssss', $name, $birthplace, $birthdate, $address, $email, $phone_number, $nisn);

        if ($stmt_update->execute()) {
            $edit_profile_message = "Profil berhasil diperbarui!";
        } else {
            $edit_profile_message = "Gagal memperbarui profil. Silakan coba lagi.";
        }
    }

    // Update username dan password
    if (isset($_POST['update_credentials'])) {
        $old_username = $_POST['old_username'];
        $old_password = $_POST['old_password'];
        $new_username = $_POST['new_username'];
        $new_password = $_POST['new_password'];

        $sql_check = "SELECT * FROM users WHERE nisn = ? AND username = ? AND password = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param('sss', $nisn, $old_username, $old_password);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $sql_update_credentials = "UPDATE users SET username = ?, password = ? WHERE nisn = ?";
            $stmt_update_credentials = $conn->prepare($sql_update_credentials);
            $stmt_update_credentials->bind_param('sss', $new_username, $new_password, $nisn);

            if ($stmt_update_credentials->execute()) {
                $_SESSION['username'] = $new_username;
                $update_credentials_message = "Username dan password berhasil diperbarui!";
            } else {
                $update_credentials_message = "Gagal memperbarui username atau password.";
            }
        } else {
            $update_credentials_message = "Username atau password lama salah.";
        }
    }
}

// Handle hapus akun
if (isset($_POST['delete_account'])) {
    $confirm_password = $_POST['confirm_password'];

    $sql_check_password = "SELECT * FROM users WHERE nisn = ? AND password = ?";
    $stmt_check_password = $conn->prepare($sql_check_password);
    $stmt_check_password->bind_param('ss', $nisn, $confirm_password);
    $stmt_check_password->execute();
    $result_password = $stmt_check_password->get_result();

    if ($result_password->num_rows > 0) {
        // Mulai transaksi untuk memastikan semua data dihapus dengan konsisten
        $conn->begin_transaction();

        try {
            // Hapus data dari tabel students
            $sql_delete_students = "DELETE FROM students WHERE nisn = ?";
            $stmt_delete_students = $conn->prepare($sql_delete_students);
            $stmt_delete_students->bind_param('s', $nisn);
            $stmt_delete_students->execute();

            // Hapus data dari tabel users
            $sql_delete_users = "DELETE FROM users WHERE nisn = ?";
            $stmt_delete_users = $conn->prepare($sql_delete_users);
            $stmt_delete_users->bind_param('s', $nisn);
            $stmt_delete_users->execute();

            // Commit transaksi
            $conn->commit();

            // Hapus session
            session_unset();
            session_destroy();

            // Hapus cookie
            setcookie('username', '', time() - 3600, '/');
            setcookie('role', '', time() - 3600, '/');

            // Redirect ke halaman utama
            header('Location: index.html');
            exit();
        } catch (Exception $e) {
            // Rollback jika ada kesalahan
            $conn->rollback();
            $delete_account_message = "Gagal menghapus akun. Silakan coba lagi.";
        }
    } else {
        $delete_account_message = "Password konfirmasi salah.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <h2>User Dashboard</h2>
            <ul>
                <li><a href="profile.php">Profil Saya</a></li>
                <li><a href="services.php">Layanan Konseling</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Profil Saya</h1>
            <!-- Edit Profil -->
            <form method="POST">
                <h3>Edit Profil</h3>
                <label>Nama</label><input type="text" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                <label>Tempat Lahir</label><input type="text" name="birthplace" value="<?php echo htmlspecialchars($profile['birthplace']); ?>" required>
                <label>Tanggal Lahir</label><input type="date" name="birthdate" value="<?php echo htmlspecialchars($profile['birthdate']); ?>" required>
                <label>Alamat</label><input type="text" name="address" value="<?php echo htmlspecialchars($profile['address']); ?>" required>
                <label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                <label>Nomor HP</label><input type="text" name="phone_number" value="<?php echo htmlspecialchars($profile['phone_number']); ?>" required>
                <button type="submit" name="edit_profile">Simpan Perubahan</button>
                <span class="notification"><?php echo $edit_profile_message; ?></span>
            </form>

            <!-- Update Credentials -->
            <form method="POST">
                <h3>Ganti Username dan Password</h3>
                <input type="text" name="old_username" placeholder="Username Lama" required>
                <input type="password" name="old_password" placeholder="Password Lama" required>
                <input type="text" name="new_username" placeholder="Username Baru" required>
                <input type="password" name="new_password" placeholder="Password Baru" required>
                <button type="submit" name="update_credentials">Simpan Perubahan</button>
                <span class="notification"><?php echo $update_credentials_message; ?></span>
            </form>

            <!-- Delete Account -->
            <form method="POST">
                <h3>Hapus Akun</h3>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <button type="submit" name="delete_account" onclick="return confirm('Yakin ingin menghapus akun?')">Hapus Akun</button>
                <span class="notification"><?php echo $delete_account_message; ?></span>
            </form>
        </div>
    </div>
</body>
</html>
