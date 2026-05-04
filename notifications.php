<?php
/**
 * Notifications Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Notifications';
require_once 'config/config.php';
requireLogin();
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isValidCsrf = verifyCsrfToken($_POST['csrf_token'] ?? '');
    if (!$isValidCsrf) {
        http_response_code(400);
    }

    $action = $_POST['action'] ?? '';
    $notification_id = (int)($_POST['notification_id'] ?? 0);
    
    if ($isValidCsrf && $action == 'mark_read' && $notification_id > 0) {
        executeQuery($conn, "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE notification_id = ? AND user_id = ?", [$notification_id, $user_id], 'ii');
    } elseif ($isValidCsrf && $action == 'mark_all_read') {
        executeQuery($conn, "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0", [$user_id], 'i');
    }
}

$notifications = fetchAll($conn, "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$user_id], 'i');
$unread = array_filter($notifications, fn($n) => !$n['is_read']);
$read = array_filter($notifications, fn($n) => $n['is_read']);

function notifIcon($type) {
    return match($type) {
        'new_application' => 'user-plus',
        'application_status' => 'check-circle',
        'new_message' => 'envelope',
        'payment' => 'peso-sign',
        default => 'bell'
    };
}
function notifTagClass($type) {
    return match($type) {
        'new_application' => 'tag-blue',
        'application_status' => 'tag-pink',
        'new_message' => 'tag-green',
        'payment' => 'tag-yellow',
        default => 'tag-gray'
    };
}
?>

<div class="container">
    <!-- Page Header with Settings -->
    <div class="panel" style="margin-bottom: 16px;">
        <div class="panel-body" style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin: 0; color: var(--text-dark); font-size: 1.5rem;">
                        <i class="fas fa-bell" style="color: var(--primary-blue); margin-right: 0.5rem;"></i>
                        Notifications
                    </h1>
                    <p style="margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
                        Manage your notifications and preferences
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="notification-settings.php" class="btn btn-outline">
                        <i class="fas fa-cog"></i> Notification Settings
                    </a>
                    <?php if (!empty($unread)): ?>
                        <form method="POST" style="display: inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-primary btn-small">
                                <i class="fas fa-check-double"></i> Mark All Read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Unread -->
            <?php if (!empty($unread)): ?>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    UNREAD NOTIFICATIONS (<?php echo count($unread); ?>)
                </div>
                <div class="panel-body">
                    <?php foreach ($unread as $notif): ?>
                        <div class="notification-item unread" style="display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.7rem 0; border-bottom: 1px solid var(--border-light);">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-blue); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem;">
                                <i class="fas fa-<?php echo notifIcon($notif['notification_type']); ?>"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 0.85rem;"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                    <span class="tag <?php echo notifTagClass($notif['notification_type']); ?>" style="font-size: 0.6rem;"><?php echo str_replace('_', ' ', $notif['notification_type']); ?></span>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-dark); margin-top: 2px;"><?php echo htmlspecialchars($notif['message']); ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px;">
                                    <i class="fas fa-clock"></i> <?php echo timeAgo($notif['created_at']); ?>
                                </div>
                                <div style="margin-top: 5px; display: flex; gap: 0.3rem;">
                                    <?php $safeActionUrl = sanitizeInternalUrl((string)($notif['action_url'] ?? ''), ''); ?>
                                    <?php if (!empty($safeActionUrl)): ?>
                                        <a href="<?php echo htmlspecialchars($safeActionUrl); ?>" class="btn btn-primary btn-small" style="font-size: 0.7rem;">
                                            <i class="fas fa-external-link-alt"></i> View
                                        </a>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="notification_id" value="<?php echo $notif['notification_id']; ?>">
                                        <button type="submit" class="btn btn-outline btn-small" style="font-size: 0.7rem;">
                                            <i class="fas fa-check"></i> Read
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Read / Earlier -->
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    <?php echo empty($unread) ? 'ALL NOTIFICATIONS' : 'EARLIER NOTIFICATIONS'; ?>
                </div>
                <div class="panel-body">
                    <?php if (empty($read) && empty($unread)): ?>
                        <div class="text-center text-muted" style="padding: 2rem;">
                            <i class="fas fa-bell-slash" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            No notifications yet.
                        </div>
                    <?php elseif (empty($read)): ?>
                        <p class="text-center text-muted" style="padding: 1rem; font-size: 0.82rem;">No earlier notifications.</p>
                    <?php else: ?>
                        <?php foreach ($read as $notif): ?>
                            <div style="display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.6rem 0; border-bottom: 1px solid var(--border-light); opacity: 0.7;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--border-light); color: var(--text-muted); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.7rem;">
                                    <i class="fas fa-<?php echo notifIcon($notif['notification_type']); ?>"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <strong style="font-size: 0.82rem;"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars($notif['message']); ?></div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;">
                                        <i class="fas fa-clock"></i> <?php echo timeAgo($notif['created_at']); ?>
                                        <?php $safeActionUrlRead = sanitizeInternalUrl((string)($notif['action_url'] ?? ''), ''); ?>
                                        <?php if (!empty($safeActionUrlRead)): ?>
                                            &middot; <a href="<?php echo htmlspecialchars($safeActionUrlRead); ?>" style="color: var(--primary-blue-dark); text-decoration: none;">View &rarr;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    NOTIFICATION INFO
                </div>
                <div class="site-info-grid">
                    <div class="site-info-item">
                        <div class="site-info-value pink"><?php echo count($unread); ?></div>
                        <div class="site-info-label">Unread</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value blue"><?php echo count($read); ?></div>
                        <div class="site-info-label">Read</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value cyan"><?php echo count($notifications); ?></div>
                        <div class="site-info-label">Total</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value green">50</div>
                        <div class="site-info-label">Max</div>
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    LEGEND
                </div>
                <div class="panel-body" style="font-size: 0.78rem;">
                    <div style="padding: 3px 0;"><span class="tag tag-blue" style="font-size: 0.6rem;">new application</span> Job application received</div>
                    <div style="padding: 3px 0;"><span class="tag tag-pink" style="font-size: 0.6rem;">application status</span> Application reviewed</div>
                    <div style="padding: 3px 0;"><span class="tag tag-green" style="font-size: 0.6rem;">new message</span> New chat message</div>
                    <div style="padding: 3px 0;"><span class="tag tag-yellow" style="font-size: 0.6rem;">payment</span> Payment update</div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    QUICK LINKS
                </div>
                <div class="panel-body">
                    <a href="messages.php" style="display: block; padding: 0.4rem 0; font-size: 0.82rem; color: var(--primary-blue-dark); text-decoration: none;">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                    <a href="index.php" style="display: block; padding: 0.4rem 0; font-size: 0.82rem; color: var(--primary-blue-dark); text-decoration: none;">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
