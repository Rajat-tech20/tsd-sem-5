<?php
// ============================================================
//  NEWSLETTER SUBSCRIPTION HANDLER
//  api/subscribe.php  — called via AJAX POST
// ============================================================

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '✓ You are now subscribed! Thank you.']);
    } else {
        // IGNORE means duplicate was silently skipped
        echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Subscription failed. Please try again.']);
}
