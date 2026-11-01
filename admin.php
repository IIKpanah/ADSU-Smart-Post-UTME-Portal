<?php
require 'db.php';
session_start();

// SECURITY GUARD: Ensure user is logged in AND is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['name'];
$message = '';

// Handle Cut-off Update Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cutoff'])) {
    $course_id = $_POST['course_id'];
    $new_cutoff = $_POST['new_cutoff'];
    
    $stmt = $pdo->prepare("UPDATE courses SET cut_off = ? WHERE id = ?");
    if ($stmt->execute([$new_cutoff, $course_id])) {
        $message = "<div class='alert alert-success'>Cut-off mark updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to update cut-off mark.</div>";
    }
}

// Fetch Dashboard Stats
$total_applicants = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$eligible = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Eligible'")->fetchColumn();
$not_eligible = $total_applicants - $eligible;

// Fetch all courses for the update dropdown
$stmt = $pdo->query("SELECT id, course_name, cut_off FROM courses ORDER BY course_name ASC");
$all_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all applications for the data table
$sql = "SELECT a.id, u.name, u.jamb_reg, c.course_name, a.jamb_score, a.status, a.created_at 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN courses c ON a.first_choice_id = c.id 
        ORDER BY a.created_at DESC";
$stmt = $pdo->query($sql);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ADSU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-dark bg-dark px-3 shadow-sm">
    <span class="navbar-brand mb-0 h1">ADSU Admissions Admin Panel</span>
    <div class="d-flex align-items-center text-white">
        <span class="me-3">Admin: <strong><?= htmlspecialchars($admin_name) ?></strong></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-4 mb-5">
    
    <?= $message ?>

    <!-- Statistics Cards -->
    <div class="row text-center mb-4 g-3">
        <div class="col-md-4">
            <div class="card bg-primary text-white p-3 shadow stat-card">
                <h5>Total Screened</h5>
                <h1 class="display-5 fw-bold"><?= $total_applicants ?></h1>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white p-3 shadow stat-card">
                <h5>Eligible Applicants</h5>
                <h1 class="display-5 fw-bold"><?= $eligible ?></h1>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white p-3 shadow stat-card">
                <h5>Not Eligible</h5>
                <h1 class="display-5 fw-bold"><?= $not_eligible ?></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Update Cut-off Form -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Update Cut-Off Marks</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach($all_courses as $course): ?>
                                    <option value="<?= $course['id'] ?>">
                                        <?= htmlspecialchars($course['course_name']) ?> (Current: <?= $course['cut_off'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Cut-Off Mark</label>
                            <input type="number" name="new_cutoff" class="form-control" required min="100" max="400">
                        </div>
                        <button type="submit" name="update_cutoff" class="btn btn-primary w-100">Update Mark</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="col-md-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Applications</h5>
                    <span class="badge bg-light text-dark"><?= count($applications) ?> Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>Name / JAMB Reg</th>
                                    <th>Course</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($applications)): ?>
                                    <tr><td colspan="4" class="text-center py-4">No applications submitted yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($app['name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($app['jamb_reg']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($app['course_name']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($app['jamb_score']) ?></td>
                                        <td>
                                            <?php if($app['status'] === 'Eligible'): ?>
                                                <span class="badge bg-success">Eligible</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Eligible</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>