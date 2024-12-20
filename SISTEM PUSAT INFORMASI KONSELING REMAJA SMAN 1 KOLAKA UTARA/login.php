<?php
session_start();
include('db_connection.php'); // Koneksi ke database

// Periksa apakah sudah login melalui cookie atau session
if (isset($_COOKIE['username']) && isset($_COOKIE['role'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['role'] = $_COOKIE['role'];
    $_SESSION['nisn'] = $_COOKIE['nisn'];

    if ($_COOKIE['role'] == 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: profile.php');
    }
    exit();
}

// Proses form login ketika tombol submit ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Simpan data ke session
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nisn'] = $user['nisn'];

        // Simpan data ke cookie selama 1 jam
        setcookie('username', $user['username'], time() + 3600, "/");
        setcookie('role', $user['role'], time() + 3600, "/");
        setcookie('nisn', $user['nisn'], time() + 3600, "/");

        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: profile.php');
        }
        exit();
    } else {
        $error_message = "Username atau password salah!";
    }

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
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            var errorMessage = '';

            if (username.trim() === '' && password.trim() === '') {
                errorMessage += 'Username dan Password tidak boleh kosong.\n';
            } else {
                if (username.trim() === '') {
                    errorMessage += 'Username tidak boleh kosong.\n';
                }
                if (password.trim() === '') {
                    errorMessage += 'Password tidak boleh kosong.\n';
                }
            }

            if (errorMessage) {
                event.preventDefault();
                document.getElementById('form-error').innerText = errorMessage;
                document.getElementById('form-error').style.display = 'block';
            }
        }
    </script>
</head>

<body>
    <div class="login-wrapper">
        <!-- Login Form Container -->
        <div class="login-form-container">
            <h2>Login</h2>
            <form action="login.php" method="POST" onsubmit="validateForm(event)">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <button type="submit">Login</button>
            </form>
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
