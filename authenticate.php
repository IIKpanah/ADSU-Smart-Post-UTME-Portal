<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jamb_reg = trim($_POST['jamb_reg']);
    $password = $_POST['password'];
    $selected_role = $_POST['role'];

    // Validate inputs
    if (empty($jamb_reg) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: index.php");
        exit;
    }

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE jamb_reg = ?");
    $stmt->execute([$jamb_reg]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password and role match
    if ($user && password_verify($password, $user['password'])) {
        
        // Check if the user is logging in with the correct role tab
        if ($user['role'] !== $selected_role) {
            $_SESSION['error'] = "Invalid login portal for this account.";
            header("Location: index.php");
            exit;
        }

        // Set Session Variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['jamb_reg'] = $user['jamb_reg'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: screening.php");
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid credentials. Please check your ID and password.";
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>