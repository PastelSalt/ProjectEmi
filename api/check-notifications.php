<?php
/**
 * API Endpoint: Check New Notifications (Unread Count)
 * RaketGo - Job Matching Platform
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';

// Must be GET (fetch uses GET)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = getCurrentUserId();
$conn = getDBConnection();

// notifications table uses: notification_id, user_id, is_read, created_at, read_at, etc.
// (consistent with notifications.php and DatabaseHelper)
try {
    $unread = fetchOne(
        $conn,
        "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0",
        [$user_id],
        'i'
    );

    $count = $unread ? (int)$unread['unread_count'] : 0;

    closeDBConnection($conn);

    echo json_encode(['success' => true, 'unread_count' => $count]);
} catch (Throwable $e) {
    closeDBConnection($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch unread notifications']);
}

