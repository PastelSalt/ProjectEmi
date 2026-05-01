<?php
/**
 * Employer Public Profile Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 * 
 * This page displays a public profile for employers (both companies and individuals)
 * showing their history, ratings, active jobs, and other public information.
 */

$page_title = 'Employer Profile';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get employer ID from URL
$employer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($employer_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch employer details
$employer = fetchOne($conn,
    "SELECT user_id, full_name, user_type, employer_subtype, bio, profile_picture, 
            region, province, city, trust_score, is_verified, created_at
     FROM users 
     WHERE user_id = ? AND user_type = 'employer' AND account_status = 'active'",
    [$employer_id], 'i'
);

if (!$employer) {
    header('Location: index.php');
    exit;
}

// Get employer type label
$employerTypeConfig = [
    'company' => ['label' => 'Company', 'icon' => 'fa-building', 'color' => 'blue'],
    'individual' => ['label' => 'Individual', 'icon' => 'fa-user', 'color' => 'green'],
    null => ['label' => 'Employer', 'icon' => 'fa-briefcase', 'color' => 'gray']
];
$typeInfo = $employerTypeConfig[$employer['employer_subtype']] ?? $employerTypeConfig[null];

// Fetch employer statistics
$statsSql = "SELECT 
    (SELECT COUNT(*) FROM job_posts WHERE employer_id = ? AND job_status = 'active') as active_jobs,
    (SELECT COUNT(*) FROM job_posts WHERE employer_id = ? AND job_status = 'completed') as completed_jobs,
    (SELECT COUNT(*) FROM job_posts WHERE employer_id = ?) as total_jobs_posted,
    (SELECT COUNT(*) FROM job_applications ja 
     JOIN job_posts jp ON ja.job_id = jp.job_id 
     WHERE jp.employer_id = ? AND ja.application_status = 'approved') as total_hires,
    (SELECT AVG(rating) FROM employer_reviews WHERE employer_id = ?) as avg_rating,
    (SELECT COUNT(*) FROM employer_reviews WHERE employer_id = ?) as total_reviews";

$stats = fetchOne($conn, $statsSql, [$employer_id, $employer_id, $employer_id, $employer_id, $employer_id, $employer_id], 'iiiiii');

// Fetch active jobs
$activeJobs = fetchAll($conn,
    "SELECT j.*, 
            (SELECT COUNT(*) FROM job_applications WHERE job_id = j.job_id AND application_status = 'pending') as pending_applications
     FROM job_posts j
     WHERE j.employer_id = ? AND j.job_status = 'active'
     ORDER BY j.created_at DESC",
    [$employer_id], 'i'
);

// Fetch completed jobs (recent 10)
$completedJobs = fetchAll($conn,
    "SELECT j.*, 
            (SELECT COUNT(*) FROM job_applications WHERE job_id = j.job_id AND application_status = 'approved') as hired_count
     FROM job_posts j
     WHERE j.employer_id = ? AND j.job_status = 'completed'
     ORDER BY j.updated_at DESC
     LIMIT 10",
    [$employer_id], 'i'
);

// Fetch reviews with worker details
$reviews = fetchAll($conn,
    "SELECT er.*, u.full_name as worker_name, u.profile_picture as worker_picture
     FROM employer_reviews er
     JOIN users u ON er.worker_id = u.user_id
     WHERE er.employer_id = ?
     ORDER BY er.created_at DESC
     LIMIT 10",
    [$employer_id], 'i'
);

// Check if current user can review (must be worker who completed a job with this employer)
$canReview = false;
if (isLoggedIn() && getCurrentUserType() === 'worker') {
    $worker_id = getCurrentUserId();
    $completedWork = fetchOne($conn,
        "SELECT COUNT(*) as count FROM job_applications ja
         JOIN job_posts jp ON ja.job_id = jp.job_id
         WHERE ja.worker_id = ? AND jp.employer_id = ? AND ja.application_status = 'completed'",
        [$worker_id, $employer_id], 'ii'
    );
    $alreadyReviewed = fetchOne($conn,
        "SELECT COUNT(*) as count FROM employer_reviews WHERE employer_id = ? AND worker_id = ?",
        [$employer_id, $worker_id], 'ii'
    );
    $canReview = ($completedWork && $completedWork['count'] > 0 && $alreadyReviewed && $alreadyReviewed['count'] == 0);
}

// Format member since date
$memberSince = date('F Y', strtotime($employer['created_at']));

// Get current user ID for checking if viewing own profile
$currentUserId = isLoggedIn() ? getCurrentUserId() : 0;
$isOwnProfile = ($currentUserId == $employer_id);
?>

<div class="container">
    <!-- Employer Profile Header -->
    <div class="panel">
        <div class="panel-body" style="padding: 1.5rem;">
            <div class="d-flex align-center gap-3" style="flex-wrap: wrap;">
                <!-- Profile Picture -->
                <div style="position: relative;">
                    <?php if (!empty($employer['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($employer['profile_picture']); ?>" 
                             alt="<?php echo htmlspecialchars($employer['full_name']); ?>" 
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-blue);">
                    <?php else: ?>
                        <div class="message-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; background: var(--primary-blue);">
                            <?php echo mb_strtoupper(mb_substr($employer['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($employer['is_verified']): ?>
                        <span style="position: absolute; bottom: 0; right: 0; background: var(--success-green); color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 2px solid white;">
                            <i class="fas fa-check"></i>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Profile Info -->
                <div style="flex: 1; min-width: 250px;">
                    <div class="d-flex align-center gap-2 flex-wrap" style="margin-bottom: 8px;">
                        <h2 style="margin: 0;"><?php echo htmlspecialchars($employer['full_name']); ?></h2>
                        <span class="tag" style="background: var(--<?php echo $typeInfo['color']; ?>-light, #e8f4f8); color: var(--<?php echo $typeInfo['color']; ?>-dark, #1565c0); font-size: 0.75rem;">
                            <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
                            <?php echo $typeInfo['label']; ?>
                        </span>
                        <?php if ($employer['is_verified']): ?>
                            <span class="tag tag-green" style="font-size: 0.75rem;">
                                <i class="fas fa-shield-alt"></i> Verified
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-3 flex-wrap text-small text-muted" style="margin-bottom: 8px;">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($employer['city'] . ', ' . $employer['province']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> Member since <?php echo $memberSince; ?></span>
                        <span><i class="fas fa-star" style="color: #FFD700;"></i> 
                            <?php echo $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 'No ratings'; ?>
                            <?php if ($stats['total_reviews'] > 0): ?>
                                (<?php echo $stats['total_reviews']; ?> reviews)
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if (!empty($employer['bio'])): ?>
                        <p style="color: var(--text-dark); margin: 8px 0 0 0; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($employer['bio'])); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column gap-2">
                    <?php if ($isOwnProfile): ?>
                        <a href="dashboard-employer.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="edit-profile.php" class="btn btn-outline btn-small">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    <?php else: ?>
                        <a href="messages.php?user=<?php echo $employer_id; ?>" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Message
                        </a>
                        <?php if (isLoggedIn() && getCurrentUserType() === 'worker'): ?>
                            <a href="index.php?employer=<?php echo $employer_id; ?>" class="btn btn-outline btn-small">
                                <i class="fas fa-briefcase"></i> View All Jobs
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="grid grid-4" style="margin-top: 1rem;">
        <div class="panel" style="text-align: center;">
            <div class="panel-body">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-blue-dark);"><?php echo (int)$stats['active_jobs']; ?></div>
                <div class="text-small text-muted">Active Jobs</div>
            </div>
        </div>
        <div class="panel" style="text-align: center;">
            <div class="panel-body">
                <div style="font-size: 2rem; font-weight: 700; color: var(--success-green);"><?php echo (int)$stats['completed_jobs']; ?></div>
                <div class="text-small text-muted">Completed Jobs</div>
            </div>
        </div>
        <div class="panel" style="text-align: center;">
            <div class="panel-body">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-pink);"><?php echo (int)$stats['total_hires']; ?></div>
                <div class="text-small text-muted">Total Hires</div>
            </div>
        </div>
        <div class="panel" style="text-align: center;">
            <div class="panel-body">
                <div style="font-size: 2rem; font-weight: 700; color: var(--accent-orange);"><?php echo (int)$stats['total_jobs_posted']; ?></div>
                <div class="text-small text-muted">Jobs Posted</div>
            </div>
        </div>
    </div>

    <div class="layout-two-col" style="margin-top: 1rem;">
        <!-- Main Content -->
        <div>
            <!-- Active Jobs -->
            <?php if (!empty($activeJobs)): ?>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    ACTIVE JOBS
                    <span class="tag tag-blue" style="font-size: 0.7rem; margin-left: 8px;"><?php echo count($activeJobs); ?> open</span>
                </div>
                <div class="panel-body">
                    <?php foreach ($activeJobs as $job): ?>
                        <div class="compact-job-item" onclick="window.location.href='job-details.php?id=<?php echo $job['job_id']; ?>'" style="cursor: pointer;">
                            <div class="d-flex justify-between align-center">
                                <h4 style="color: var(--primary-blue-dark); margin: 0;">
                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                </h4>
                                <span class="text-xs text-muted"><?php echo timeAgo($job['created_at']); ?></span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap" style="margin-top: 6px;">
                                <span class="text-small text-muted">
                                    <i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?>/<?php echo $job['pay_type']; ?>
                                </span>
                                <span class="text-small text-muted">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location_city']); ?>
                                </span>
                                <?php if ($job['pending_applications'] > 0): ?>
                                    <span class="tag tag-yellow" style="font-size: 0.7rem;">
                                        <i class="fas fa-user-clock"></i> <?php echo $job['pending_applications']; ?> pending
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($job['required_skills'])): ?>
                                <div class="d-flex gap-1 flex-wrap" style="margin-top: 6px;">
                                    <?php foreach (array_slice(explode(',', $job['required_skills']), 0, 3) as $skill): ?>
                                        <span class="tag" style="font-size: 0.7rem;"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Job History -->
            <?php if (!empty($completedJobs)): ?>
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    COMPLETED JOBS
                </div>
                <div class="panel-body">
                    <?php foreach ($completedJobs as $job): ?>
                        <div class="compact-job-item" style="opacity: 0.8;">
                            <div class="d-flex justify-between align-center">
                                <h4 style="margin: 0; color: var(--text-dark);">
                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                </h4>
                                <span class="tag tag-green" style="font-size: 0.7rem;">Completed</span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap" style="margin-top: 6px;">
                                <span class="text-small text-muted">
                                    <i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?>
                                </span>
                                <span class="text-small text-muted">
                                    <i class="fas fa-users"></i> <?php echo $job['hired_count']; ?> hired
                                </span>
                                <span class="text-small text-muted">
                                    Completed <?php echo timeAgo($job['updated_at']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($activeJobs) && empty($completedJobs)): ?>
            <div class="panel">
                <div class="panel-body text-center" style="padding: 3rem;">
                    <i class="fas fa-briefcase" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3>No Jobs Yet</h3>
                    <p class="text-muted">This employer hasn't posted any jobs yet.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Reviews Section -->
            <div class="widget">
                <div class="section-header section-header-pink sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-star"></i> Reviews</span>
                    <?php if ($stats['total_reviews'] > 0): ?>
                        <span class="tag tag-pink" style="font-size: 0.7rem; margin-left: auto;"><?php echo $stats['total_reviews']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="panel-body">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div style="padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                                <div class="d-flex align-center gap-2" style="margin-bottom: 6px;">
                                    <?php if (!empty($review['worker_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($review['worker_picture']); ?>" 
                                             style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="message-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <?php echo mb_strtoupper(mb_substr($review['worker_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-small" style="font-weight: 600;"><?php echo htmlspecialchars($review['worker_name']); ?></div>
                                        <div style="color: #FFD700; font-size: 0.75rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-empty'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($review['review_text'])): ?>
                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-dark); line-height: 1.5;">
                                        <?php echo htmlspecialchars($review['review_text']); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="text-xs text-muted" style="margin-top: 6px;">
                                    <?php echo timeAgo($review['created_at']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center" style="padding: 1.5rem;">
                            <i class="fas fa-star" style="font-size: 2rem; color: var(--text-light); margin-bottom: 0.5rem;"></i>
                            <p class="text-muted text-small">No reviews yet</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($canReview): ?>
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                            <a href="rate-employer.php?employer_id=<?php echo $employer_id; ?>" class="btn btn-primary btn-small btn-block">
                                <i class="fas fa-star"></i> Write a Review
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Trust Score Info -->
            <?php if ($employer['trust_score'] > 0): ?>
            <div class="widget">
                <div class="section-header section-header-green sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-shield-alt"></i> Trust Score</span>
                </div>
                <div class="panel-body text-center">
                    <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-pink);">
                        <?php echo number_format($employer['trust_score'], 2); ?>
                    </div>
                    <div class="text-small text-muted" style="margin-top: 4px;">
                        out of 5.00
                    </div>
                    <div style="margin-top: 12px; padding: 8px; background: var(--off-white); border-radius: 8px; font-size: 0.8rem;">
                        <?php if ($employer['trust_score'] >= 4.5): ?>
                            <i class="fas fa-trophy" style="color: var(--success-green);"></i> Top Rated Employer
                        <?php elseif ($employer['trust_score'] >= 4.0): ?>
                            <i class="fas fa-check-circle" style="color: var(--success-green);"></i> Highly Trusted
                        <?php elseif ($employer['trust_score'] >= 3.0): ?>
                            <i class="fas fa-thumbs-up" style="color: var(--primary-blue);"></i> Good Standing
                        <?php else: ?>
                            <i class="fas fa-info-circle" style="color: var(--text-muted);"></i> Building Reputation
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Location Info -->
            <div class="widget">
                <div class="section-header section-header-gray sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-map-marker-alt"></i> Location</span>
                </div>
                <div class="panel-body">
                    <div style="font-size: 0.9rem; line-height: 1.6;">
                        <div style="margin-bottom: 4px;"><strong><?php echo htmlspecialchars($employer['city']); ?></strong></div>
                        <div class="text-small text-muted"><?php echo htmlspecialchars($employer['province']); ?></div>
                        <div class="text-small text-muted"><?php echo htmlspecialchars($employer['region']); ?></div>
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
