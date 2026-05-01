<?php
/**
 * For You Page (Personalized Recommendations)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'For You';
require_once 'config/config.php';
requireLogin();
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();
$user_type = getCurrentUserType();

$user = fetchOne($conn, "SELECT * FROM users WHERE user_id = ?", [$user_id], 'i');

$userSkills = [];
if ($user_type == 'worker') {
    $skills = fetchAll($conn, "SELECT skill_name FROM user_skills WHERE user_id = ?", [$user_id], 'i');
    foreach ($skills as $skill) {
        $userSkills[] = $skill['skill_name'];
    }
}

// Build personalized recommendations using RaketGo MatchScore Algorithm
$recommendedJobs = [];
$recommendedWorkers = [];

if ($user_type == 'worker') {
    // Get candidate jobs for scoring (broader set for algorithm to rank)
    $candidateJobsSql = "SELECT j.*, u.full_name as employer_name, u.trust_score as employer_trust_score
                         FROM job_posts j
                         JOIN users u ON j.employer_id = u.user_id
                         WHERE j.job_status = 'active'
                         AND j.employer_id != ?
                         AND j.job_id NOT IN (SELECT job_id FROM job_applications WHERE worker_id = ?)
                         AND (j.location_region = ? OR j.location_province = ? OR j.location_city = ?
                              OR EXISTS (SELECT 1 FROM user_skills us
                                        WHERE us.user_id = ?
                                        AND (j.required_skills LIKE CONCAT('%', us.skill_name, '%')
                                             OR j.preferred_skills LIKE CONCAT('%', us.skill_name, '%'))))
                         ORDER BY j.created_at DESC LIMIT 50";

    $candidateJobs = fetchAll($conn, $candidateJobsSql,
        [$user_id, $user_id, $user['region'], $user['province'], $user['city'], $user_id],
        'iissssi'
    );

    // Calculate MatchScore for each candidate
    foreach ($candidateJobs as $job) {
        $matchResult = calculateMatchScore($conn, $user_id, $job, $user, $recommendedJobs);
        $job['match_score'] = $matchResult['total'];
        $job['match_tier'] = $matchResult['match_tier'];
        $job['match_breakdown'] = $matchResult['breakdown'];
        $recommendedJobs[] = $job;
    }

    // Sort by match score descending
    usort($recommendedJobs, function($a, $b) {
        return $b['match_score'] <=> $a['match_score'];
    });

    // Take top 15
    $recommendedJobs = array_slice($recommendedJobs, 0, 15);

} elseif ($user_type == 'employer') {
    // Get employer's active jobs for context
    $employerJobs = fetchAll($conn,
        "SELECT job_id, required_skills, preferred_skills, job_category, job_type, location_region, location_province, location_city
         FROM job_posts WHERE employer_id = ? AND job_status = 'active' ORDER BY created_at DESC LIMIT 5",
        [$user_id], 'i'
    );

    if (!empty($employerJobs)) {
        // Build search criteria from employer's job postings
        $allSkills = [];
        $allCategories = [];
        $allJobTypes = [];
        $employerRegions = [];

        foreach ($employerJobs as $job) {
            if (!empty($job['required_skills'])) {
                foreach (explode(',', $job['required_skills']) as $skill) {
                    $allSkills[] = trim($skill);
                }
            }
            if (!empty($job['preferred_skills'])) {
                foreach (explode(',', $job['preferred_skills']) as $skill) {
                    $allSkills[] = trim($skill);
                }
            }
            if (!empty($job['job_category'])) $allCategories[] = $job['job_category'];
            if (!empty($job['job_type'])) $allJobTypes[] = $job['job_type'];
            if (!empty($job['location_region'])) $employerRegions[] = $job['location_region'];
        }

        $allSkills = array_unique(array_filter($allSkills));
        $allCategories = array_unique(array_filter($allCategories));
        $allJobTypes = array_unique(array_filter($allJobTypes));
        $employerRegions = array_unique(array_filter($employerRegions));

        // Find candidate workers
        $workerConditions = ["u.user_type = 'worker'", "u.account_status = 'active'"];
        $workerParams = [];
        $workerTypes = '';

        if (!empty($allSkills)) {
            $skillPlaceholders = implode(',', array_fill(0, count($allSkills), '?'));
            $workerConditions[] = "u.user_id IN (SELECT DISTINCT us.user_id FROM user_skills us WHERE us.skill_name IN ($skillPlaceholders))";
            $workerParams = array_merge($workerParams, $allSkills);
            $workerTypes .= str_repeat('s', count($allSkills));
        }

        if (!empty($employerRegions)) {
            $regionPlaceholders = implode(',', array_fill(0, count($employerRegions), '?'));
            $workerConditions[] = "(u.region IN ($regionPlaceholders) OR u.province = ?)";
            $workerParams = array_merge($workerParams, $employerRegions, [$user['province'] ?? '']);
            $workerTypes .= str_repeat('s', count($employerRegions)) . 's';
        }

        $whereClause = implode(' AND ', $workerConditions);

        $candidateWorkersSql = "SELECT u.*, GROUP_CONCAT(DISTINCT us.skill_name) as skills,
                                (SELECT COUNT(*) FROM user_skills WHERE user_id = u.user_id AND is_verified = 1) as verified_count,
                                (SELECT COUNT(*) FROM job_applications WHERE worker_id = u.user_id AND application_status IN ('approved', 'completed')) as completed_jobs
                                FROM users u
                                LEFT JOIN user_skills us ON u.user_id = us.user_id
                                WHERE $whereClause
                                AND u.user_id NOT IN (SELECT DISTINCT worker_id FROM job_applications WHERE employer_id = ? AND application_status IN ('pending', 'approved'))
                                GROUP BY u.user_id
                                LIMIT 30";

        $workerParams[] = $user_id;
        $workerTypes .= 'i';

        $candidateWorkers = fetchAll($conn, $candidateWorkersSql, $workerParams, $workerTypes);

        // Calculate MatchScore for each candidate worker
        foreach ($candidateWorkers as $worker) {
            $matchScore = 0;
            $maxPossible = 0;

            // 1. Skill Match (0-40 points)
            $workerSkills = array_filter(explode(',', $worker['skills'] ?? ''));
            $bestSkillMatch = 0;
            foreach ($employerJobs as $job) {
                $jobSkills = array_filter(array_merge(
                    explode(',', $job['required_skills'] ?? ''),
                    explode(',', $job['preferred_skills'] ?? '')
                ));
                if (!empty($jobSkills)) {
                    $match = calculateSkillAffinityScore($workerSkills, implode(',', $jobSkills), '');
                    $bestSkillMatch = max($bestSkillMatch, $match);
                }
            }
            $matchScore += $bestSkillMatch * 1.14; // Scale to ~40 max
            $maxPossible += 40;

            // 2. Trust Score (0-20 points)
            $matchScore += min(20, $worker['trust_score'] * 4);
            $maxPossible += 20;

            // 3. Experience (0-15 points) - based on completed jobs
            $matchScore += min(15, ($worker['completed_jobs'] ?? 0) * 3);
            $maxPossible += 15;

            // 4. Location Proximity (0-15 points)
            $bestLocation = 0;
            foreach ($employerJobs as $job) {
                $prox = calculateLocationProximityScore(
                    $worker['region'], $worker['province'], $worker['city'],
                    $job['location_region'], $job['location_province'], $job['location_city']
                );
                $bestLocation = max($bestLocation, $prox);
            }
            $matchScore += $bestLocation * 1.5; // Scale to ~15 max
            $maxPossible += 15;

            // 5. Verification Boost (0-10 points)
            $matchScore += min(10, ($worker['verified_count'] ?? 0) * 2);
            $maxPossible += 10;

            // Normalize to 0-100
            $normalizedScore = $maxPossible > 0 ? round(($matchScore / $maxPossible) * 100) : 0;

            $worker['match_score'] = $normalizedScore;
            $worker['match_tier'] = getMatchTier($normalizedScore);
            $recommendedWorkers[] = $worker;
        }

        // Sort by match score
        usort($recommendedWorkers, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        // Take top 10
        $recommendedWorkers = array_slice($recommendedWorkers, 0, 10);
    }
}

// Get trending jobs
$trendingSql = "SELECT j.*, u.full_name as employer_name, COUNT(ui.interaction_id) as interaction_count
                FROM job_posts j
                JOIN users u ON j.employer_id = u.user_id
                LEFT JOIN user_interactions ui ON j.job_id = ui.job_id AND ui.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                WHERE j.job_status = 'active'
                GROUP BY j.job_id
                HAVING interaction_count > 0
                ORDER BY interaction_count DESC LIMIT 6";
$trendingJobs = fetchAll($conn, $trendingSql);
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <?php if ($user_type == 'worker'): ?>
            <!-- Recommended Jobs for Workers -->
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    RECOMMENDED JOBS FOR YOU
                </div>
                <div class="panel-body">
                    <?php if (empty($recommendedJobs)): ?>
                        <div class="text-center" style="padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                            <p class="text-muted">No recommendations yet. Add more skills to your profile!</p>
                            <a href="dashboard-worker.php" class="btn btn-primary btn-small mt-2">
                                <i class="fas fa-user-edit"></i> Update Profile
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recommendedJobs as $job):
                            $tierInfo = getMatchTierInfo($job['match_tier']);
                        ?>
                            <div class="compact-job-item" onclick="window.location.href='job-details.php?id=<?php echo $job['job_id']; ?>'" style="cursor: pointer;">
                                <div class="d-flex justify-between align-center">
                                    <h4 style="color: var(--primary-blue-dark);">
                                        <?php echo htmlspecialchars($job['job_title']); ?>
                                    </h4>
                                    <div class="d-flex align-center gap-2">
                                        <?php if ($job['match_score'] >= 70): ?>
                                            <span class="tag" style="background: var(--<?php echo $tierInfo['color']; ?>-light, #e8f4f8); color: var(--<?php echo $tierInfo['color']; ?>-dark, #1565c0); font-size: 0.65rem;">
                                                <i class="fas <?php echo $tierInfo['icon']; ?>"></i>
                                                <?php echo $tierInfo['label']; ?> (<?php echo $job['match_score']; ?>%)
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-xs text-muted"><?php echo timeAgo($job['created_at']); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap" style="margin-top: 4px;">
                                    <span class="text-small text-muted"><i class="fas fa-building"></i> <?php echo htmlspecialchars($job['employer_name']); ?></span>
                                    <span class="text-small text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location_city']); ?></span>
                                    <span class="text-small text-muted"><i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?></span>
                                    <?php if (!empty($job['remote_policy'])): ?>
                                        <span class="text-small text-muted"><i class="fas fa-laptop-house"></i> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $job['remote_policy']))); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($job['required_skills'])): ?>
                                    <div class="d-flex gap-1 flex-wrap" style="margin-top: 6px;">
                                        <?php
                                        $skills = explode(',', $job['required_skills']);
                                        foreach (array_slice($skills, 0, 3) as $skill):
                                            $skill = trim($skill);
                                            $hasSkill = in_array($skill, $userSkills);
                                        ?>
                                            <span class="tag <?php echo $hasSkill ? 'tag-pink' : ''; ?>">
                                                <?php if ($hasSkill): ?><i class="fas fa-check"></i> <?php endif; ?>
                                                <?php echo htmlspecialchars($skill); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php elseif ($user_type == 'employer'): ?>
            <!-- Recommended Workers for Employers -->
            <?php if (!empty($recommendedWorkers)): ?>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    RECOMMENDED WORKERS
                </div>
                <div class="panel-body">
                    <?php foreach ($recommendedWorkers as $worker): ?>
                        <div class="compact-job-item">
                            <div class="d-flex align-center gap-2">
                                <div class="message-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <?php echo mb_strtoupper(mb_substr($worker['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($worker['full_name']); ?></h4>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="text-small text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($worker['city'] . ', ' . $worker['province']); ?></span>
                                        <span class="text-small text-pink"><i class="fas fa-star"></i> <?php echo number_format($worker['trust_score'], 2); ?></span>
                                    </div>
                                </div>
                                <a href="messages.php?user=<?php echo $worker['user_id']; ?>" class="btn btn-secondary btn-small">
                                    <i class="fas fa-envelope"></i> Contact
                                </a>
                            </div>
                            <?php if (!empty($worker['skills'])): ?>
                                <div class="d-flex gap-1 flex-wrap" style="margin-top: 6px;">
                                    <?php foreach (array_slice(explode(',', $worker['skills']), 0, 4) as $skill): ?>
                                        <span class="tag tag-pink"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    RECOMMENDED WORKERS
                </div>
                <div class="panel-body text-center" style="padding: 2rem;">
                    <i class="fas fa-users" style="font-size: 2rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                    <p class="text-muted">No matching workers found yet.</p>
                    <p class="text-muted" style="font-size: 0.82rem; margin-top: 4px;">Post a job with required skills to get worker recommendations.</p>
                    <a href="post-job.php" class="btn btn-primary btn-small mt-1"><i class="fas fa-plus"></i> Post a Job</a>
                </div>
            </div>
            <?php endif; ?>

            <?php elseif ($user_type == 'admin'): ?>
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    ADMIN — FOR YOU
                </div>
                <div class="panel-body text-center" style="padding: 2rem;">
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--primary-blue-dark); display: block; margin-bottom: 0.5rem;"></i>
                    <p class="text-muted">This page is intended for workers and employers.</p>
                    <a href="dashboard-admin.php" class="btn btn-primary btn-small mt-1"><i class="fas fa-tachometer-alt"></i> Go to Admin Dashboard</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Trending Jobs -->
            <?php if (!empty($trendingJobs)): ?>
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    TRENDING THIS WEEK
                </div>
                <div class="panel-body">
                    <?php foreach ($trendingJobs as $job): ?>
                        <div class="compact-job-item" onclick="window.location.href='job-details.php?id=<?php echo $job['job_id']; ?>'" style="cursor: pointer;">
                            <div class="d-flex justify-between align-center">
                                <h4>
                                    <i class="fas fa-fire" style="color: #FF6B35; font-size: 0.8rem;"></i>
                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                </h4>
                                <span class="tag tag-yellow" style="font-size: 0.65rem;"><?php echo $job['interaction_count']; ?> views</span>
                            </div>
                            <div class="d-flex gap-2" style="margin-top: 4px;">
                                <span class="text-small text-muted"><i class="fas fa-building"></i> <?php echo htmlspecialchars($job['employer_name']); ?></span>
                                <span class="text-small text-muted"><i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?></span>
                                <?php if (!empty($job['remote_policy'])): ?>
                                    <span class="text-small text-muted"><i class="fas fa-laptop-house"></i> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $job['remote_policy']))); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    QUICK ACTIONS
                </div>
                <div class="panel-body">
                    <div class="grid grid-2">
                        <a href="index.php" class="quick-action" style="text-decoration: none;">
                            <i class="fas fa-search"></i>
                            <h3>Browse Jobs</h3>
                            <p>Explore all available opportunities</p>
                        </a>
                        <a href="skill-learn.php" class="quick-action" style="text-decoration: none;">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Learn Skills</h3>
                            <p>Enhance your profile</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Profile Summary -->
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    YOUR PROFILE
                </div>
                <div class="panel-body" style="text-align: center;">
                    <div class="message-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto 8px;">
                        <?php echo mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    </div>
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-small text-muted"><?php echo ucfirst($user_type); ?> &middot; <?php echo htmlspecialchars($user['city']); ?></p>
                    <?php if ($user_type == 'worker'): ?>
                        <div class="d-flex justify-center align-center gap-1 mt-1">
                            <span class="text-pink" style="font-size: 1.2rem; font-weight: 700;"><?php echo number_format($user['trust_score'], 2); ?></span>
                            <span class="text-small text-muted">Trust Score</span>
                        </div>
                    <?php endif; ?>
                    <a href="dashboard-<?php echo $user_type; ?>.php" class="btn btn-outline btn-small btn-block mt-2">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                </div>
            </div>

            <!-- Your Skills (Workers) -->
            <?php if ($user_type == 'worker' && !empty($userSkills)): ?>
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    YOUR SKILLS
                </div>
                <div class="panel-body">
                    <div class="tech-tags">
                        <?php foreach ($userSkills as $skill): ?>
                            <span class="tag tag-pink"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Platform Notice -->
            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    RECOMMENDATION INFO
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        Recommendations are based on your skills, location, activity history, and trust score. 
                        Keep your profile updated for better matches.
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