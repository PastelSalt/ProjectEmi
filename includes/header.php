<?php
/**
 * Header Include File
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

$page_title = $page_title ?? 'Welcome';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$stylePath = BASE_PATH . 'css/style.css';
$styleVersion = file_exists($stylePath) ? filemtime($stylePath) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title) . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $styleVersion; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="rg site-modern <?php echo $current_page === 'index' ? 'is-home' : ''; ?>">
    <!-- Sakura Background -->
    <div class="sakura-bg"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-shell">
            <div class="nav-logo">
                <a href="index.php" class="nav-logo-link">
                    <span class="logo-mark">R</span>
                    <span>Raket<span class="brand-accent">Go</span></span>
                </a>
            </div>
            
            <div class="nav-links nav-menu">
                <a href="index.php" class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-house nav-item-icon" aria-hidden="true"></i>
                    <span>Home</span>
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="for-you.php" class="nav-link <?php echo $current_page == 'for-you' ? 'active' : ''; ?>">
                        <i class="fas fa-compass nav-item-icon" aria-hidden="true"></i>
                        <span>For You</span>
                    </a>
                    <a href="skill-learn.php" class="nav-link <?php echo $current_page == 'skill-learn' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap nav-item-icon" aria-hidden="true"></i>
                        <span>Learn</span>
                    </a>
                    <a href="messages.php" class="nav-link <?php echo $current_page == 'messages' ? 'active' : ''; ?>">
                        <i class="fas fa-message nav-item-icon" aria-hidden="true"></i>
                        <span>Messages</span>
                    </a>
                    
                    <?php if (getCurrentUserType() == 'worker'): ?>
                        <a href="dashboard-worker.php" class="nav-link <?php echo $current_page == 'dashboard-worker' ? 'active' : ''; ?>">
                            <i class="fas fa-gauge-high nav-item-icon" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php elseif (getCurrentUserType() == 'employer'): ?>
                        <a href="dashboard-employer.php" class="nav-link <?php echo $current_page == 'dashboard-employer' ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase nav-item-icon" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php elseif (getCurrentUserType() == 'admin'): ?>
                        <a href="dashboard-admin.php" class="nav-link <?php echo $current_page == 'dashboard-admin' ? 'active' : ''; ?>">
                            <i class="fas fa-shield-halved nav-item-icon" aria-hidden="true"></i>
                            <span>Admin</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="skill-learn.php" class="nav-link <?php echo $current_page == 'skill-learn' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap nav-item-icon" aria-hidden="true"></i>
                        <span>Learn</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="nav-right">
                <?php if (isLoggedIn()): ?>
                    <?php
                    // Use scoped variables to avoid colliding with the page's own $conn/$user_id
                    $_hNavConn  = getDBConnection();
                    $_hNavUid   = getCurrentUserId();
                    $_hNavUnread = fetchOne($_hNavConn, "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$_hNavUid], 'i');
                    $_hNavUser = fetchOne($_hNavConn, "SELECT profile_picture, full_name FROM users WHERE user_id = ?", [$_hNavUid], 'i');
                    closeDBConnection($_hNavConn);
                    ?>
                    <!-- Profile Picture in Nav -->
                    <a href="<?php echo getCurrentUserType() == 'worker' ? 'dashboard-worker.php' : (getCurrentUserType() == 'employer' ? 'dashboard-employer.php' : 'dashboard-admin.php'); ?>" class="nav-link" title="My Dashboard">
                        <?php if (!empty($_hNavUser['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($_hNavUser['profile_picture']); ?>" alt="Profile" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; vertical-align: middle;">
                        <?php else: ?>
                            <span class="nav-item-icon" style="width: 28px; height: 28px; border-radius: 50%; background: var(--primary-blue); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                <?php echo mb_strtoupper(mb_substr($_hNavUser['full_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="notifications.php" class="nav-link <?php echo $current_page == 'notifications' ? 'active' : ''; ?>" title="Notifications" aria-label="Notifications">
                        <i class="fas fa-bell nav-item-icon" aria-hidden="true"></i>
                        <span>Notifications</span>
                        <?php if ($_hNavUnread && (int)$_hNavUnread['count'] > 0): ?>
                            <span class="notif-dot"></span>
                        <?php endif; ?>
                    </a>

                    <?php if (getCurrentUserType() === 'employer'): ?>
                        <a href="post-job.php" class="btn-primary nav-cta">
                            <i class="fas fa-plus nav-item-icon" aria-hidden="true"></i>
                            <span>Post a Job</span>
                        </a>
                    <?php endif; ?>

                    <form method="POST" action="logout.php" class="nav-logout-form">
                        <?php echo csrfField(); ?>
                        <button type="submit" class="btn-logout">
                            <i class="fas fa-right-from-bracket nav-item-icon" aria-hidden="true"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                    <?php unset($_hNavUnread, $_hNavUser); ?>
                <?php else: ?>
                    <a href="login.php" class="btn-login nav-auth-btn">
                        <i class="fas fa-right-to-bracket nav-item-icon" aria-hidden="true"></i>
                        <span>Login</span>
                    </a>
                    <a href="signup.php" class="btn-primary nav-auth-btn">
                        <i class="fas fa-user-plus nav-item-icon" aria-hidden="true"></i>
                        <span>Sign Up</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>


    <main class="main-content">
