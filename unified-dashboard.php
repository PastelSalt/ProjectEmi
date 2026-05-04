<?php
/**
 * Unified Dashboard - RaketGo + RaketKo
 * Combined view of job matching and social networking activities
 */
$page_title = 'Unified Dashboard - RaketGo + RaketKo';
require_once 'config/config.php';
requireLogin();
require_once 'includes/header.php';
require_once 'includes/raketko_raketgo_integration.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();
$user_type = getCurrentUserType();

// Sync user profile between platforms
syncUserProfile($user_id, $conn);

// Get unified user profile
$unifiedProfile = getUnifiedUserProfile($user_id, $conn);

// Get cross-platform activities
$activities = getCrossPlatformActivities($user_id, 10, $conn);

// Get unified notifications
$notifications = getUnifiedNotifications($user_id, 5, true, $conn);

// Get network statistics
$networkStats = getUserNetworkStats($user_id, $conn);

// Get trending content
$trendingContent = getTrendingContent('daily', 5, $conn);

// Get recommended content
$recommendedContent = getRecommendedContent($user_id, 5, $conn);

// Get career milestones
$milestones = getCareerMilestones($user_id, true, 3, $conn);

// Get career progress score
$careerProgress = getCareerProgressScore($user_id, $conn);

closeDBConnection($conn);
?>

