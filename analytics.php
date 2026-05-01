<?php
/**
 * Analytics Page (Admin)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Analytics & Reports';
require_once 'config/config.php';
requireUserType('admin');
require_once 'includes/header.php';

$conn = getDBConnection();
$tab = sanitizeInput($_GET['tab'] ?? 'overview');

// Get all stats
$userStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN user_type = 'worker' THEN 1 ELSE 0 END) as total_workers,
    SUM(CASE WHEN user_type = 'employer' THEN 1 ELSE 0 END) as total_employers,
    SUM(CASE WHEN account_status = 'active' THEN 1 ELSE 0 END) as active_users,
    AVG(CASE WHEN user_type = 'worker' THEN trust_score ELSE NULL END) as avg_worker_score,
    MAX(created_at) as last_signup
    FROM users WHERE user_type != 'admin'");

$jobStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_jobs,
    SUM(CASE WHEN job_status = 'active' THEN 1 ELSE 0 END) as active_jobs,
    SUM(CASE WHEN job_status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
    SUM(CASE WHEN job_status = 'draft' THEN 1 ELSE 0 END) as draft_jobs,
    AVG(pay_amount) as avg_pay,
    MAX(created_at) as last_post
    FROM job_posts");

$appStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN application_status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
    SUM(CASE WHEN application_status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications,
    SUM(CASE WHEN worker_confirmed = 1 AND employer_confirmed = 1 THEN 1 ELSE 0 END) as confirmed_work
    FROM job_applications");

$skillStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_posts,
    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_posts,
    MAX(created_at) as last_post
    FROM skill_posts");

$platformStats = fetchOne($conn, "SELECT 
    COUNT(DISTINCT day) as active_days,
    COUNT(*) as total_interactions
    FROM user_interactions
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

// Recent activity
$recentSignups = fetchAll($conn, "SELECT user_id, full_name, user_type, created_at FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 10");
$topEmployers = fetchAll($conn, "SELECT u.user_id, u.full_name, COUNT(j.job_id) as job_count FROM users u LEFT JOIN job_posts j ON u.user_id = j.employer_id WHERE u.user_type = 'employer' GROUP BY u.user_id ORDER BY job_count DESC LIMIT 5");
$topSkills = fetchAll($conn, "SELECT skill_name, difficulty_level, is_featured, created_at FROM skill_posts ORDER BY created_at DESC LIMIT 10");

// Charts data
$usersPerDay = fetchAll($conn, "SELECT DATE(created_at) as day, COUNT(*) as count FROM users WHERE user_type != 'admin' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY day");
$jobsPerDay = fetchAll($conn, "SELECT DATE(created_at) as day, COUNT(*) as count FROM job_posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY day");
$appsByStatus = fetchAll($conn, "SELECT application_status, COUNT(*) as count FROM job_applications GROUP BY application_status");
$usersByType = fetchAll($conn, "SELECT user_type, COUNT(*) as count FROM users WHERE user_type != 'admin' GROUP BY user_type");
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Tabs -->
            <div style="margin-bottom: 20px; display: flex; gap: 8px; border-bottom: 2px solid #E8ECF5;">
                <a href="?tab=overview" class="btn btn-<?php echo $tab === 'overview' ? 'primary' : 'outline'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo $tab === 'overview' ? '#5F96B3' : 'transparent'; ?>;">
                    <i class="fas fa-chart-line"></i> Overview
                </a>
                <a href="?tab=users" class="btn btn-<?php echo $tab === 'users' ? 'primary' : 'outline'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo $tab === 'users' ? '#5F96B3' : 'transparent'; ?>;">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="?tab=jobs" class="btn btn-<?php echo $tab === 'jobs' ? 'primary' : 'outline'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo $tab === 'jobs' ? '#5F96B3' : 'transparent'; ?>;">
                    <i class="fas fa-briefcase"></i> Jobs
                </a>
                <a href="?tab=skills" class="btn btn-<?php echo $tab === 'skills' ? 'primary' : 'outline'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo $tab === 'skills' ? '#5F96B3' : 'transparent'; ?>;">
                    <i class="fas fa-graduation-cap"></i> Skills
                </a>
            </div>

            <?php if ($tab === 'overview'): ?>
            <!-- Overview Tab -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users stat-icon stat-blue"></i>
                    <div class="stat-value"><?php echo number_format($userStats['total_users'] ?? 0); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-briefcase stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo number_format($jobStats['total_jobs'] ?? 0); ?></div>
                    <div class="stat-label">Job Posts</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-file-alt stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo number_format($appStats['total_applications'] ?? 0); ?></div>
                    <div class="stat-label">Applications</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-graduation-cap stat-icon stat-blue"></i>
                    <div class="stat-value"><?php echo number_format($skillStats['total_posts'] ?? 0); ?></div>
                    <div class="stat-label">Skill Posts</div>
                </div>
            </div>

            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    RECENT SIGNUPS (LAST 10)
                </div>
                <div class="panel-body">
                    <?php if (empty($recentSignups)): ?>
                        <p class="text-muted text-center">No signups yet</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Name</th><th>Type</th><th>Joined</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSignups as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                        <td><span class="tag tag-<?php echo $u['user_type'] == 'worker' ? 'pink' : 'blue'; ?>" style="font-size: 0.7rem;"><?php echo ucfirst($u['user_type']); ?></span></td>
                                        <td class="text-small"><?php echo timeAgo($u['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($tab === 'users'): ?>
            <!-- Users Tab -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-user-tie stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo number_format($userStats['total_workers'] ?? 0); ?></div>
                    <div class="stat-label">Workers</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-briefcase stat-icon stat-blue"></i>
                    <div class="stat-value"><?php echo number_format($userStats['total_employers'] ?? 0); ?></div>
                    <div class="stat-label">Employers</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle stat-icon" style="color: #28a745;"></i>
                    <div class="stat-value"><?php echo number_format($userStats['active_users'] ?? 0); ?></div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-star stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo number_format($userStats['avg_worker_score'] ?? 0, 2); ?></div>
                    <div class="stat-label">Avg Score</div>
                </div>
            </div>

            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    TOP EMPLOYERS
                </div>
                <div class="panel-body">
                    <?php if (empty($topEmployers)): ?>
                        <p class="text-muted text-center">No employers yet</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Name</th><th>Jobs Posted</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topEmployers as $emp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emp['full_name']); ?></td>
                                        <td><strong><?php echo number_format($emp['job_count']); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($tab === 'jobs'): ?>
            <!-- Jobs Tab -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-list stat-icon stat-blue"></i>
                    <div class="stat-value"><?php echo number_format($jobStats['total_jobs'] ?? 0); ?></div>
                    <div class="stat-label">Total Jobs</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-bolt stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo number_format($jobStats['active_jobs'] ?? 0); ?></div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-double stat-icon" style="color: #28a745;"></i>
                    <div class="stat-value"><?php echo number_format($jobStats['completed_jobs'] ?? 0); ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-peso-sign stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo formatCurrency($jobStats['avg_pay'] ?? 0); ?></div>
                    <div class="stat-label">Avg Pay</div>
                </div>
            </div>

            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    APPLICATIONS BY STATUS
                </div>
                <div class="panel-body">
                    <?php if (empty($appsByStatus)): ?>
                        <p class="text-muted text-center">No applications yet</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Status</th><th>Count</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appsByStatus as $stat): ?>
                                    <tr>
                                        <td><span class="tag tag-<?php echo $stat['application_status'] === 'approved' ? 'green' : ($stat['application_status'] === 'rejected' ? 'red' : 'gray'); ?>" style="font-size: 0.7rem;"><?php echo ucfirst($stat['application_status']); ?></span></td>
                                        <td><strong><?php echo number_format($stat['count']); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($tab === 'skills'): ?>
            <!-- Skills Tab -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-graduation-cap stat-icon stat-blue"></i>
                    <div class="stat-value"><?php echo number_format($skillStats['total_posts'] ?? 0); ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-star stat-icon stat-pink"></i>
                    <div class="stat-value"><?php echo number_format($skillStats['featured_posts'] ?? 0); ?></div>
                    <div class="stat-label">Featured</div>
                </div>
            </div>

            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    RECENT SKILL POSTS
                </div>
                <div class="panel-body">
                    <?php if (empty($topSkills)): ?>
                        <p class="text-muted text-center">No skill posts yet</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Skill</th><th>Level</th><th>Featured</th><th>Added</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topSkills as $skill): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                                        <td><span class="tag" style="font-size: 0.7rem;"><?php echo ucfirst($skill['difficulty_level']); ?></span></td>
                                        <td><?php echo $skill['is_featured'] ? '<i class="fas fa-check" style="color: #28a745;"></i>' : '<i class="fas fa-times" style="color: #dc3545;"></i>'; ?></td>
                                        <td class="text-small"><?php echo timeAgo($skill['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    ADD NEW SKILL POST
                </div>
                <div class="panel-body">
                    <a href="add-skill-post.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Skill Post
                    </a>
                </div>
            </div>

            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    QUICK STATS
                </div>
                <div class="site-info-grid">
                    <div class="site-info-item">
                        <div class="site-info-value" style="color: #5F96B3;"><?php echo number_format($userStats['total_users'] ?? 0); ?></div>
                        <div class="site-info-label">Total Users</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value" style="color: #D792AC;"><?php echo number_format($jobStats['total_jobs'] ?? 0); ?></div>
                        <div class="site-info-label">Job Posts</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value" style="color: #6DD4A4;"><?php echo number_format($skillStats['total_posts'] ?? 0); ?></div>
                        <div class="site-info-label">Skills</div>
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    ACTIONS
                </div>
                <div class="panel-body">
                    <a href="manage-users.php" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="add-skill-post.php" class="btn btn-secondary btn-block" style="margin-bottom: 8px;">
                        <i class="fas fa-plus"></i> Add Skill
                    </a>
                    <a href="dashboard-admin.php" class="btn btn-outline btn-block">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    LAST UPDATED
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        <strong>Users:</strong> <?php echo timeAgo($userStats['last_signup'] ?? null); ?><br>
                        <strong>Jobs:</strong> <?php echo timeAgo($jobStats['last_post'] ?? null); ?><br>
                        <strong>Skills:</strong> <?php echo timeAgo($skillStats['last_post'] ?? null); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
