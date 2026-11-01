<?php
require 'db.php';
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $jamb_score = $_POST['jamb_score'];
    $jamb_subjects = array_map('trim', array_map('ucwords', $_POST['jamb_subjects']));
    $olevel_subjects = array_map('trim', array_map('ucwords', $_POST['olevel_subjects']));
    $olevel_grades = $_POST['olevel_grades'];

    // Format O'Level for Database
    $olevel_data = [];
    foreach($olevel_subjects as $index => $subj) {
        $olevel_data[$subj] = $olevel_grades[$index];
    }

    // 1. Fetch the chosen course
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    // 2. Evaluation Logic
    $is_eligible = true;
    $reasons = [];

    // A. Check JAMB Score vs Cut-off
    if ($jamb_score < $course['cut_off']) {
        $is_eligible = false;
        $reasons[] = "Your JAMB score ({$jamb_score}) is below the cut-off mark ({$course['cut_off']}) for {$course['course_name']}.";
    }

    // B. Check O'Level Requirements (Must have 5 credits including English & Math)
    $credits = 0;
    $has_english = false;
    $has_math = false;
    
    foreach ($olevel_data as $subj => $grade) {
        if (in_array($grade, ['A1', 'B2', 'B3', 'C4', 'C5', 'C6'])) {
            $credits++;
            if (strpos($subj, 'English') !== false) $has_english = true;
            if (strpos($subj, 'Math') !== false) $has_math = true;
        }
    }

    if ($credits < 5 || !$has_english || !$has_math) {
        $is_eligible = false;
        $reasons[] = "You do not have the minimum 5 credits including English and Mathematics.";
    }

    // C. Check JAMB Subject Combination
    $required_jamb = explode(',', $course['jamb_req']);
    $jamb_match = true;
    
    foreach ($required_jamb as $req) {
        // Handle "or" conditions (e.g., Physics|Chemistry)
        $options = explode('|', trim($req));
        $found = false;
        foreach ($options as $opt) {
            if (in_array(trim($opt), $jamb_subjects)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $jamb_match = false;
            break;
        }
    }

    if (!$jamb_match) {
        $is_eligible = false;
        $reasons[] = "Your JAMB subject combination does not match the requirements for {$course['course_name']}.";
    }

    // 3. SMART RECOMMENDATION ENGINE
    $recommendations = [];
    if (!$is_eligible) {
        // Find alternative courses where score > cut_off and subjects match
        $stmt = $pdo->query("SELECT * FROM courses");
        $all_courses = $stmt->fetchAll();

        foreach ($all_courses as $alt_course) {
            if ($alt_course['id'] == $course_id) continue; // Skip the one they failed

            // Check cut-off
            if ($jamb_score >= $alt_course['cut_off']) {
                // Check JAMB subjects for the alternative course
                $alt_req_jamb = explode(',', $alt_course['jamb_req']);
                $alt_jamb_match = true;
                foreach ($alt_req_jamb as $req) {
                    $options = explode('|', trim($req));
                    $found = false;
                    foreach ($options as $opt) {
                        if (in_array(trim($opt), $jamb_subjects)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $alt_jamb_match = false;
                        break;
                    }
                }
                
                if ($alt_jamb_match) {
                    $recommendations[] = $alt_course['course_name'];
                }
            }
        }
    }

    // 4. Save to Database
    $status = $is_eligible ? 'Eligible' : 'Not Eligible';
    $rec_json = json_encode($recommendations);

    $sql = "INSERT INTO applications (user_id, jamb_score, jamb_subjects, olevel_results, first_choice_id, status, recommendations) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $jamb_score, implode(',', $jamb_subjects), json_encode($olevel_data), $course_id, $status, $rec_json]);

    // 5. Display Result
    ?>
    <!DOCTYPE html>
    <html>
    <head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow border-<?= $is_eligible ? 'success' : 'danger' ?>">
            <div class="card-header bg-<?= $is_eligible ? 'success' : 'danger' ?> text-white">
                <h3>Screening Result</h3>
            </div>
            <div class="card-body">
                <h4>Status: <strong><?= $status ?></strong></h4>
                <p>First Choice Course: <strong><?= $course['course_name'] ?></strong></p>
                
                <?php if(!$is_eligible): ?>
                    <div class="alert alert-danger">
                        <h5>Reasons for Ineligibility:</h5>
                        <ul>
                            <?php foreach($reasons as $reason): ?>
                                <li><?= $reason ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info">
                        <h5><i class="bi bi-lightbulb"></i> Smart Recommendations:</h5>
                        <p>Based on your JAMB score and subject combination, you are eligible for the following alternative courses at ADSU:</p>
                        <?php if(empty($recommendations)): ?>
                            <p>No alternative courses found matching your current profile. Please review your O'Level results or JAMB combination.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach($recommendations as $rec): ?>
                                    <li class="list-group-item list-group-item-action"><?= $rec ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <h4>Congratulations!</h4>
                        <p>You meet all the requirements for <strong><?= $course['course_name'] ?></strong>. Please proceed to the Bursary to pay your screening fee.</p>
                    </div>
                <?php endif; ?>
                <a href="screening.php" class="btn btn-primary mt-3">Back to Portal</a>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
}
?>