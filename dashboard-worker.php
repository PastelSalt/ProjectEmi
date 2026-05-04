<?php
/**
 * Worker Dashboard (Acts as Resume)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'My Dashboard';
require_once 'config/config.php';
require_once 'config/DatabaseHelper.php';
require_once 'config/ErrorHandler.php';
require_once 'config/AuthHelper.php';

AuthHelper::requireUserType('worker');
require_once 'includes/header.php';

$conn = getDBConnection();
$db = new DatabaseHelper($conn);
$handler = new ErrorHandler();
$user_id = AuthHelper::getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if (!$handler->validateCsrf($_POST['csrf_token'] ?? '')) {
        $handler->addError('Invalid request. Please refresh the page and try again.');
    } elseif ($action == 'update_profile') {
        $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
        $payment_details = sanitizeInput($_POST['payment_details'] ?? '');
        $social_links = sanitizeInput($_POST['social_links'] ?? '');

        $updateSql = "UPDATE users SET payment_method = ?, payment_details = ?, social_links = ? WHERE user_id = ?";
        if (executeQuery($conn, $updateSql, [$payment_method, $payment_details, $social_links, $user_id], 'sssi')) {
            $handler->addSuccess('Profile updated successfully!');
        } else {
            $handler->addError('Failed to update profile. Please try again.');
        }
    } elseif ($action == 'add_skill') {
        $skill_name = sanitizeInput($_POST['skill_name'] ?? '');
        $proficiency = sanitizeInput($_POST['proficiency'] ?? 'beginner');
        $allowedProficiency = ['beginner', 'intermediate', 'advanced', 'expert'];
        if (!in_array($proficiency, $allowedProficiency, true)) {
            $proficiency = 'beginner';
        }

        if (!empty($skill_name) && strlen($skill_name) <= 100) {
            if ($db->addUserSkill($user_id, $skill_name, $proficiency)) {
                $handler->addSuccess('Skill added successfully!');
            } else {
                $handler->addError('Failed to add skill. Please try again.');
            }
        } else {
            $handler->addError('Please provide a valid skill name (max 100 characters).');
        }
    } elseif ($action == 'remove_skill') {
        $skill_name = sanitizeInput($_POST['skill_name'] ?? '');
        if (!empty($skill_name)) {
            if ($db->removeUserSkill($user_id, $skill_name)) {
                $handler->addSuccess('Skill removed successfully!');
            } else {
                $handler->addError('Failed to remove skill. Please try again.');
            }
        } else {
            $handler->addError('Invalid skill name.');
        }
    } elseif ($action == 'delete_skill') {
        $skill_id = (int)($_POST['skill_id'] ?? 0);
        if (executeQuery($conn, "DELETE FROM user_skills WHERE skill_id = ? AND user_id = ?", [$skill_id, $user_id], 'ii')) {
            $success = 'Skill removed.';
        }
    } elseif ($action == 'remove_saved_job') {
        $job_id = (int)($_POST['job_id'] ?? 0);
        if (executeQuery($conn, "DELETE FROM user_interactions WHERE user_id = ? AND job_id = ? AND interaction_type = 'save'", [$user_id, $job_id], 'ii')) {
            $success = 'Saved job removed.';
        } else {
            $error = 'Failed to remove saved job.';
        }
    } elseif ($action == 'upload_profile_picture') {
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $fileSize = $file['size'];
            $fileTmpPath = $file['tmp_name'];
            $fileName = $file['name'];
            $fileType = mime_content_type($fileTmpPath);

            // Validate file type (images only)
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($fileType, $allowedMimeTypes, true)) {
                $error = 'Invalid image type. Please upload JPEG, PNG, or WebP.';
            } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB limit for profile pics
                $error = 'Image size exceeds 2MB limit.';
            } else {
                // Generate unique filename
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $uniqueFileName = uniqid('profile_', true) . '_' . $user_id . '.' . $fileExtension;
                $destination = PROFILE_PICS_DIR . $uniqueFileName;

                // Delete old profile picture if exists
                if (!empty($user['profile_picture'])) {
                    $oldFile = BASE_PATH . $user['profile_picture'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $profile_picture = 'uploads/profiles/' . $uniqueFileName;
                    if (executeQuery($conn, "UPDATE users SET profile_picture = ? WHERE user_id = ?", [$profile_picture, $user_id], 'si')) {
                        $success = 'Profile picture updated successfully!';
                    } else {
                        $error = 'Failed to save profile picture.';
                    }
                } else {
                    $error = 'Failed to upload profile picture. Please try again.';
                }
            }
        } else {
            $error = 'Please select an image to upload.';
        }
    }
}

$user = fetchOne($conn, "SELECT * FROM users WHERE user_id = ?", [$user_id], 'i');
$skills = fetchAll($conn, "SELECT * FROM user_skills WHERE user_id = ? ORDER BY is_verified DESC, created_at DESC", [$user_id], 'i');

$applicationsSql = "SELECT ja.*, j.job_title, j.pay_amount, j.pay_type, j.job_type, u.full_name as employer_name
                    FROM job_applications ja
                    JOIN job_posts j ON ja.job_id = j.job_id
                    JOIN users u ON ja.employer_id = u.user_id
                    WHERE ja.worker_id = ?
                    ORDER BY ja.applied_at DESC LIMIT 10";
$applications = fetchAll($conn, $applicationsSql, [$user_id], 'i');

 $savedJobsSql = "SELECT j.job_id, j.job_title, j.location_city, j.pay_amount, j.pay_type, j.job_type, j.job_status,
                                u.full_name as employer_name, s.saved_at
                      FROM (
                          SELECT job_id, MAX(created_at) as saved_at
                          FROM user_interactions
                          WHERE user_id = ? AND interaction_type = 'save'
                          GROUP BY job_id
                      ) s
                      JOIN job_posts j ON s.job_id = j.job_id
                      JOIN users u ON j.employer_id = u.user_id
                      ORDER BY s.saved_at DESC
                      LIMIT 8";
$savedJobs = fetchAll($conn, $savedJobsSql, [$user_id], 'i');

$statsResult = fetchOne($conn, "SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN application_status = 'approved' THEN 1 ELSE 0 END) as approved_jobs,
    SUM(CASE WHEN application_status = 'approved' AND worker_confirmed = 1 AND employer_confirmed = 1 THEN 1 ELSE 0 END) as completed_jobs,
    SUM(CASE WHEN application_status = 'pending' THEN 1 ELSE 0 END) as pending_applications
    FROM job_applications WHERE worker_id = ?", [$user_id], 'i');
$stats = $statsResult ?: ['total_applications' => 0, 'approved_jobs' => 0, 'completed_jobs' => 0, 'pending_applications' => 0];
?>

<div class="container">
    <?php echo $handler->renderSuccesses(); ?>
    <?php echo $handler->renderErrors(); ?>

    <!-- Stats Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-briefcase stat-icon stat-blue"></i>
            <div class="stat-value"><?php echo $stats['total_applications']; ?></div>
            <div class="stat-label">Applications</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo $stats['approved_jobs']; ?></div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-trophy stat-icon" style="color: #FFD700;"></i>
            <div class="stat-value"><?php echo $stats['completed_jobs']; ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-peso-sign stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo formatCurrency($user['current_balance']); ?></div>
            <div class="stat-label">Balance</div>
        </div>
    </div>

    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Profile Summary Panel -->
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    PROFILE SUMMARY
                </div>
                <div class="panel-body">
                    <div class="d-flex align-center gap-2">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div class="message-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <?php echo mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <div style="flex: 1;">
                            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p class="text-small text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['city'] . ', ' . $user['province']); ?>
                                &middot; <i class="fas fa-star"></i> Trust Score: <?php echo number_format($user['trust_score'], 2); ?>
                            </p>
                        </div>
                        <div>
                            <a href="worker-profile.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-user"></i> View Full Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills Section -->
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    MY SKILLS
                </div>
                <div class="panel-body">
                    <?php if (!empty($skills)): ?>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <?php foreach ($skills as $skill): ?>
                                <div class="tag <?php echo $skill['is_verified'] ? 'tag-pink' : ''; ?>" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;">
                                    <?php if ($skill['is_verified']): ?><i class="fas fa-check-circle"></i><?php endif; ?>
                                    <?php echo htmlspecialchars($skill['skill_name']); ?>
                                    <span class="text-xs">(<?php echo ucfirst($skill['proficiency_level']); ?>)</span>
                                    <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Remove?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="delete_skill">
                                        <input type="hidden" name="skill_id" value="<?php echo $skill['skill_id']; ?>">
                                        <button type="submit" style="background: none; border: none; cursor: pointer; color: inherit; padding: 0; font-size: 0.7rem;"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-2">No skills yet. Add skills to get better job matches!</p>
                    <?php endif; ?>
                    
                    <form method="POST" style="border-top: 1px solid var(--gray); padding-top: 12px;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="add_skill">
                        <div class="d-flex gap-1">
                            <input type="text" name="skill_name" class="form-control" placeholder="Skill name" required style="flex: 2;">
                            <select name="proficiency" class="form-control" style="flex: 1;">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-small"><i class="fas fa-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Saved Jobs -->
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    SAVED JOBS
                </div>
                <div class="panel-body">
                    <?php if (empty($savedJobs)): ?>
                        <div class="text-center" style="padding: 1.5rem;">
                            <i class="fas fa-bookmark" style="font-size: 1.8rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                            <p class="text-muted">No saved jobs yet.</p>
                            <a href="index.php" class="btn btn-primary btn-small mt-1"><i class="fas fa-search"></i> Find Jobs</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($savedJobs as $savedJob): ?>
                            <div class="compact-job-item">
                                <div class="d-flex justify-between align-center gap-2">
                                    <div style="flex: 1; min-width: 0;">
                                        <h4 style="margin-bottom: 2px;"><?php echo htmlspecialchars($savedJob['job_title']); ?></h4>
                                        <div class="text-small text-muted d-flex gap-2 flex-wrap">
                                            <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($savedJob['employer_name']); ?></span>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($savedJob['location_city']); ?></span>
                                            <span><i class="fas fa-peso-sign"></i> <?php echo formatCurrency($savedJob['pay_amount']); ?>/<?php echo $savedJob['pay_type']; ?></span>
                                            <?php if (!empty($savedJob['job_type'])): ?>
                                                <span class="text-small text-muted">&middot; <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $savedJob['job_type']))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1" style="flex-shrink: 0;">
                                        <a href="job-details.php?id=<?php echo $savedJob['job_id']; ?>" class="btn btn-primary btn-small"><i class="fas fa-eye"></i></a>
                                        <form method="POST" style="display: inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="remove_saved_job">
                                            <input type="hidden" name="job_id" value="<?php echo $savedJob['job_id']; ?>">
                                            <button type="submit" class="btn btn-outline btn-small" title="Remove saved job"><i class="fas fa-times"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Application History -->
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    RECENT APPLICATIONS
                </div>
                <div class="panel-body">
                    <?php if (empty($applications)): ?>
                        <div class="text-center" style="padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                            <p class="text-muted">No applications yet.</p>
                            <a href="index.php" class="btn btn-primary btn-small mt-1"><i class="fas fa-search"></i> Browse Jobs</a>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Job</th>
                                    <th>Employer</th>
                                    <th>Pay</th>
                                    <th>Status</th>
                                    <th>Rating</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app):
                                    $ratingStatus = '';
                                    $rateButton = '';
                                    $ratingWaitInfo = '';

                                    if ($app['application_status'] == 'approved' && $app['worker_confirmed'] == 1 && $app['employer_confirmed'] == 1) {
                                        $rating = fetchOne($conn, "SELECT rating_stars FROM job_ratings WHERE application_id = ? AND rating_type = 'worker_to_employer'", [$app['application_id']], 'i');
                                        if ($rating) {
                                            $ratingStatus = '<i class="fas fa-star" style="color: #FFD700;"></i> Rated';
                                        } else {
                                            // Check if rating is available based on job type timing
                                            $isRatingAvailable = true;

                                            if (!empty($app['rating_available_at'])) {
                                                $ratingAvailableTime = strtotime($app['rating_available_at']);
                                                if (time() < $ratingAvailableTime) {
                                                    $isRatingAvailable = false;
                                                    $hoursRemaining = ceil(($ratingAvailableTime - time()) / 3600);
                                                    $daysRemaining = ceil($hoursRemaining / 24);
                                                    $ratingWaitInfo = $daysRemaining > 1 ? "In {$daysRemaining} days" : ($hoursRemaining > 1 ? "In {$hoursRemaining}h" : "Soon");
                                                }
                                            }

                                            if ($isRatingAvailable) {
                                                $rateButton = '<a href="rate-employer.php?app_id=' . $app['application_id'] . '" class="btn btn-primary btn-small" onclick="event.stopPropagation();"><i class="fas fa-star"></i> Rate</a>';
                                            } else {
                                                $ratingStatus = '<span class="text-muted" title="Long-term jobs have a 3-day cooling-off period"><i class="fas fa-clock"></i> ' . $ratingWaitInfo . '</span>';
                                            }
                                        }
                                    }
                                ?>
                                    <tr onclick="window.location.href='job-details.php?id=<?php echo $app['job_id']; ?>'" style="cursor: pointer;">
                                        <td><strong><?php echo htmlspecialchars($app['job_title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($app['employer_name']); ?></td>
                                        <td>
                                            <?php echo formatCurrency($app['pay_amount']); ?>/<?php echo $app['pay_type']; ?>
                                            <?php if (!empty($app['job_type'])): ?>
                                                <div class="text-xs text-muted"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $app['job_type']))); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="tag tag-<?php echo $app['application_status'] == 'approved' ? 'green' : ($app['application_status'] == 'rejected' ? 'red' : 'blue'); ?>" style="font-size: 0.7rem;">
                                                <?php echo ucfirst($app['application_status']); ?>
                                            </span>
                                            <?php if ($app['application_status'] == 'approved'): ?>
                                                <?php if ($app['worker_confirmed'] == 0 && $app['employer_confirmed'] == 0): ?>
                                                    <div class="text-xs text-muted" style="margin-top: 2px;">Awaiting confirmation</div>
                                                <?php elseif ($app['worker_confirmed'] == 1 && $app['employer_confirmed'] == 0): ?>
                                                    <div class="text-xs text-muted" style="margin-top: 2px;">Waiting for employer</div>
                                                <?php elseif ($app['worker_confirmed'] == 0 && $app['employer_confirmed'] == 1): ?>
                                                    <div class="text-xs text-muted" style="margin-top: 2px;">Confirm your work</div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($ratingStatus)): ?>
                                                <?php echo $ratingStatus; ?>
                                            <?php elseif (!empty($rateButton)): ?>
                                                <?php echo $rateButton; ?>
                                            <?php else: ?>
                                                <span class="text-xs text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-xs text-muted"><?php echo timeAgo($app['applied_at']); ?></td>
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
            <!-- Payment Settings -->
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    PAYMENT SETTINGS
                </div>
                <div class="panel-body">
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label class="text-small">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">Select method</option>
                                <option value="gcash" <?php echo $user['payment_method'] == 'gcash' ? 'selected' : ''; ?>>GCash</option>
                                <option value="paymaya" <?php echo $user['payment_method'] == 'paymaya' ? 'selected' : ''; ?>>PayMaya</option>
                                <option value="bank" <?php echo $user['payment_method'] == 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="text-small">Account Number</label>
                            <input type="text" name="payment_details" class="form-control" placeholder="Account #" value="<?php echo htmlspecialchars($user['payment_details'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="text-small">Social Links</label>
                            <textarea name="social_links" class="form-control" rows="2" placeholder="Facebook, LinkedIn..."><?php echo htmlspecialchars($user['social_links'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-small btn-block"><i class="fas fa-save"></i> Update</button>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="widget">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    QUICK LINKS
                </div>
                <div class="panel-body-compact">
                    <a href="for-you.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-star"></i> Recommendations</a>
                    <a href="index.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-search"></i> Browse Jobs</a>
                    <a href="worker-portfolio.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-briefcase"></i> My Portfolio</a>
                    <a href="messages.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-envelope"></i> Messages</a>
                    <a href="skill-learn.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-graduation-cap"></i> Learn Skills</a>
                    <a href="notification-settings.php" class="btn btn-white btn-small btn-block"><i class="fas fa-bell"></i> Notification Settings</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
