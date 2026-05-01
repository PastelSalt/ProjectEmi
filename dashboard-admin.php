<?php
/**
 * Admin Dashboard
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Admin Dashboard';
require_once 'config/config.php';
requireUserType('admin');
require_once 'includes/header.php';

$conn = getDBConnection();

$userStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN user_type = 'worker' THEN 1 ELSE 0 END) as total_workers,
    SUM(CASE WHEN user_type = 'employer' THEN 1 ELSE 0 END) as total_employers,
    SUM(CASE WHEN account_status = 'active' THEN 1 ELSE 0 END) as active_users
    FROM users WHERE user_type != 'admin'");

$jobStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_jobs,
    SUM(CASE WHEN job_status = 'active' THEN 1 ELSE 0 END) as active_jobs,
    SUM(CASE WHEN job_status = 'completed' THEN 1 ELSE 0 END) as completed_jobs
    FROM job_posts");

$appStats = fetchOne($conn, "SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN application_status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
    SUM(CASE WHEN worker_confirmed = 1 AND employer_confirmed = 1 THEN 1 ELSE 0 END) as completed_work
    FROM job_applications");

$skillPostsCount = fetchOne($conn, "SELECT COUNT(*) as total_posts FROM skill_posts");

$recentUsers = fetchAll($conn, "SELECT user_id, full_name, user_type, city, province, created_at FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 5");
$recentJobs = fetchAll($conn, "SELECT j.*, u.full_name as employer_name FROM job_posts j JOIN users u ON j.employer_id = u.user_id ORDER BY j.created_at DESC LIMIT 5");
?>

<div class="container admin-dashboard">
    <!-- Stats Row 1: Users -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-users stat-icon stat-blue"></i>
            <div class="stat-value"><?php echo number_format($userStats['total_users'] ?? 0); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-tie stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo number_format($userStats['total_workers'] ?? 0); ?></div>
            <div class="stat-label">Workers</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-briefcase stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo number_format($userStats['total_employers'] ?? 0); ?></div>
            <div class="stat-label">Employers</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle stat-icon" style="color: #28a745;"></i>
            <div class="stat-value"><?php echo number_format($userStats['active_users'] ?? 0); ?></div>
            <div class="stat-label">Active Users</div>
        </div>
    </div>

    <!-- Stats Row 2: Jobs -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-list stat-icon stat-blue"></i>
            <div class="stat-value"><?php echo number_format($jobStats['total_jobs'] ?? 0); ?></div>
            <div class="stat-label">Total Jobs</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-bolt stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo number_format($jobStats['active_jobs'] ?? 0); ?></div>
            <div class="stat-label">Active Jobs</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-file-alt stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo number_format($appStats['total_applications'] ?? 0); ?></div>
            <div class="stat-label">Applications</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-double stat-icon" style="color: #28a745;"></i>
            <div class="stat-value"><?php echo number_format($appStats['completed_work'] ?? 0); ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Quick Actions -->
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    ADMIN QUICK ACTIONS
                </div>
                <div class="panel-body">
                    <div class="grid grid-3">
                        <a href="add-skill-post.php" class="quick-action" style="text-decoration: none;">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Skill Posts</h3>
                            <p><?php echo $skillPostsCount['total_posts'] ?? 0; ?> total</p>
                            <span class="btn btn-primary btn-small"><i class="fas fa-plus"></i> Add</span>
                        </a>
                        <a href="manage-users.php" class="quick-action" style="text-decoration: none;">
                            <i class="fas fa-users"></i>
                            <h3>Users</h3>
                            <p>Manage accounts</p>
                            <span class="btn btn-secondary btn-small"><i class="fas fa-cog"></i> Manage</span>
                        </a>
                        <a href="analytics.php" class="quick-action" style="text-decoration: none;">
                            <i class="fas fa-chart-line"></i>
                            <h3>Analytics</h3>
                            <p>View reports</p>
                            <span class="btn btn-outline btn-small"><i class="fas fa-chart-bar"></i> View</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    RECENT USERS
                </div>
                <div class="panel-body">
                    <?php if (empty($recentUsers)): ?>
                        <p class="text-muted text-center" style="padding: 1rem;">No users yet</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Name</th><th>Type</th><th>Location</th><th>Joined</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $u): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                                        <td><span class="tag tag-<?php echo $u['user_type'] == 'worker' ? 'pink' : 'blue'; ?>" style="font-size: 0.65rem;"><?php echo ucfirst($u['user_type']); ?></span></td>
                                        <td class="text-small"><?php echo htmlspecialchars($u['city'] . ', ' . $u['province']); ?></td>
                                        <td class="text-xs text-muted"><?php echo timeAgo($u['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Jobs -->
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    RECENT JOB POSTS
                </div>
                <div class="panel-body">
                    <?php if (empty($recentJobs)): ?>
                        <p class="text-muted text-center" style="padding: 1rem;">No jobs yet</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Job</th><th>Employer</th><th>Location</th><th>Pay</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job): ?>
                                    <tr onclick="window.location.href='job-details.php?id=<?php echo $job['job_id']; ?>'" style="cursor: pointer;">
                                        <td><strong><?php echo htmlspecialchars($job['job_title']); ?></strong></td>
                                        <td class="text-small"><?php echo htmlspecialchars($job['employer_name']); ?></td>
                                        <td class="text-small"><?php echo htmlspecialchars($job['location_city']); ?></td>
                                        <td class="text-small"><?php echo formatCurrency($job['pay_amount']); ?></td>
                                        <td><span class="tag tag-<?php echo $job['job_status'] == 'active' ? 'green' : 'gray'; ?>" style="font-size: 0.65rem;"><?php echo ucfirst($job['job_status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    PLATFORM STATUS
                </div>
                <div class="site-info-grid">
                    <div class="site-info-item">
                        <div class="site-info-value green">Active</div>
                        <div class="site-info-label">Status</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value cyan">1.0.0</div>
                        <div class="site-info-label">Version</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value pink"><?php echo number_format($skillPostsCount['total_posts'] ?? 0); ?></div>
                        <div class="site-info-label">Posts</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value blue"><?php echo number_format($userStats['total_users'] ?? 0); ?></div>
                        <div class="site-info-label">Users</div>
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    ENVIRONMENT
                </div>
                <div class="panel-body">
                    <table class="data-table" style="font-size: 0.78rem;">
                        <tr><td class="text-muted">Platform</td><td class="text-right">PHP 7.4+</td></tr>
                        <tr><td class="text-muted">Database</td><td class="text-right">MySQL</td></tr>
                        <tr><td class="text-muted">Framework</td><td class="text-right">Custom MVC</td></tr>
                        <tr><td class="text-muted">Region</td><td class="text-right">Philippines</td></tr>
                    </table>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    ADMIN NOTICE
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        Manage the platform from this dashboard. Monitor users, jobs, and skill posts. 
                        Use the quick actions above for common tasks.
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
