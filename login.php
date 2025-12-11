<?php
require_once __DIR__ . '/config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Process login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi";
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    
                    redirect('dashboard.php');
                } else {
                    $error = "Username atau password salah";
                }
            } catch (PDOException $e) {
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        } else {
            $error = "Koneksi database gagal: " . $db->getError();
        }
    }
}

// Include login view
include __DIR__ . '/app/views/auth/login.php';
?>
