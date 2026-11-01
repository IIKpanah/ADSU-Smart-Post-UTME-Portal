<?php
require 'db.php';

// Securely hash the password "password123"
$hashed_password = password_hash('password123', PASSWORD_DEFAULT);

try {
    // Clear users to start fresh (WARNING: This deletes existing users)
    $pdo->exec("DELETE FROM users");
    
    // Insert Admin
    $stmt = $pdo->prepare("INSERT INTO users (jamb_reg, name, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['ADMIN001', 'Admissions Officer', $hashed_password, 'admin']);
    
    // Insert Applicant
    $stmt->execute(['20241234', 'John Doe', $hashed_password, 'applicant']);
    
    echo "<h3>Success! Accounts created successfully.</h3>";
    echo "<p><b>Admin ID:</b> ADMIN001 <br><b>Password:</b> password123</p>";
    echo "<p><b>Applicant ID:</b> 20241234 <br><b>Password:</b> password123</p>";
    echo "<a href='index.php'>Go to Login Page</a>";
    
} catch (PDOException $e) {
    echo "Error setting up accounts: " . $e->getMessage();
}
?>