<?php
/**
 * Worker Profile/Portfolio Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Worker Profile';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();
$worker_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($worker_id == 0) {
    redirect('index.php');
}

// Get worker basic information
$worker = fetchOne(
    $conn,
    "SELECT u.user_id, u.full_name, u.mobile_number, u.email, u.region, u.province, u.city,
            u.bio, u.trust_score, u.profile_picture, u.created_at, u.last_login,
            u.account_status
     FROM users u 
     WHERE u.user_id = ? AND u.user_type = 'worker'",
    [$worker_id],
    'i'
);

if (!$worker) {
    redirect('index.php');
}

// Get worker skills
$skills = fetchAll(
    $conn,
    "SELECT skill_name, proficiency_level, is_verified, verification_document, verified_at
     FROM user_skills 
     WHERE user_id = ? 
     ORDER BY is_verified DESC, proficiency_level DESC, skill_name ASC",
    [$worker_id],
    'i'
);

// Get worker portfolio items
$portfolio = fetchAll(
    $conn,
    "SELECT portfolio_id, title, description, image_path, project_url, skills_used, 
            is_featured, views_count, created_at, updated_at
     FROM worker_portfolio 
     WHERE worker_id = ? 
     ORDER BY is_featured DESC, created_at DESC",
    [$worker_id],
    'i'
);

// Get worker's job application history (completed and approved jobs)
$jobHistory = fetchAll(
    $conn,
    "SELECT ja.application_id, ja.job_id, ja.application_status, ja.applied_at, ja.reviewed_at,
            ja.worker_confirmed, ja.employer_confirmed, ja.work_start_time, ja.work_end_time,
            ja.payment_completed, ja.both_confirmed_at,
            jp.job_title, jp.job_description, jp.pay_amount, jp.pay_type, jp.job_type,
            jp.location_region, jp.location_province, jp.location_city,
            e.full_name as employer_name, e.company_name, e.employer_subtype,
            jr.rating_stars as employer_rating, jr.feedback as employer_feedback,
            dc.contract_content, dc.meeting_time
     FROM job_applications ja
     JOIN job_posts jp ON ja.job_id = jp.job_id
     JOIN users e ON ja.employer_id = e.user_id
     LEFT JOIN job_ratings jr ON ja.application_id = jr.application_id 
                               AND jr.rating_type = 'employer_to_worker'
     LEFT JOIN digital_contracts dc ON ja.application_id = dc.application_id
     WHERE ja.worker_id = ? AND ja.application_status IN ('approved', 'completed')
     ORDER BY ja.applied_at DESC",
    [$worker_id],
    'i'
);

// Get worker's ratings received from employers
$ratings = fetchAll(
    $conn,
    "SELECT jr.rating_stars, jr.feedback, jr.created_at,
            jp.job_title, e.full_name as employer_name, e.company_name
     FROM job_ratings jr
     JOIN job_applications ja ON jr.application_id = ja.application_id
     JOIN job_posts jp ON ja.job_id = jp.job_id
     JOIN users e ON ja.employer_id = e.user_id
     WHERE jr.ratee_id = ? AND jr.rating_type = 'employer_to_worker'
     ORDER BY jr.created_at DESC",
    [$worker_id],
    'i'
);

// Calculate statistics
$totalApplications = count($jobHistory);
$completedJobs = count(array_filter($jobHistory, function($job) {
    return $job['payment_completed'] == 1;
}));
$averageRating = 0;
if (!empty($ratings)) {
    $totalRating = array_sum(array_column($ratings, 'rating_stars'));
    $averageRating = $totalRating / count($ratings);
}

// Increment portfolio view count
if (isLoggedIn() && getCurrentUserType() == 'employer') {
    executeQuery($conn, "UPDATE worker_portfolio SET views_count = views_count + 1 WHERE worker_id = ?", [$worker_id], 'i');
}

closeDBConnection($conn);
?>

<div class="container">
    <!-- Worker Profile Header -->
    <div class="panel">
        <div class="section-header">
            <span class="header-square"></span>
            WORKER PROFILE ─ <?php echo htmlspecialchars(strtoupper($worker['full_name'])); ?>
        </div>
        <div class="panel-body">
            <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                <!-- Profile Picture -->
                <div style="flex-shrink: 0;">
                    <?php if (!empty($worker['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($worker['profile_picture']); ?>" 
                             alt="<?php echo htmlspecialchars($worker['full_name']); ?>"
                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-blue-light);">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary-blue); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700;">
                            <?php echo strtoupper(substr($worker['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Basic Info -->
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 1.5rem;">
                        <?php echo htmlspecialchars($worker['full_name']); ?>
                    </h2>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($worker['city'] . ', ' . $worker['province']); ?></span>
                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($worker['mobile_number']); ?></span>
                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($worker['email']); ?></span>
                    </div>
                    
                    <!-- Trust Score -->
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary-blue-dark);">
                                <?php echo number_format($worker['trust_score'], 2); ?>
                            </span>
                            <div style="color: #FFD700;">
                                <?php 
                                $stars = round($worker['trust_score']);
                                for ($i = 1; $i <= 5; $i++):
                                    echo $i <= $stars ? '★' : '☆';
                                endfor; 
                                ?>
                            </div>
                        </div>
                        <span class="text-muted">Trust Score</span>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div style="display: flex; gap: 2rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-blue-dark);"><?php echo $totalApplications; ?></div>
                            <div class="text-muted" style="font-size: 0.8rem;">Total Applications</div>
                        </div>
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-pink);"><?php echo $completedJobs; ?></div>
                            <div class="text-muted" style="font-size: 0.8rem;">Completed Jobs</div>
                        </div>
                        <?php if ($averageRating > 0): ?>
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: #FFD700;"><?php echo number_format($averageRating, 1); ?></div>
                            <div class="text-muted" style="font-size: 0.8rem;">Average Rating</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Bio -->
                    <?php if (!empty($worker['bio'])): ?>
                    <div style="padding: 1rem; background: var(--off-white); border-radius: 8px; margin-bottom: 1rem;">
                        <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 0.9rem;">About</h4>
                        <p style="margin: 0; color: var(--text-muted); line-height: 1.6; font-size: 0.85rem;">
                            <?php echo nl2br(htmlspecialchars($worker['bio'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons for Employers -->
                    <?php if (isLoggedIn() && getCurrentUserType() == 'employer'): ?>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="messages.php?user=<?php echo $worker['user_id']; ?>" class="btn btn-primary btn-small">
                            <i class="fas fa-envelope"></i> Message Worker
                        </a>
                        <button onclick="window.history.back()" class="btn btn-outline btn-small">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Skills Section -->
            <?php if (!empty($skills)): ?>
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    SKILLS &amp; EXPERTISE
                </div>
                <div class="panel-body">
                    <div class="tech-tags">
                        <?php foreach ($skills as $skill): ?>
                            <span class="tag <?php echo $skill['is_verified'] ? 'tag-green' : 'tag-pink'; ?>" style="position: relative;">
                                <?php echo htmlspecialchars($skill['skill_name']); ?>
                                <?php if ($skill['is_verified']): ?>
                                    <i class="fas fa-check-circle" style="color: var(--green-badge); margin-left: 4px; font-size: 0.7rem;" title="Verified Skill"></i>
                                <?php endif; ?>
                                <span class="text-muted" style="font-size: 0.6rem; margin-left: 4px;">
                                    <?php echo ucfirst($skill['proficiency_level']); ?>
                                </span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Portfolio Section -->
            <?php if (!empty($portfolio)): ?>
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    PORTFOLIO (<?php echo count($portfolio); ?> items)
                </div>
                <div class="panel-body">
                    <?php foreach ($portfolio as $item): ?>
                        <div class="compact-job-item" style="padding: 1rem 0; border-bottom: 1px solid var(--border-light);">
                            <?php if ($item['is_featured']): ?>
                                <span class="tag tag-green" style="font-size: 0.65rem; margin-bottom: 0.5rem;">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            <?php endif; ?>
                            
                            <h4 style="margin: 0.5rem 0; color: var(--text-dark); font-size: 1rem;">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h4>
                            
                            <?php if (!empty($item['description'])): ?>
                                <p style="margin: 0.5rem 0; color: var(--text-muted); line-height: 1.5; font-size: 0.85rem;">
                                    <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['image_path'])): ?>
                                <div style="margin: 0.5rem 0;">
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         style="max-width: 100%; max-height: 200px; border-radius: 6px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['skills_used'])): ?>
                                <div style="margin: 0.5rem 0;">
                                    <span class="text-muted" style="font-size: 0.75rem;">Skills used:</span>
                                    <div class="tech-tags" style="margin-top: 0.25rem;">
                                        <?php foreach (explode(',', $item['skills_used']) as $skill): ?>
                                            <span class="tag tag-outline" style="font-size: 0.6rem;">
                                                <?php echo htmlspecialchars(trim($skill)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['project_url'])): ?>
                                <div style="margin: 0.5rem 0;">
                                    <a href="<?php echo htmlspecialchars($item['project_url']); ?>" 
                                       target="_blank" class="btn btn-outline btn-small" style="font-size: 0.7rem;">
                                        <i class="fas fa-external-link-alt"></i> View Project
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 0.5rem; font-size: 0.7rem; color: var(--text-muted);">
                                <i class="fas fa-eye"></i> <?php echo number_format($item['views_count']); ?> views • 
                                Added <?php echo timeAgo($item['created_at']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Employment History Section -->
            <?php if (!empty($jobHistory)): ?>
            <div class="panel">
                <div class="section-header section-header-blue">
                    <span class="header-square"></span>
                    EMPLOYMENT HISTORY (<?php echo count($jobHistory); ?> jobs)
                </div>
                <div class="panel-body">
                    <?php foreach ($jobHistory as $job): ?>
                        <div class="compact-job-item" style="padding: 1rem 0; border-bottom: 1px solid var(--border-light);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <h4 style="margin: 0; color: var(--text-dark); font-size: 1rem;">
                                        <?php echo htmlspecialchars($job['job_title']); ?>
                                    </h4>
                                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                                        with <?php echo htmlspecialchars($job['employer_name']); ?>
                                        <?php if (!empty($job['company_name'])): ?>
                                            (<?php echo htmlspecialchars($job['company_name']); ?>)
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="tag tag-<?php echo $job['payment_completed'] ? 'green' : 'blue'; ?>" style="font-size: 0.65rem;">
                                        <?php echo $job['payment_completed'] ? 'Completed' : 'In Progress'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location_city'] . ', ' . $job['location_province']); ?> •
                                <i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?> / <?php echo $job['pay_type']; ?> •
                                <i class="fas fa-briefcase"></i> <?php echo ucwords(str_replace('_', ' ', $job['job_type'])); ?>
                            </div>
                            
                            <?php if (!empty($job['employer_rating'])): ?>
                                <div style="margin: 0.5rem 0; padding: 0.5rem; background: var(--off-white); border-radius: 4px;">
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                        Employer Rating:
                                    </div>
                                    <div style="color: #FFD700; font-size: 0.9rem;">
                                        <?php echo str_repeat('★', $job['employer_rating']) . str_repeat('☆', 5 - $job['employer_rating']); ?>
                                        <span style="color: var(--text-muted); margin-left: 0.5rem;"><?php echo $job['employer_rating']; ?>/5</span>
                                    </div>
                                    <?php if (!empty($job['employer_feedback'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; font-style: italic;">
                                            "<?php echo htmlspecialchars($job['employer_feedback']); ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="font-size: 0.7rem; color: var(--text-muted);">
                                Applied <?php echo timeAgo($job['applied_at']); ?>
                                <?php if ($job['reviewed_at']): ?>
                                    • Approved <?php echo timeAgo($job['reviewed_at']); ?>
                                <?php endif; ?>
                                <?php if ($job['both_confirmed_at']): ?>
                                    • Completed <?php echo timeAgo($job['both_confirmed_at']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Ratings Summary -->
            <?php if (!empty($ratings)): ?>
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    RATINGS SUMMARY
                </div>
                <div class="panel-body text-center">
                    <div style="font-size: 2rem; color: #FFD700; margin-bottom: 0.5rem;">
                        <?php echo str_repeat('★', round($averageRating)) . str_repeat('☆', 5 - round($averageRating)); ?>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">
                        <?php echo number_format($averageRating, 1); ?>/5.0
                    </div>
                    <div class="text-muted" style="font-size: 0.8rem;">
                        Based on <?php echo count($ratings); ?> reviews
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Reviews -->
            <?php if (!empty($ratings)): ?>
            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    RECENT REVIEWS
                </div>
                <div class="panel-body">
                    <?php foreach (array_slice($ratings, 0, 3) as $rating): ?>
                        <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-light);">
                            <div style="color: #FFD700; font-size: 0.8rem; margin-bottom: 0.25rem;">
                                <?php echo str_repeat('★', $rating['rating_stars']) . str_repeat('☆', 5 - $rating['rating_stars']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                from <?php echo htmlspecialchars($rating['employer_name']); ?>
                                <?php if (!empty($rating['company_name'])): ?>
                                    (<?php echo htmlspecialchars($rating['company_name']); ?>)
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($rating['feedback'])): ?>
                                <div style="font-size: 0.8rem; color: var(--text-dark); line-height: 1.4;">
                                    "<?php echo htmlspecialchars(substr($rating['feedback'], 0, 100)); ?><?php echo strlen($rating['feedback']) > 100 ? '...' : ''; ?>"
                                </div>
                            <?php endif; ?>
                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">
                                <?php echo timeAgo($rating['created_at']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Account Info -->
            <div class="widget">
                <div class="section-header section-header-blue">
                    <span class="header-square"></span>
                    ACCOUNT INFO
                </div>
                <div class="panel-body">
                    <table class="data-table" style="font-size: 0.78rem;">
                        <tr><td class="text-muted">Member Since</td><td><?php echo date('F Y', strtotime($worker['created_at'])); ?></td></tr>
                        <tr><td class="text-muted">Last Active</td><td><?php echo $worker['last_login'] ? timeAgo($worker['last_login']) : 'Never'; ?></td></tr>
                        <tr><td class="text-muted">Account Status</td><td><span class="tag tag-<?php echo $worker['account_status'] == 'active' ? 'green' : 'red'; ?>"><?php echo ucfirst($worker['account_status']); ?></span></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
