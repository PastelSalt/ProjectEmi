<?php
/**
 * Notification Settings Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Notification Settings';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();
$user_type = getCurrentUserType();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $job_alerts = isset($_POST['job_alerts']) ? 1 : 0;
        $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;
        $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
        
        $quiet_hours_start = !empty($_POST['quiet_hours_start']) ? $_POST['quiet_hours_start'] : null;
        $quiet_hours_end = !empty($_POST['quiet_hours_end']) ? $_POST['quiet_hours_end'] : null;
        
        // Insert or update notification preferences
        $existing = fetchOne($conn, "SELECT id FROM notification_preferences WHERE user_id = ?", [$user_id], 'i');
        if ($existing) {
            $result = executeQuery($conn, 
                "UPDATE notification_preferences SET 
                    email_notifications = ?, sms_notifications = ?, job_alerts = ?, message_notifications = ?, 
                    marketing_emails = ?, quiet_hours_start = ?, quiet_hours_end = ? 
                 WHERE user_id = ?",
                [$email_notifications, $sms_notifications, $job_alerts, $message_notifications, 
                 $marketing_emails, $quiet_hours_start, $quiet_hours_end, $user_id], 
                'iiiiissi'
            );
        } else {
            $result = executeQuery($conn,
                "INSERT INTO notification_preferences 
                    (user_id, email_notifications, sms_notifications, job_alerts, message_notifications, marketing_emails, quiet_hours_start, quiet_hours_end) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$user_id, $email_notifications, $sms_notifications, $job_alerts, $message_notifications, 
                 $marketing_emails, $quiet_hours_start, $quiet_hours_end], 
                'iiiiiiiss'
            );
        }
        
        if ($result) {
            $success = 'Notification preferences saved successfully!';
        } else {
            $error = 'Failed to save preferences. Please try again.';
        }
    }
}

// Get current preferences
$prefs = fetchOne($conn, 
    "SELECT * FROM notification_preferences WHERE user_id = ?", 
    [$user_id], 'i'
) ?: [
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'job_alerts' => 1,
    'message_notifications' => 1,
    'marketing_emails' => 0,
    'quiet_hours_start' => null,
    'quiet_hours_end' => null
];

closeDBConnection($conn);
?>

<div class="container">
    <!-- Page Header with Back Button -->
    <div class="panel" style="margin-bottom: 16px;">
        <div class="panel-body" style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="notifications.php" class="btn btn-outline btn-small">
                        <i class="fas fa-arrow-left"></i> Back to Notifications
                    </a>
                    <div>
                        <h1 style="margin: 0; color: var(--text-dark); font-size: 1.5rem;">
                            <i class="fas fa-cog" style="color: var(--primary-blue); margin-right: 0.5rem;"></i>
                            Notification Settings
                        </h1>
                        <p style="margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
                            Customize how and when you receive notifications
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="layout-two-col">
        <div>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    Notification Preferences
                </div>
                <div class="panel-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success" style="margin-bottom: 1rem;">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error" style="margin-bottom: 1rem;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        
                        <h3 style="margin-bottom: 1rem; color: var(--primary-blue-dark);">
                            <i class="fas fa-bell"></i> Notification Channels
                        </h3>
                        
                        <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
                            <label class="notification-pref-item">
                                <input type="checkbox" name="email_notifications" value="1" <?php echo $prefs['email_notifications'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-envelope" style="color: var(--primary-blue);"></i> Email Notifications</strong></div>
                                    <small class="text-muted">Receive updates about your applications, job matches, and platform news</small>
                                </div>
                            </label>
                            
                            <label class="notification-pref-item">
                                <input type="checkbox" name="sms_notifications" value="1" <?php echo $prefs['sms_notifications'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-sms" style="color: var(--green-badge);"></i> SMS Notifications</strong></div>
                                    <small class="text-muted">Get text messages for urgent updates. Standard messaging rates may apply.</small>
                                </div>
                            </label>
                        </div>
                        
                        <h3 style="margin-bottom: 1rem; color: var(--primary-blue-dark);">
                            <i class="fas fa-filter"></i> Notification Types
                        </h3>
                        
                        <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
                            <?php if ($user_type === 'worker'): ?>
                            <label class="notification-pref-item">
                                <input type="checkbox" name="job_alerts" value="1" <?php echo $prefs['job_alerts'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-briefcase" style="color: var(--yellow-badge);"></i> Job Alerts</strong></div>
                                    <small class="text-muted">Get notified when new jobs matching your skills are posted</small>
                                </div>
                            </label>
                            <?php else: ?>
                            <label class="notification-pref-item">
                                <input type="checkbox" name="job_alerts" value="1" <?php echo $prefs['job_alerts'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-users" style="color: var(--yellow-badge);"></i> Application Alerts</strong></div>
                                    <small class="text-muted">Get notified when workers apply to your job postings</small>
                                </div>
                            </label>
                            <?php endif; ?>
                            
                            <label class="notification-pref-item">
                                <input type="checkbox" name="message_notifications" value="1" <?php echo $prefs['message_notifications'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-comment" style="color: var(--purple-light);"></i> Message Notifications</strong></div>
                                    <small class="text-muted">Be notified when you receive new messages from employers or workers</small>
                                </div>
                            </label>
                            
                            <label class="notification-pref-item">
                                <input type="checkbox" name="marketing_emails" value="1" <?php echo $prefs['marketing_emails'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-bullhorn" style="color: var(--accent-pink);"></i> Marketing & Promotions</strong></div>
                                    <small class="text-muted">Receive occasional updates about new features, tips, and special offers</small>
                                </div>
                            </label>
                        </div>
                        
                        <h3 style="margin-bottom: 1rem; color: var(--primary-blue-dark);">
                            <i class="fas fa-moon"></i> Quiet Hours
                        </h3>
                        
                        <div style="background: var(--gray-lightest); padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                            <p class="text-muted" style="margin-bottom: 1rem;">Set times when you don't want to receive non-urgent notifications.</p>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="quiet_hours_start">Start Time</label>
                                    <input type="time" name="quiet_hours_start" id="quiet_hours_start" class="form-control" 
                                           value="<?php echo htmlspecialchars($prefs['quiet_hours_start'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="quiet_hours_end">End Time</label>
                                    <input type="time" name="quiet_hours_end" id="quiet_hours_end" class="form-control" 
                                           value="<?php echo htmlspecialchars($prefs['quiet_hours_end'] ?? ''); ?>">
                                </div>
                            </div>
                            <small class="text-muted">Leave empty to disable quiet hours</small>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Preferences
                            </button>
                            <a href="<?php echo $user_type === 'worker' ? 'dashboard-worker.php' : 'dashboard-employer.php'; ?>" class="btn btn-outline">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="sidebar">
            <div class="widget">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    Tips
                </div>
                <div class="panel-body">
                    <div class="notice-text" style="font-size: 0.9rem;">
                        <strong><i class="fas fa-lightbulb" style="color: var(--yellow-badge);"></i> Getting the right balance:</strong><br><br>
                        • Enable email notifications for important updates<br>
                        • Use quiet hours to avoid interruptions<br>
                        • Job alerts help you find opportunities faster<br>
                        • You can change these settings anytime
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
