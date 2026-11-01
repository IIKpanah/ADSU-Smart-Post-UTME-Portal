<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: screening.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ADSU Smart Post-UTME Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 450px; }
    </style>
</head>
<body>

<div class="card shadow login-card">
    <div class="card-header bg-primary text-white text-center py-3">
        <h4>ADSU Smart Post-UTME Portal</h4>
        <small>Adamawa State University, Mubi</small>
    </div>
    <div class="card-body p-4">
        <!-- Error Message Display -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="authenticate.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Login As:</label>
                <select name="role" class="form-select" required>
                    <option value="applicant">Applicant (Student)</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">JAMB Registration No. / Admin ID</label>
                <input type="text" name="jamb_reg" class="form-control" placeholder="e.g., 20241234 or ADMIN001" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Secure Login</button>
        </form>
    </div>
    <div class="card-footer text-center text-muted py-3">
        <small>&copy; 2026 ADSU ICT Department</small>
    </div>
</div>

</body>
</html>