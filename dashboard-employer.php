<?php
/**
 * Employer Dashboard
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Employer Dashboard';
require_once 'config/config.php';
requireUserType('employer');
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();

$success = '';
$error = '';

if (!empty($_SESSION['flash_success'])) {
    $success = sanitizeInput($_SESSION['flash_success']);
    unset($_SESSION['flash_success']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $job_id = (int)($_POST['job_id'] ?? 0);

        if ($job_id > 0 && $action === 'pause_job') {
            $pauseStmt = executeQuery(
                $conn,
                "UPDATE job_posts
                 SET job_status = 'cancelled'
                 WHERE job_id = ? AND employer_id = ? AND job_status = 'active'",
                [$job_id, $user_id],
                'ii'
            );

            if ($pauseStmt && $pauseStmt->affected_rows > 0) {
                $success = 'Job posting paused successfully.';
            } else {
                $error = 'Only active jobs can be paused.';
            }
        } elseif ($job_id > 0 && $action === 'reopen_job') {
            $reopenStmt = executeQuery(
                $conn,
                "UPDATE job_posts
                 SET job_status = 'active'
                 WHERE job_id = ?
                   AND employer_id = ?
                   AND job_status = 'cancelled'
                   AND slots_filled < slots_available",
                [$job_id, $user_id],
                'ii'
            );

            if ($reopenStmt && $reopenStmt->affected_rows > 0) {
                $success = 'Job posting reopened and now accepting applications.';
            } else {
                $error = 'This job cannot be reopened (already filled or not in paused state).';
            }
        } elseif ($action === 'upload_profile_picture') {
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
                } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB limit
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

                    if (saveUploadedImage($fileTmpPath, $destination, 800, 800)) {
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
        } elseif ($action === 'update_profile_info') {
            // Handle profile info update (bio and employer subtype)
            $employer_subtype = sanitizeInput($_POST['employer_subtype'] ?? '');
            $bio = sanitizeMultilineInput($_POST['bio'] ?? '');

            // Validate employer subtype
            if (!in_array($employer_subtype, ['company', 'individual'], true)) {
                $error = 'Please select a valid employer type.';
            } elseif (strlen($bio) > 500) {
                $error = 'Bio cannot exceed 500 characters.';
            } else {
                if (executeQuery($conn,
                    "UPDATE users SET employer_subtype = ?, bio = ? WHERE user_id = ?",
                    [$employer_subtype, $bio, $user_id],
                    'ssi'
                )) {
                    $success = 'Profile information updated successfully!';
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        } elseif ($action === 'update_company_profile') {
            // Handle company profile update
            $company_name = sanitizeInput($_POST['company_name'] ?? '');
            $company_size = sanitizeInput($_POST['company_size'] ?? '');
            $industry = sanitizeInput($_POST['industry'] ?? '');
            $company_website = sanitizeExternalUrl($_POST['company_website'] ?? '');
            $year_founded = (int)($_POST['year_founded'] ?? 0);

            // Validation
            $valid_sizes = ['1-10', '11-50', '51-200', '201-500', '500+'];
            if (!empty($company_size) && !in_array($company_size, $valid_sizes, true)) {
                $error = 'Please select a valid company size.';
            } elseif (strlen($company_name) > 255) {
                $error = 'Company name cannot exceed 255 characters.';
            } elseif (strlen($industry) > 100) {
                $error = 'Industry cannot exceed 100 characters.';
            } elseif ($year_founded > 0 && ($year_founded < 1800 || $year_founded > date('Y'))) {
                $error = 'Please enter a valid year founded.';
            } else {
                if (executeQuery($conn,
                    "UPDATE users SET company_name = ?, company_size = ?, industry = ?, company_website = ?, year_founded = ? WHERE user_id = ?",
                    [$company_name ?: null, $company_size ?: null, $industry ?: null, $company_website ?: null, $year_founded > 0 ? $year_founded : null, $user_id],
                    'ssssii'
                )) {
                    $success = 'Company profile updated successfully!';
                } else {
                    $error = 'Failed to update company profile. Please try again.';
                }
            }
        } elseif ($action === 'upload_company_logo') {
            // Handle company logo upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['company_logo'];
                $fileSize = $file['size'];
                $fileTmpPath = $file['tmp_name'];
                $fileName = $file['name'];
                $fileType = mime_content_type($fileTmpPath);

                // Validate file type (images only)
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($fileType, $allowedMimeTypes, true)) {
                    $error = 'Invalid image type. Please upload JPEG, PNG, or WebP.';
                } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB limit
                    $error = 'Image size exceeds 2MB limit.';
                } else {
                    // Generate unique filename
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $uniqueFileName = uniqid('company_logo_', true) . '_' . $user_id . '.' . $fileExtension;
                    $destination = COMPANY_LOGOS_DIR . $uniqueFileName;

                    // Delete old logo if exists
                    if (!empty($user['company_logo'])) {
                        $oldFile = BASE_PATH . $user['company_logo'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    if (saveUploadedImage($fileTmpPath, $destination, 800, 800)) {
                        $company_logo = 'uploads/company_logos/' . $uniqueFileName;
                        if (executeQuery($conn, "UPDATE users SET company_logo = ? WHERE user_id = ?", [$company_logo, $user_id], 'si')) {
                            $success = 'Company logo updated successfully!';
                        } else {
                            $error = 'Failed to save company logo.';
                        }
                    } else {
                        $error = 'Failed to upload company logo. Please try again.';
                    }
                }
            } else {
                $error = 'Please select an image to upload.';
            }
        }
    }
}

$user = fetchOne($conn, "SELECT * FROM users WHERE user_id = ?", [$user_id], 'i');

$jobsSql = "SELECT *, 
            (SELECT COUNT(*) FROM job_applications WHERE job_id = job_posts.job_id) as application_count,
            (SELECT COUNT(*) FROM job_applications WHERE job_id = job_posts.job_id AND application_status = 'pending') as pending_count
            FROM job_posts WHERE employer_id = ? ORDER BY created_at DESC";
$jobs = fetchAll($conn, $jobsSql, [$user_id], 'i');

$pendingAppsSql = "SELECT ja.*, j.job_title, u.full_name as worker_name, u.trust_score, u.mobile_number
                   FROM job_applications ja
                   JOIN job_posts j ON ja.job_id = j.job_id
                   JOIN users u ON ja.worker_id = u.user_id
                   WHERE ja.employer_id = ? AND ja.application_status = 'pending'
                   ORDER BY ja.applied_at DESC LIMIT 5";
$pendingApps = fetchAll($conn, $pendingAppsSql, [$user_id], 'i');

$statsResult = fetchOne($conn, "SELECT 
    (SELECT COUNT(*) FROM job_posts WHERE employer_id = ?) as total_jobs,
    (SELECT COUNT(*) FROM job_posts WHERE employer_id = ? AND job_status = 'active') as active_jobs,
    (SELECT COUNT(*) FROM job_applications WHERE employer_id = ?) as total_applications,
    (SELECT COUNT(*) FROM job_applications WHERE employer_id = ? AND application_status = 'pending') as pending_applications",
    [$user_id, $user_id, $user_id, $user_id], 'iiii');
$stats = $statsResult ?: ['total_jobs' => 0, 'active_jobs' => 0, 'total_applications' => 0, 'pending_applications' => 0];
?>

<div class="container">
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-briefcase stat-icon stat-blue"></i>
            <div class="stat-value"><?php echo $stats['total_jobs']; ?></div>
            <div class="stat-label">Total Jobs</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo $stats['active_jobs']; ?></div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users stat-icon stat-pink"></i>
            <div class="stat-value"><?php echo $stats['total_applications']; ?></div>
            <div class="stat-label">Applications</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock stat-icon" style="color: #FFD700;"></i>
            <div class="stat-value"><?php echo $stats['pending_applications']; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Profile Summary Panel -->
            <div class="panel">
                <div class="section-header section-header-pink">
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
                                <?php if (($user['employer_subtype'] ?? '') === 'company' && !empty($user['company_name'])): ?>
                                    &middot; <i class="fas fa-building"></i> <?php echo htmlspecialchars($user['company_name']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <a href="employer-profile.php?id=<?php echo $user_id; ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-user"></i> View Full Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <?php if (!empty($pendingApps)): ?>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    PENDING APPLICATIONS
                    <span class="view-all"><?php echo $stats['pending_applications']; ?> pending</span>
                </div>
                <div class="panel-body">
                    <?php foreach ($pendingApps as $app): ?>
                        <div class="compact-job-item">
                            <div class="d-flex align-center gap-2">
                                <div class="message-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <?php echo mb_strtoupper(mb_substr($app['worker_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($app['worker_name']); ?></h4>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="text-small text-muted">For: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong></span>
                                        <span class="text-small text-pink"><i class="fas fa-star"></i> <?php echo number_format($app['trust_score'], 2); ?></span>
                                    </div>
                                    <?php if (!empty($app['resume_file'])): ?>
                                        <div style="margin-top: 4px;">
                                            <a href="<?php echo htmlspecialchars($app['resume_file']); ?>" target="_blank" class="btn btn-outline btn-small" style="font-size: 0.7rem;">
                                                <i class="fas fa-file-pdf"></i> View Resume
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="job-details.php?id=<?php echo $app['job_id']; ?>" class="btn btn-primary btn-small"><i class="fas fa-eye"></i> Review</a>
                                    <a href="messages.php?user=<?php echo $app['worker_id']; ?>" class="btn btn-secondary btn-small"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- In Progress Jobs (Awaiting Confirmation) -->
            <?php
            $inProgressAppsSql = "SELECT ja.*, j.job_title, u.full_name as worker_name, u.trust_score
                                 FROM job_applications ja
                                 JOIN job_posts j ON ja.job_id = j.job_id
                                 JOIN users u ON ja.worker_id = u.user_id
                                 WHERE ja.employer_id = ? AND ja.application_status = 'approved'
                                 ORDER BY ja.applied_at DESC LIMIT 10";
            $inProgressApps = fetchAll($conn, $inProgressAppsSql, [$user_id], 'i');
            ?>
            <?php if (!empty($inProgressApps)): ?>
            <div class="panel">
                <div class="section-header section-header-blue">
                    <span class="header-square"></span>
                    IN PROGRESS
                    <span class="view-all"><?php echo count($inProgressApps); ?> active</span>
                </div>
                <div class="panel-body">
                    <?php foreach ($inProgressApps as $app): ?>
                        <div class="compact-job-item">
                            <div class="d-flex align-center gap-2">
                                <div class="message-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <?php echo mb_strtoupper(mb_substr($app['worker_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($app['worker_name']); ?></h4>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="text-small text-muted">For: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong></span>
                                        <span class="text-small text-pink"><i class="fas fa-star"></i> <?php echo number_format($app['trust_score'], 2); ?></span>
                                    </div>
                                    <?php if ($app['worker_confirmed'] == 0 && $app['employer_confirmed'] == 0): ?>
                                        <div class="text-xs text-muted" style="margin-top: 2px;">Awaiting confirmation</div>
                                    <?php elseif ($app['worker_confirmed'] == 1 && $app['employer_confirmed'] == 0): ?>
                                        <div class="text-xs text-muted" style="margin-top: 2px;">Worker confirmed - Confirm your work</div>
                                    <?php elseif ($app['worker_confirmed'] == 0 && $app['employer_confirmed'] == 1): ?>
                                        <div class="text-xs text-muted" style="margin-top: 2px;">Waiting for worker</div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="job-details.php?id=<?php echo $app['job_id']; ?>" class="btn btn-primary btn-small"><i class="fas fa-eye"></i> Review</a>
                                    <a href="messages.php?user=<?php echo $app['worker_id']; ?>" class="btn btn-secondary btn-small"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Completed Jobs Ready for Rating -->
            <?php
            $completedAppsSql = "SELECT ja.*, j.job_title, u.full_name as worker_name, u.trust_score
                                 FROM job_applications ja
                                 JOIN job_posts j ON ja.job_id = j.job_id
                                 JOIN users u ON ja.worker_id = u.user_id
                                 WHERE ja.employer_id = ? AND ja.application_status = 'approved' 
                                 AND ja.worker_confirmed = 1 AND ja.employer_confirmed = 1
                                 ORDER BY ja.updated_at DESC LIMIT 10";
            $completedApps = fetchAll($conn, $completedAppsSql, [$user_id], 'i');
            ?>
            <?php if (!empty($completedApps)): ?>
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    COMPLETED JOBS
                    <span class="view-all"><?php echo count($completedApps); ?> completed</span>
                </div>
                <div class="panel-body">
                    <?php foreach ($completedApps as $app): 
                        $rating = fetchOne($conn, "SELECT rating_stars FROM job_ratings WHERE application_id = ? AND rating_type = 'employer_to_worker'", [$app['application_id']], 'i');
                    ?>
                        <div class="compact-job-item">
                            <div class="d-flex align-center gap-2">
                                <div class="message-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <?php echo mb_strtoupper(mb_substr($app['worker_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($app['worker_name']); ?></h4>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="text-small text-muted">For: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong></span>
                                        <span class="text-small text-pink"><i class="fas fa-star"></i> <?php echo number_format($app['trust_score'], 2); ?></span>
                                        <?php if ($rating): ?>
                                            <span class="text-small" style="color: #FFD700;"><i class="fas fa-star"></i> You rated: <?php echo $rating['rating_stars']; ?>/5</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <?php if ($rating): ?>
                                        <span class="text-small text-muted">Rated</span>
                                    <?php else: ?>
                                        <a href="rate-worker.php?app_id=<?php echo $app['application_id']; ?>" class="btn btn-primary btn-small"><i class="fas fa-star"></i> Rate Worker</a>
                                    <?php endif; ?>
                                    <a href="messages.php?user=<?php echo $app['worker_id']; ?>" class="btn btn-secondary btn-small"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- My Job Posts -->
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    MY JOB POSTS
                    <a href="post-job.php" class="view-all"><i class="fas fa-plus"></i> Post New Job</a>
                </div>
                <div class="panel-body">
                    <?php if (empty($jobs)): ?>
                        <div class="text-center" style="padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                            <p class="text-muted">No jobs posted yet.</p>
                            <a href="post-job.php" class="btn btn-primary btn-small mt-1"><i class="fas fa-plus"></i> Post First Job</a>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                            <tr>
                                    <th>Job Title</th>
                                    <th>Location</th>
                                    <th>Arrangement</th>
                                    <th>Pay</th>
                                    <th>Apps</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($job['job_title']); ?></strong>
                                            <?php if ($job['pending_count'] > 0): ?>
                                                <span class="tag tag-red" style="font-size: 0.6rem; margin-left: 4px;"><?php echo $job['pending_count']; ?> new</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-small"><?php echo htmlspecialchars($job['location_city']); ?></td>
                                        <td class="text-small">
                                            <?php
                                            $rp = $job['remote_policy'] ?? 'on_site';
                                            $rpLabel = ucwords(str_replace('_', ' ', $rp));
                                            $rpClass = $rp === 'fully_remote' ? 'tag-blue' : ($rp === 'hybrid' ? 'tag-pink' : 'tag-outline');
                                            ?>
                                            <span class="tag <?php echo $rpClass; ?>" style="font-size: 0.65rem;"><?php echo htmlspecialchars($rpLabel); ?></span>
                                        </td>
                                        <td class="text-small"><?php echo formatCurrency($job['pay_amount']); ?>/<?php echo $job['pay_type']; ?></td>
                                        <td><?php echo $job['application_count']; ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'gray';
                                            if ($job['job_status'] == 'active') {
                                                $statusClass = 'green';
                                            } elseif ($job['job_status'] == 'cancelled') {
                                                $statusClass = 'red';
                                            } elseif ($job['job_status'] == 'in_progress') {
                                                $statusClass = 'blue';
                                            }
                                            ?>
                                            <span class="tag tag-<?php echo $statusClass; ?>" style="font-size: 0.65rem;">
                                                <?php echo ucfirst(str_replace('_', ' ', $job['job_status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary btn-small" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <?php if ($job['job_status'] == 'active'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="pause_job">
                                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                                        <button type="submit" class="btn btn-outline btn-small" title="Pause applications">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif ($job['job_status'] == 'cancelled' && $job['slots_filled'] < $job['slots_available']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="reopen_job">
                                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                                        <button type="submit" class="btn btn-secondary btn-small" title="Reopen applications">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
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
            <!-- Employer Profile -->
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    EMPLOYER PROFILE
                </div>
                <div class="panel-body" style="text-align: center;">
                    <div class="message-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto 8px; background: var(--pastel-blue);">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-small text-muted">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['city'] . ', ' . $user['province']); ?>
                    </p>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--gray);">
                        <div class="text-xs text-muted">Available Balance</div>
                        <div style="font-size: 1.3rem; font-weight: 700;"><?php echo formatCurrency($user['current_balance']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Post New Job -->
            <div class="widget">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    POST A JOB
                </div>
                <div class="panel-body" style="text-align: center;">
                    <p class="text-small text-muted mb-2">Find the perfect worker</p>
                    <a href="post-job.php" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Create Job Post</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    QUICK LINKS
                </div>
                <div class="panel-body-compact">
                    <a href="for-you.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-star"></i> Recommendations</a>
                    <a href="messages.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-envelope"></i> Messages</a>
                    <a href="notifications.php" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="notification-settings.php" class="btn btn-white btn-small btn-block"><i class="fas fa-cog"></i> Notification Settings</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bio character counter
    const bioTextarea = document.getElementById('bio');
    const charCount = document.getElementById('bio-char-count');

    if (bioTextarea && charCount) {
        bioTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
