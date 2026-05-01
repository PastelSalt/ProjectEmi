<?php
/**
 * API Endpoint: Update User Theme Preference
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Get and validate theme
$theme = sanitizeInput($_POST['theme'] ?? '');
$valid_themes = ['light', 'dark', 'auto'];

if (!in_array($theme, $valid_themes, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid theme value']);
    exit;
}

// Update user's theme preference
$user_id = getCurrentUserId();
$conn = getDBConnection();

$result = executeQuery(
    $conn,
    "UPDATE users SET theme_preference = ? WHERE user_id = ?",
    [$theme, $user_id],
    'si'
);

closeDBConnection($conn);

if ($result && $result->affected_rows >= 0) {
    echo json_encode(['success' => true, 'theme' => $theme]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update theme preference']);
}