<div class="container">
    <!-- Unified Dashboard Header -->
    <div class="unified-header" style="background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark)); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; font-size: 2rem;">
                    <span style="color: white;">Raket</span><span style="color: var(--accent-color);">Go</span> + <span style="color: var(--secondary-color);">Raket</span><span style="color: white;">Ko</span>
                </h1>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Your Unified Career Platform</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo round($careerProgress); ?>%</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">Career Progress</div>
            </div>
        </div>
    </div>

    <div class="unified-layout" style="display: grid; grid-template-columns: 300px 1fr 300px; gap: 2rem;">
        <!-- Left Sidebar -->
        <div class="unified-sidebar">
            <!-- Profile Summary -->
            <div class="panel" style="margin-bottom: 1.5rem;">
                <div class="panel-body">
                    <div style="text-align: center; padding: 1rem;">
                        <?php if (!empty($unifiedProfile['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($unifiedProfile['profile_picture']); ?>" 
                                 alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 1rem;">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: white; color: var(--primary-blue); 
                                        display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                                <?php echo mb_strtoupper(mb_substr($unifiedProfile['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <h3 style="margin: 0; color: var(--text-dark);"><?php echo htmlspecialchars($unifiedProfile['combined_headline']); ?></h3>
                        <p style="margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
                            <?php echo htmlspecialchars($unifiedProfile['combined_location']); ?>
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                        <div style="text-align: center;">
                            <div style="font-weight: bold; color: var(--primary-blue);"><?php echo $networkStats['followers'] ?? 0; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Followers</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: bold; color: var(--primary-blue);"><?php echo $networkStats['following'] ?? 0; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Following</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: bold; color: var(--primary-blue);"><?php echo $networkStats['social_posts'] ?? 0; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Posts</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: bold; color: var(--primary-blue);"><?php echo $networkStats['job_posts'] ?? 0; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Jobs</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="panel" style="margin-bottom: 1.5rem;">
                <div class="section-header">
                    <span class="header-square"></span>
                    Quick Actions
                </div>
                <div class="panel-body">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="raketko-feed.php" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-plus"></i> Create Social Post
                        </a>
                        <?php if ($user_type === 'employer'): ?>
                            <a href="post-job.php" class="btn btn-outline btn-small" style="width: 100%;">
                                <i class="fas fa-briefcase"></i> Post Job
                            </a>
                        <?php endif; ?>
                        <a href="raketko-profile.php?id=<?php echo $user_id; ?>" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                        <a href="messages.php" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </div>
                </div>
            </div>

            <!-- Career Milestones -->
            <?php if (!empty($milestones)): ?>
                <div class="panel">
                    <div class="section-header">
                        <span class="header-square"></span>
                        Recent Milestones
                    </div>
                    <div class="panel-body">
                        <?php foreach ($milestones as $milestone): ?>
                            <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-light);">
                                <div style="font-weight: bold; color: var(--text-dark); font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($milestone['title']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?php echo date('M j, Y', strtotime($milestone['milestone_date'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="unified-main">
            <!-- Notifications -->
            <?php if (!empty($notifications)): ?>
                <div class="panel" style="margin-bottom: 1.5rem;">
                    <div class="section-header">
                        <span class="header-square"></span>
                        Recent Notifications
                        <a href="notifications.php" class="btn btn-outline btn-small" style="margin-left: auto;">
                            View All
                        </a>
                    </div>
                    <div class="panel-body">
                        <?php foreach ($notifications as $notification): ?>
                            <div style="display: flex; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
                                <div style="flex-shrink: 0;">
                                    <?php if (!empty($notification['actor_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($notification['actor_picture']); ?>" 
                                             alt="Actor" style="width: 32px; height: 32px; border-radius: 50%;">
                                    <?php else: ?>
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-blue); color: white; 
                                                    display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                            <?php echo mb_strtoupper(mb_substr($notification['actor_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: bold; color: var(--text-dark); font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        <?php echo timeAgo($notification['created_at']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cross-Platform Activities -->
            <?php if (!empty($activities)): ?>
                <div class="panel" style="margin-bottom: 1.5rem;">
                    <div class="section-header">
                        <span class="header-square"></span>
                        Your Activities
                    </div>
                    <div class="panel-body">
                        <?php foreach ($activities as $activity): ?>
                            <div style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $activity['platform'] === 'raketgo' ? 'var(--primary-blue)' : 'var(--secondary-color)'; ?>; color: white; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-<?php echo $activity['platform'] === 'raketgo' ? 'briefcase' : 'users'; ?>"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: bold; color: var(--text-dark);">
                                        <?php echo getActivityTitle($activity['activity_type']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?php echo ucfirst($activity['platform']); ?> • <?php echo timeAgo($activity['created_at']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recommended Content -->
            <?php if (!empty($recommendedContent)): ?>
                <div class="panel">
                    <div class="section-header">
                        <span class="header-square"></span>
                        Recommended For You
                    </div>
                    <div class="panel-body">
                        <?php foreach ($recommendedContent as $content): ?>
                            <div style="padding: 1rem 0; border-bottom: 1px solid var(--border-light);">
                                <?php if ($content['content_type'] === 'job_post'): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 0.5rem 0; color: var(--primary-blue);">
                                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($content['title']); ?>
                                            </h4>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                                <?php echo htmlspecialchars($content['company_name'] ?? 'Company'); ?> • <?php echo htmlspecialchars($content['city']); ?>
                                            </div>
                                            <div style="font-size: 0.9rem; color: var(--text-dark);">
                                                <?php echo substr(htmlspecialchars($content['description']), 0, 150) . '...'; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; margin-left: 1rem;">
                                            <div style="font-weight: bold; color: var(--primary-blue);">
                                                <?php echo formatCurrency($content['pay_amount']); ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?php echo htmlspecialchars($content['pay_type']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 0.5rem;">
                                        <a href="job-details.php?id=<?php echo $content['job_id']; ?>" class="btn btn-primary btn-small">
                                            View Job
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; gap: 1rem;">
                                        <?php if (!empty($content['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($content['profile_picture']); ?>" 
                                                 alt="Author" style="width: 32px; height: 32px; border-radius: 50%;">
                                        <?php else: ?>
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--secondary-color); color: white; 
                                                        display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                <?php echo mb_strtoupper(mb_substr($content['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="flex: 1;">
                                            <div style="font-weight: bold; color: var(--text-dark); font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($content['full_name']); ?>
                                            </div>
                                            <h4 style="margin: 0.25rem 0; color: var(--text-dark);">
                                                <i class="fas fa-users"></i> <?php echo htmlspecialchars($content['title']); ?>
                                            </h4>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?php echo timeAgo($content['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 0.5rem;">
                                        <a href="raketko-profile.php?id=<?php echo $content['user_id']; ?>" class="btn btn-outline btn-small">
                                            View Post
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="unified-sidebar">
            <!-- Trending Content -->
            <?php if (!empty($trendingContent)): ?>
                <div class="panel" style="margin-bottom: 1.5rem;">
                    <div class="section-header">
                        <span class="header-square"></span>
                        Trending Now
                    </div>
                    <div class="panel-body">
                        <?php foreach ($trendingContent as $content): ?>
                            <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-light);">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: <?php echo $content['content_type'] === 'job_post' ? 'var(--primary-blue)' : 'var(--secondary-color)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                                        <i class="fas fa-<?php echo $content['content_type'] === 'job_post' ? 'briefcase' : 'users'; ?>"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: bold; color: var(--text-dark); font-size: 0.85rem;">
                                            <?php echo htmlspecialchars(substr($content['title'], 0, 30)) . '...'; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?php echo $content['engagement_count']; ?> engagements
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Platform Stats -->
            <div class="panel" style="margin-bottom: 1.5rem;">
                <div class="section-header">
                    <span class="header-square"></span>
                    Platform Stats
                </div>
                <div class="panel-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                        <div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-blue);">
                                <?php echo $networkStats['job_applications'] ?? 0; ?>
                            </div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">Applications</div>
                        </div>
                        <div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: var(--secondary-color);">
                                <?php echo $networkStats['avg_rating'] ? number_format($networkStats['avg_rating'], 1) : '0.0'; ?>
                            </div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">Avg Rating</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    Quick Links
                </div>
                <div class="panel-body">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="raketko-feed.php" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-users"></i> RaketKo Feed
                        </a>
                        <a href="index.php" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-home"></i> Job Board
                        </a>
                        <a href="for-you.php" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-compass"></i> Discover
                        </a>
                        <a href="skill-learn.php" class="btn btn-outline btn-small" style="width: 100%;">
                            <i class="fas fa-graduation-cap"></i> Learn
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getActivityTitle($activity_type) {
    $titles = [
        'job_application' => 'Applied for a job',
        'job_post' => 'Posted a new job',
        'social_post' => 'Created a social post',
        'profile_update' => 'Updated profile',
        'connection' => 'New connection',
        'skill_update' => 'Updated skills',
        'portfolio_update' => 'Updated portfolio'
    ];
    
    return $titles[$activity_type] ?? 'Activity';
}
?>

<?php require_once 'includes/footer.php'; ?>
