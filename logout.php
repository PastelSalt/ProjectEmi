<?php
/**
 * Logout Script
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
	redirect('index.php');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home page
redirect('index.php');
?>
