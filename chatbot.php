<?php
// chatbot.php
header('Content-Type: application/json');

// Get the user's message
$input = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($input['message'] ?? ''));

if (empty($message)) {
    echo json_encode(['reply' => "Please type a message so I can assist you."]);
    exit;
}

// Define Smart Rule-Based Responses based on the SRS
$reply = "I'm sorry, I didn't quite understand that. I can assist you with: JAMB scores, O'Level requirements, Cut-off marks, or Course Recommendations. You can also contact the ICT helpdesk for further assistance.";

// Keyword matching logic
if (preg_match('/(hello|hi|hey|greet)/', $message)) {
    $reply = "Hello! I am the ADSU Smart Screening Assistant. How can I help you today?";
} 
elseif (preg_match('/(cut.?off|mark|score)/', $message)) {
    $reply = "Cut-off marks vary by course. For example, Computer Science requires 170, while general courses like Physics require 150. The system will automatically check your score against the exact cut-off for your chosen course.";
} 
elseif (preg_match('/(jamb|subject|combination)/', $message)) {
    $reply = "Your JAMB subject combination must match your chosen course. For Science courses, you typically need English, Mathematics, and relevant sciences. Ensure you enter them correctly in the form.";
} 
elseif (preg_match('/(olevel|o.?level|waec|neco|grade|credit)/', $message)) {
    $reply = "You must have a minimum of 5 O'Level credits, including English Language and Mathematics. Please use the dropdown menus to select your exact grades (A1 to F9).";
} 
elseif (preg_match('/(recommend|alternative|fail|miss|change)/', $message)) {
    $reply = "If you don't meet the cut-off for your first choice, our Smart Recommendation Engine will automatically suggest 3 alternative courses based on your JAMB score and subject combination!";
} 
elseif (preg_match('/(fee|payment|pay|money)/', $message)) {
    $reply = "If you are eligible, you will be directed to a secure payment gateway to pay the screening fee. Ensure you download your receipt after payment.";
} 
elseif (preg_match('/(error|not working|help|problem)/', $message)) {
    $reply = "If you are experiencing technical issues, please ensure you have filled all required fields. For further help, email support@adsu.edu.ng.";
}

// Simulate a slight delay for a realistic "typing" feel
sleep(1);

// Return response
echo json_encode(['reply' => $reply]);
?>