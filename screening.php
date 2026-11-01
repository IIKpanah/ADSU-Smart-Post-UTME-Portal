<?php
require 'db.php';
session_start();

// SECURITY GUARD
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

$stmt = $pdo->query("SELECT id, course_name FROM courses");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Screening Portal - ADSU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .card-header { font-weight: bold; }
        
        /* Chatbot CSS */
        .chat-widget-btn {
            position: fixed; bottom: 20px; right: 20px; z-index: 1000;
            border-radius: 50%; width: 60px; height: 60px;
            font-size: 24px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .chat-window {
            position: fixed; bottom: 90px; right: 20px; width: 350px; max-width: 100%;
            height: 450px; z-index: 1000; display: none; flex-direction: column;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); border-radius: 10px; overflow: hidden;
        }
        .chat-header { background: #0d6efd; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .chat-body { flex: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; }
        .chat-message { margin-bottom: 10px; padding: 10px 15px; border-radius: 15px; max-width: 80%; font-size: 14px; }
        .chat-bot { background: #e9ecef; color: #333; align-self: flex-start; border-bottom-left-radius: 0; }
        .chat-user { background: #0d6efd; color: white; align-self: flex-end; border-bottom-right-radius: 0; margin-left: auto; }
        .chat-footer { padding: 10px; background: white; border-top: 1px solid #ddd; display: flex; }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-dark bg-primary px-3 shadow-sm">
    <span class="navbar-brand mb-0 h1">ADSU Smart Post-UTME</span>
    <div class="d-flex align-items-center text-white">
        <span class="me-3">Welcome, <strong><?= htmlspecialchars($user_name) ?></strong></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Screening Application Form</h4>
                </div>
                <div class="card-body p-4">
                    <form action="process_screening.php" method="POST">
                        <!-- Course Selection -->
                        <h5 class="text-primary border-bottom pb-2">1. Course Selection</h5>
                        <div class="mb-4 mt-3">
                            <label class="form-label fw-bold">Select First Choice Course</label>
                            <select name="course_id" class="form-select form-select-lg" required>
                                <option value="">-- Choose a Course --</option>
                                <?php foreach($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- JAMB Details -->
                        <h5 class="text-primary border-bottom pb-2 mt-4">2. JAMB Details</h5>
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">JAMB Aggregate Score</label>
                            <input type="number" name="jamb_score" class="form-control form-control-lg" required max="400" placeholder="e.g., 210">
                        </div>
                        
                        <label class="form-label fw-bold">JAMB Subject Combination</label>
                        <div class="row mb-4 g-2">
                            <div class="col-md-6"><input type="text" name="jamb_subjects[]" class="form-control" placeholder="Subject 1 (e.g., English)" required></div>
                            <div class="col-md-6"><input type="text" name="jamb_subjects[]" class="form-control" placeholder="Subject 2 (e.g., Mathematics)" required></div>
                            <div class="col-md-6"><input type="text" name="jamb_subjects[]" class="form-control" placeholder="Subject 3 (e.g., Physics)" required></div>
                            <div class="col-md-6"><input type="text" name="jamb_subjects[]" class="form-control" placeholder="Subject 4 (e.g., Chemistry)" required></div>
                        </div>

                        <!-- O'Level Results -->
                        <h5 class="text-primary border-bottom pb-2 mt-4">3. O'Level Results</h5>
                        <p class="text-muted small">Enter your 5 core subjects and select the grade obtained.</p>
                        
                        <?php for($i=0; $i<5; $i++): ?>
                        <div class="row mb-2 g-2">
                            <div class="col-md-7">
                                <input type="text" name="olevel_subjects[]" class="form-control" placeholder="Subject (e.g., English Language)" required>
                            </div>
                            <div class="col-md-5">
                                <select name="olevel_grades[]" class="form-select" required>
                                    <option value="" disabled selected>Select Grade</option>
                                    <option value="A1">A1</option>
                                    <option value="B2">B2</option>
                                    <option value="B3">B3</option>
                                    <option value="C4">C4</option>
                                    <option value="C5">C5</option>
                                    <option value="C6">C6 (Credit)</option>
                                    <option value="D7">D7 (Pass)</option>
                                    <option value="E8">E8 (Pass)</option>
                                    <option value="F9">F9 (Fail)</option>
                                </select>
                            </div>
                        </div>
                        <?php endfor; ?>

                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-success btn-lg">Submit for Screening</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHATBOT WIDGET HTML -->
<button class="btn btn-primary chat-widget-btn" onclick="toggleChat()">
    💬
</button>

<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <span>🤖 Smart Assistant</span>
        <button class="btn btn-sm btn-light" onclick="toggleChat()">✖</button>
    </div>
    <div class="chat-body" id="chatBody">
        <div class="chat-message chat-bot">
            Hello! I am your Smart Screening Assistant. Ask me about Cut-off marks, JAMB requirements, O'Level grades, or Course Recommendations!
        </div>
    </div>
    <div class="chat-footer">
        <input type="text" id="chatInput" class="form-control me-2" placeholder="Type your question..." onkeypress="handleKeyPress(event)">
        <button class="btn btn-primary" onclick="sendMessage()">Send</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Chat Window
    function toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
    }

    // Handle Enter key
    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    // Send Message to Backend
    async function sendMessage() {
        const inputField = document.getElementById('chatInput');
        const message = inputField.value.trim();
        if (message === '') return;

        // 1. Append User Message to UI
        appendMessage(message, 'chat-user');
        inputField.value = '';

        // 2. Add temporary "Typing..." message
        const typingId = 'typing-' + Date.now();
        appendMessage("Typing...", 'chat-bot', typingId);

        try {
            // 3. Send AJAX request to chatbot.php
            const response = await fetch('chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
            const data = await response.json();

            // 4. Remove typing message and append bot reply
            document.getElementById(typingId).remove();
            appendMessage(data.reply, 'chat-bot');
        } catch (error) {
            document.getElementById(typingId).remove();
            appendMessage("Sorry, I am having trouble connecting right now. Please try again.", 'chat-bot');
        }
    }

    // Helper function to append messages to UI
    function appendMessage(text, className, id = '') {
        const chatBody = document.getElementById('chatBody');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${className}`;
        if (id) messageDiv.id = id;
        messageDiv.textContent = text;
        chatBody.appendChild(messageDiv);
        
        // Auto-scroll to bottom
        chatBody.scrollTop = chatBody.scrollHeight;
    }
</script>

</body>
</html>