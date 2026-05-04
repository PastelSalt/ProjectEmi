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

// Get user's theme preference
$_userTheme = 'auto';
if (isLoggedIn()) {
    $_themeConn = getDBConnection();
    $_themeResult = fetchOne($_themeConn, "SELECT theme_preference FROM users WHERE user_id = ?", [getCurrentUserId()], 'i');
    if ($_themeResult && $_themeResult['theme_preference']) {
        $_userTheme = $_themeResult['theme_preference'];
    }
    closeDBConnection($_themeConn);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($_userTheme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RaketGo - Connect with employers and workers across the Philippines. Find your next job or hire skilled workers.">
    <meta name="theme-color" content="#A8C5D4">
    <title><?php echo htmlspecialchars($page_title) . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $styleVersion; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
    // Apply theme immediately to prevent flash
    (function() {
        const theme = localStorage.getItem('theme') || '<?php echo $_userTheme; ?>';
        if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
    </script>
</head>
<body class="rg site-modern <?php echo $current_page === 'index' ? 'is-home' : ''; ?>">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <!-- Sakura Background -->
    <div class="sakura-bg"></div>

    <!-- Navigation -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="container nav-shell">
            <div class="nav-logo">
                <a href="index.php" class="nav-logo-link" aria-label="RaketGo Home">
                    <span class="logo-mark">R</span>
                    <span>Raket<span class="brand-accent">Go</span></span>
                </a>
            </div>
            
            <!-- Mobile menu toggle -->
            <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            
            <div class="nav-links nav-menu" id="mainNavMenu">
                <!-- Core Navigation -->
                <a href="index.php" class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-house nav-item-icon" aria-hidden="true"></i>
                    <span>Home</span>
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="for-you.php" class="nav-link <?php echo $current_page == 'for-you' ? 'active' : ''; ?>">
                        <i class="fas fa-compass nav-item-icon" aria-hidden="true"></i>
                        <span>Discover</span>
                    </a>
                    <a href="skill-learn.php" class="nav-link <?php echo $current_page == 'skill-learn' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap nav-item-icon" aria-hidden="true"></i>
                        <span>Learn</span>
                    </a>
                    
                    <!-- Role-specific primary action -->
                    <?php if (getCurrentUserType() == 'worker'): ?>
                        <a href="jobs.php" class="nav-link <?php echo $current_page == 'jobs' ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase nav-item-icon" aria-hidden="true"></i>
                            <span>Find Jobs</span>
                        </a>
                    <?php elseif (getCurrentUserType() == 'employer'): ?>
                        <a href="dashboard-employer.php" class="nav-link <?php echo $current_page == 'dashboard-employer' ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase nav-item-icon" aria-hidden="true"></i>
                            <span>My Jobs</span>
                        </a>
                    <?php elseif (getCurrentUserType() == 'admin'): ?>
                        <a href="dashboard-admin.php" class="nav-link <?php echo $current_page == 'dashboard-admin' ? 'active' : ''; ?>">
                            <i class="fas fa-shield-halved nav-item-icon" aria-hidden="true"></i>
                            <span>Admin</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="jobs.php" class="nav-link <?php echo $current_page == 'jobs' ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase nav-item-icon" aria-hidden="true"></i>
                        <span>Browse Jobs</span>
                    </a>
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
                    
                    <!-- Communication -->
                    <div class="nav-communications">
                        <a href="messages.php" class="nav-link <?php echo $current_page == 'messages' ? 'active' : ''; ?>" title="Messages">
                            <i class="fas fa-message nav-item-icon" aria-hidden="true"></i>
                            <span>Messages</span>
                        </a>

                        <a href="notifications.php" class="nav-link <?php echo $current_page == 'notifications' ? 'active' : ''; ?>" title="Notifications" aria-label="Notifications">
                            <i class="fas fa-bell nav-item-icon" aria-hidden="true"></i>
                            <span>Notifications</span>
                            <?php if ($_hNavUnread && (int)$_hNavUnread['count'] > 0): ?>
                                <span class="notif-dot"></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- User Actions -->
                    <div class="nav-user-actions">
                        <!-- Profile Link -->
                        <a href="<?php 
                            echo getCurrentUserType() == 'worker' ? 'worker-profile.php?id=' . $_hNavUid : 
                                 (getCurrentUserType() == 'employer' ? 'employer-profile.php?id=' . $_hNavUid : 
                                 'dashboard-admin.php'); 
                        ?>" class="nav-link <?php echo $current_page == 'worker-profile' || $current_page == 'employer-profile' ? 'active' : ''; ?>" title="My Profile">
                            <?php if (!empty($_hNavUser['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($_hNavUser['profile_picture']); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user nav-item-icon" aria-hidden="true"></i>
                            <?php endif; ?>
                            <span>Profile</span>
                        </a>

                        <!-- Secondary Actions -->
                        <div class="nav-secondary">
                            <?php if (getCurrentUserType() == 'worker'): ?>
                                <a href="dashboard-worker.php" class="nav-link <?php echo $current_page == 'dashboard-worker' ? 'active' : ''; ?>" title="My Dashboard">
                                    <i class="fas fa-th-large nav-item-icon" aria-hidden="true"></i>
                                    <span>Dashboard</span>
                                </a>
                            <?php elseif (getCurrentUserType() == 'employer'): ?>
                                <a href="post-job.php" class="btn-primary nav-cta">
                                    <i class="fas fa-plus nav-item-icon" aria-hidden="true"></i>
                                    <span>Post Job</span>
                                </a>
                            <?php endif; ?>

                            <form method="POST" action="logout.php" class="nav-logout-form">
                                <?php echo csrfField(); ?>
                                <button type="submit" class="btn-logout">
                                    <i class="fas fa-right-from-bracket nav-item-icon" aria-hidden="true"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php unset($_hNavUnread, $_hNavUser); ?>
                <?php else: ?>
                    <!-- Guest Actions -->
                    <div class="nav-guest-actions">
                        <a href="login.php" class="btn-login nav-auth-btn">
                            <i class="fas fa-right-to-bracket nav-item-icon" aria-hidden="true"></i>
                            <span>Login</span>
                        </a>
                        <a href="signup.php" class="btn-primary nav-auth-btn">
                            <i class="fas fa-user-plus nav-item-icon" aria-hidden="true"></i>
                            <span>Sign Up</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>


    <main class="main-content" id="main-content" role="main">
    
    <!-- Mobile Bottom Navigation -->
    <?php if (isLoggedIn()): ?>
    <nav class="mobile-bottom-nav" role="navigation" aria-label="Mobile navigation">
        <a href="index.php" class="mobile-nav-item <?php echo $current_page == 'index' ? 'active' : ''; ?>">
            <i class="fas fa-home" aria-hidden="true"></i>
            <span>Home</span>
        </a>
        <a href="for-you.php" class="mobile-nav-item <?php echo $current_page == 'for-you' ? 'active' : ''; ?>">
            <i class="fas fa-compass" aria-hidden="true"></i>
            <span>Explore</span>
        </a>
        <?php if (getCurrentUserType() == 'worker'): ?>
            <a href="dashboard-worker.php" class="mobile-nav-item <?php echo $current_page == 'dashboard-worker' ? 'active' : ''; ?>">
                <i class="fas fa-briefcase" aria-hidden="true"></i>
                <span>Jobs</span>
            </a>
        <?php else: ?>
            <a href="post-job.php" class="mobile-nav-item <?php echo $current_page == 'post-job' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                <span>Post</span>
            </a>
        <?php endif; ?>
        <a href="messages.php" class="mobile-nav-item <?php echo $current_page == 'messages' ? 'active' : ''; ?>">
            <i class="fas fa-comment" aria-hidden="true"></i>
            <span>Messages</span>
        </a>
        <a href="<?php echo getCurrentUserType() == 'worker' ? 'dashboard-worker.php' : 'dashboard-employer.php'; ?>" class="mobile-nav-item">
            <i class="fas fa-user" aria-hidden="true"></i>
            <span>Profile</span>
        </a>
    </nav>
    <?php endif; ?>
    
    <!-- Theme and Mobile Menu Scripts -->
    <script>
    // Theme toggle functionality
    (function() {
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const sunIcon = themeToggle?.querySelector('[data-theme-icon="light"]');
        const moonIcon = themeToggle?.querySelector('[data-theme-icon="dark"]');
        
        function updateThemeIcons(theme) {
            if (!sunIcon || !moonIcon) return;
            if (theme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'inline-block';
            } else {
                sunIcon.style.display = 'inline-block';
                moonIcon.style.display = 'none';
            }
        }
        
        function setTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeIcons(theme);
            
            // Save preference to server if logged in
            <?php if (isLoggedIn()): ?>
            fetch('api/update-theme.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'theme=' + encodeURIComponent(theme) + '&csrf_token=' + encodeURIComponent('<?php echo $_SESSION['csrf_token'] ?? ''; ?>')
            }).catch(() => {});
            <?php endif; ?>
        }
        
        // Initialize icons
        const currentTheme = html.getAttribute('data-theme') || 'auto';
        const isDark = currentTheme === 'dark' || 
            (currentTheme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        updateThemeIcons(isDark ? 'dark' : 'light');
        
        themeToggle?.addEventListener('click', function() {
            const current = html.getAttribute('data-theme') || 'auto';
            const isDarkNow = current === 'dark' || 
                (current === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            setTheme(isDarkNow ? 'light' : 'dark');
        });
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            const theme = html.getAttribute('data-theme');
            if (theme === 'auto') {
                updateThemeIcons(e.matches ? 'dark' : 'light');
            }
        });
    })();
    
    // Mobile menu toggle
    (function() {
        const mobileToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('mainNavMenu');
        
        mobileToggle?.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            navMenu?.classList.toggle('mobile-open');
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });
    })();
    </script>
