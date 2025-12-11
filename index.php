<?php
// Redirect to dashboard or login
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>
