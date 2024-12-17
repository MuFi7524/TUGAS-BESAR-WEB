<?php
// Mulai sesi
session_start();

// Periksa apakah sudah login dan arahkan ke dashboard yang sesuai
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: profile.php');
    }
    exit();
}

// Proses form login ketika tombol submit ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'konselingdb');

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Query untuk memeriksa pengguna di database
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Pengguna ditemukan, ambil data
        $user = $result->fetch_assoc();

        // Simpan informasi pengguna ke sesi
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nisn'] = $user['nisn']; // Ambil NISN dari tabel users

        // Redirect berdasarkan role pengguna
        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: profile.php');
        }
        exit();
    } else {
        // Jika login gagal, tampilkan pesan error
        $error_message = "Username atau password salah!";
    }

    // Tutup koneksi
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <script>
        // Fungsi untuk validasi form
        function validateForm(event) {
            // Ambil nilai dari input
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            var errorMessage = ''; // Inisialisasi pesan error

            // Validasi username
            if (username.trim() === '' && password.trim() === '') {
                errorMessage += 'Username dan Password tidak boleh kosong.\n';
            } else {
                // Validasi username
                if (username.trim() === '') {
                    errorMessage += 'Username tidak boleh kosong.\n';
                }

                // Validasi password
                if (password.trim() === '') {
                    errorMessage += 'Password tidak boleh kosong.\n';
                }
            }

            // Jika ada error, tampilkan pesan dan batalkan submit
            if (errorMessage) {
                event.preventDefault(); // Mencegah pengiriman form
                document.getElementById('form-error').innerText = errorMessage; // Tampilkan pesan error di halaman
                document.getElementById('form-error').style.display = 'block'; // Tampilkan pesan error
            }
        }
    </script>
</head>

<body>
    <div class="login-wrapper">
        <!-- Login Form Container -->
        <div class="login-form-container">
            <h2>Login</h2>

            <!-- Form login dengan onsubmit untuk validasi -->
            <form action="login.php" method="POST" onsubmit="validateForm(event)">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <button type="submit">Login</button>
            </form>

            <!-- Pesan kesalahan jika ada -->
            <p id="form-error" class="error" style="display: <?php echo isset($error_message) ? 'block' : 'none'; ?>;">
                <?php echo isset($error_message) ? $error_message : ''; ?>
            </p>
        </div>
        <!-- Right Background Section with Logo -->
        <div class="background-container">
            <div class="logo-background">
                <img src="genre.png" alt="Logo" class="background-logo">
            </div>
        </div>
    </div>
</body>

</html>
