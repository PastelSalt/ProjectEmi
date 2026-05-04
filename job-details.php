<?php
/**
 * Job Details & Application Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Job Details';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id == 0) {
    redirect('index.php');
}

$success = '';
$error = '';

// Handle job application submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && getCurrentUserType() == 'worker') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $user_id = getCurrentUserId();

        if ($action == 'save_job') {
            $existingSave = fetchOne(
                $conn,
                "SELECT interaction_id FROM user_interactions WHERE user_id = ? AND job_id = ? AND interaction_type = 'save' LIMIT 1",
                [$user_id, $job_id],
                'ii'
            );
            if ($existingSave) {
                $success = 'This job is already in your saved list.';
            } elseif (executeQuery($conn, "INSERT INTO user_interactions (user_id, interaction_type, job_id) VALUES (?, 'save', ?)", [$user_id, $job_id], 'ii')) {
                $success = 'Job saved to your list.';
            } else {
                $error = 'Failed to save job. Please try again.';
            }
        } elseif ($action == 'unsave_job') {
            if (executeQuery($conn, "DELETE FROM user_interactions WHERE user_id = ? AND job_id = ? AND interaction_type = 'save'", [$user_id, $job_id], 'ii')) {
                $success = 'Job removed from saved list.';
            } else {
                $error = 'Failed to update saved jobs.';
            }
        } elseif ($action == 'withdraw') {
            $withdrawStmt = executeQuery(
                $conn,
                "UPDATE job_applications SET application_status = 'withdrawn', reviewed_at = NOW() WHERE job_id = ? AND worker_id = ? AND application_status = 'pending'",
                [$job_id, $user_id],
                'ii'
            );

            if ($withdrawStmt && $withdrawStmt->affected_rows > 0) {
                $owner = fetchOne($conn, "SELECT employer_id FROM job_posts WHERE job_id = ?", [$job_id], 'i');
                if ($owner) {
                    executeQuery(
                        $conn,
                        "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                         VALUES (?, 'application_status', 'Application Withdrawn', 'A worker has withdrawn their pending application.', ?, 'job', ?)",
                        [$owner['employer_id'], $job_id, "job-details.php?id={$job_id}"],
                        'iis'
                    );
                }
                $success = 'Application withdrawn successfully.';
            } else {
                $error = 'Only pending applications can be withdrawn.';
            }
        } elseif ($action == 'confirm_work_complete') {
            // Get application data including job type for rating availability calculation
            $appData = fetchOne(
                $conn,
                "SELECT ja.employer_confirmed, j.job_type
                 FROM job_applications ja
                 JOIN job_posts j ON ja.job_id = j.job_id
                 WHERE ja.job_id = ? AND ja.worker_id = ? AND ja.application_status = 'approved'",
                [$job_id, $user_id],
                'ii'
            );

            if (!$appData) {
                $error = 'Application not found or not approved.';
            } else {
                beginTransaction($conn);
                try {
                    $confirmStmt = executeQuery(
                        $conn,
                        "UPDATE job_applications
                         SET worker_confirmed = 1,
                             both_confirmed_at = CASE WHEN employer_confirmed = 1 THEN NOW() ELSE both_confirmed_at END
                         WHERE job_id = ? AND worker_id = ? AND application_status = 'approved' AND worker_confirmed = 0",
                        [$job_id, $user_id],
                        'ii'
                    );

                    if ($confirmStmt && $confirmStmt->affected_rows > 0) {
                        // If employer already confirmed, set rating availability
                        if ($appData['employer_confirmed'] == 1) {
                            $jobType = $appData['job_type'];
                            $immediateRatingTypes = ['one_time'];
                            $delayedRatingTypes = ['full_time', 'part_time', 'contractual', 'internship'];

                            if (in_array($jobType, $immediateRatingTypes, true)) {
                                $ratingAvailableAt = null; // Immediate
                            } elseif (in_array($jobType, $delayedRatingTypes, true)) {
                                $ratingAvailableAt = date('Y-m-d H:i:s', strtotime('+3 days'));
                            } else {
                                $ratingAvailableAt = null;
                            }

                            executeQuery(
                                $conn,
                                "UPDATE job_applications SET rating_available_at = ? WHERE job_id = ? AND worker_id = ?",
                                [$ratingAvailableAt, $job_id, $user_id],
                                'sii'
                            );
                        }

                        commitTransaction($conn);

                        $owner = fetchOne($conn, "SELECT employer_id FROM job_posts WHERE job_id = ?", [$job_id], 'i');
                        if ($owner) {
                            $msg = $appData['employer_confirmed'] == 1
                                ? 'Both parties have confirmed work completion. Rating is now available.'
                                : 'The worker has confirmed the work is complete.';
                            executeQuery(
                                $conn,
                                "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                                 VALUES (?, 'work_confirmation', 'Work Confirmed by Worker', ?, ?, 'job', ?)",
                                [$owner['employer_id'], $msg, $job_id, "job-details.php?id={$job_id}"],
                                'isis'
                            );
                        }
                        $success = 'Work completion confirmed.' . ($appData['employer_confirmed'] == 1 ? ' Rating is now available.' : ' Waiting for employer confirmation.');
                    } else {
                        rollbackTransaction($conn);
                        $error = 'Failed to confirm work. Only approved applications can be confirmed.';
                    }
                } catch (Exception $e) {
                    rollbackTransaction($conn);
                    error_log('Worker confirmation failed: ' . $e->getMessage());
                    $error = 'Failed to confirm work. Please try again.';
                }
            }
        } elseif ($action == 'apply') {
            $cover_letter = sanitizeMultilineInput($_POST['cover_letter'] ?? '');
            if (strlen($cover_letter) > 3000) {
                $cover_letter = substr($cover_letter, 0, 3000);
            }

            // Handle resume file upload
            $resume_file = null;
            if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['resume_file'];
                $fileSize = $file['size'];
                $fileTmpPath = $file['tmp_name'];
                $fileName = $file['name'];
                $fileType = mime_content_type($fileTmpPath);

                // Validate file type (PDF only)
                $allowedMimeTypes = ['application/pdf'];
                if (!in_array($fileType, $allowedMimeTypes, true)) {
                    $error = 'Invalid file type. Please upload a PDF file.';
                } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                    $error = 'File size exceeds 5MB limit.';
                } else {
                    // Generate unique filename
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $uniqueFileName = uniqid('resume_', true) . '_' . $user_id . '_' . $job_id . '.' . $fileExtension;
                    $destination = RESUMES_DIR . $uniqueFileName;

                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $resume_file = 'uploads/resumes/' . $uniqueFileName;
                    } else {
                        $error = 'Failed to upload resume file. Please try again.';
                    }
                }
            }

            if (empty($error)) {
                // Verify job is still active AND has open slots
                $jobData = fetchOne($conn, "SELECT employer_id, slots_available, slots_filled FROM job_posts WHERE job_id = ? AND job_status = 'active' AND slots_filled < slots_available", [$job_id], 'i');

                if (!$jobData) {
                    $error = 'This job is no longer accepting applications (all slots have been filled).';
                } else {
                    // Begin transaction to prevent duplicate applications from double-clicks
                    beginTransaction($conn);
                    try {
                        $checkSql = "SELECT application_id, application_status FROM job_applications WHERE job_id = ? AND worker_id = ?";
                        $existing = fetchOne($conn, $checkSql, [$job_id, $user_id], 'ii');

                        if ($existing && $existing['application_status'] !== 'withdrawn') {
                            rollbackTransaction($conn);
                            $error = 'You have already applied for this job.';
                        } elseif ($existing && $existing['application_status'] === 'withdrawn') {
                                $reapplySql = "UPDATE job_applications
                                               SET application_status = 'pending', employer_id = ?, cover_letter = ?, resume_file = ?, applied_at = NOW(), reviewed_at = NULL,
                                                   worker_confirmed = 0, employer_confirmed = 0
                                               WHERE application_id = ? AND worker_id = ? AND application_status = 'withdrawn'";
                                $reapplyStmt = executeQuery($conn, $reapplySql, [$jobData['employer_id'], $cover_letter, $resume_file, $existing['application_id'], $user_id], 'issii');
                                if ($reapplyStmt && $reapplyStmt->affected_rows > 0) {
                                    $notifSql = "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                                                VALUES (?, 'new_application', 'New Job Application', 'A worker has applied for your job', ?, 'job', ?)";
                                    executeQuery($conn, $notifSql, [$jobData['employer_id'], $job_id, "job-details.php?id={$job_id}"], 'iis');
                                    commitTransaction($conn);
                                    $success = 'Application resubmitted successfully!';
                                } else {
                                    rollbackTransaction($conn);
                                    $error = 'Failed to resubmit application. Please try again.';
                                }
                            } else {
                                $insertSql = "INSERT INTO job_applications (job_id, worker_id, employer_id, cover_letter, resume_file) VALUES (?, ?, ?, ?, ?)";
                                if (executeQuery($conn, $insertSql, [$job_id, $user_id, $jobData['employer_id'], $cover_letter, $resume_file], 'iiiss')) {
                                    $notifSql = "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                                                VALUES (?, 'new_application', 'New Job Application', 'A worker has applied for your job', ?, 'job', ?)";
                                    executeQuery($conn, $notifSql, [$jobData['employer_id'], $job_id, "job-details.php?id={$job_id}"], 'iis');
                                    commitTransaction($conn);
                                    $success = 'Application submitted successfully! The employer will review your application.';
                                } else {
                                    rollbackTransaction($conn);
                                    $error = 'Failed to submit application. Please try again.';
                                }
                            }
                        } catch (Exception $e) {
                            rollbackTransaction($conn);
                            error_log('Application submission transaction failed: ' . $e->getMessage());
                            $error = 'Failed to submit application. Please try again.';
                        }
                    }
                }
            }
        }
    }

// Handle application approval/rejection (for employers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && getCurrentUserType() == 'employer') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $application_id = (int)($_POST['application_id'] ?? 0);

        if ($action == 'approve' || $action == 'reject') {
            $appData = fetchOne(
                $conn,
                "SELECT worker_id, job_id FROM job_applications WHERE application_id = ? AND employer_id = ? AND job_id = ?",
                [$application_id, getCurrentUserId(), $job_id],
                'iii'
            );

            if (!$appData) {
                $error = 'Application not found.';
            } elseif ($action == 'approve') {
                // Begin transaction for race condition protection
                beginTransaction($conn);
                try {
                    // Approve only once and only while there are remaining slots.
                    $approveSql = "UPDATE job_applications ja
                                   JOIN job_posts jp ON ja.job_id = jp.job_id
                                   SET ja.application_status = 'approved',
                                       ja.reviewed_at = NOW(),
                                       jp.slots_filled = jp.slots_filled + 1
                                   WHERE ja.application_id = ?
                                     AND ja.employer_id = ?
                                     AND ja.job_id = ?
                                     AND ja.application_status = 'pending'
                                     AND jp.slots_filled < jp.slots_available";
                    $approveStmt = executeQuery($conn, $approveSql, [$application_id, getCurrentUserId(), $job_id], 'iii');

                    if ($approveStmt && $approveStmt->affected_rows > 0) {
                        executeQuery($conn, "UPDATE job_posts SET job_status = 'in_progress' WHERE job_id = ? AND slots_filled >= slots_available", [$appData['job_id']], 'i');
                        $notifSql = "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                                    VALUES (?, 'application_status', 'Application Approved!', 'Your job application has been approved! The employer will contact you.', ?, 'job', ?)";
                        executeQuery($conn, $notifSql, [$appData['worker_id'], $appData['job_id'], "job-details.php?id={$appData['job_id']}"], 'iis');
                        commitTransaction($conn);
                        $success = 'Application approved successfully!';
                    } else {
                        rollbackTransaction($conn);
                        $error = 'This application can no longer be approved (already reviewed or no slots left).';
                    }
                } catch (Exception $e) {
                    rollbackTransaction($conn);
                    error_log('Approval transaction failed: ' . $e->getMessage());
                    $error = 'Failed to approve application. Please try again.';
                }
            } else {
                $rejectSql = "UPDATE job_applications
                              SET application_status = 'rejected', reviewed_at = NOW()
                              WHERE application_id = ? AND employer_id = ? AND job_id = ? AND application_status = 'pending'";
                $rejectStmt = executeQuery($conn, $rejectSql, [$application_id, getCurrentUserId(), $job_id], 'iii');

                if ($rejectStmt && $rejectStmt->affected_rows > 0) {
                    $notifSql = "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                                VALUES (?, 'application_status', 'Application Not Selected', 'Your job application was reviewed but was not selected for this position.', ?, 'job', ?)";
                    executeQuery($conn, $notifSql, [$appData['worker_id'], $appData['job_id'], "job-details.php?id={$appData['job_id']}"], 'iis');
                    $success = 'Application rejected successfully!';
                } else {
                    $error = 'This application can no longer be rejected because it was already reviewed.';
                }
            }
        } elseif ($action == 'confirm_work_complete') {
            $appData = fetchOne(
                $conn,
                "SELECT ja.worker_id, ja.job_id, ja.worker_confirmed, j.job_type
                 FROM job_applications ja
                 JOIN job_posts j ON ja.job_id = j.job_id
                 WHERE ja.application_id = ? AND ja.employer_id = ? AND ja.job_id = ?",
                [$application_id, getCurrentUserId(), $job_id],
                'iii'
            );

            if (!$appData) {
                $error = 'Application not found.';
            } else {
                beginTransaction($conn);
                try {
                    // Set employer confirmation and both_confirmed timestamp
                    $confirmSql = "UPDATE job_applications
                                   SET employer_confirmed = 1,
                                       both_confirmed_at = NOW()
                                   WHERE application_id = ? AND employer_id = ? AND application_status = 'approved' AND employer_confirmed = 0";
                    $confirmStmt = executeQuery($conn, $confirmSql, [$application_id, getCurrentUserId()], 'ii');

                    if ($confirmStmt && $confirmStmt->affected_rows > 0) {
                        // Calculate rating availability based on job type
                        // One-time jobs: immediate (NULL = available now)
                        // Long-term jobs: 3 days delay
                        $jobType = $appData['job_type'];
                        $immediateRatingTypes = ['one_time'];
                        $delayedRatingTypes = ['full_time', 'part_time', 'contractual', 'internship'];

                        if (in_array($jobType, $immediateRatingTypes, true)) {
                            // One-time job: rating available immediately after both confirm
                            $ratingAvailableAt = null;
                            $ratingMsg = 'Rating is now available immediately.';
                        } elseif (in_array($jobType, $delayedRatingTypes, true)) {
                            // Long-term job: 3-day delay after both confirm
                            $ratingAvailableAt = date('Y-m-d H:i:s', strtotime('+3 days'));
                            $ratingMsg = 'Rating will be available in 3 days (allowing time for final assessment).';
                        } else {
                            // Default: immediate
                            $ratingAvailableAt = null;
                            $ratingMsg = 'Rating is now available.';
                        }

                        // Update rating availability if both parties have now confirmed
                        if ($appData['worker_confirmed'] == 1) {
                            executeQuery(
                                $conn,
                                "UPDATE job_applications SET rating_available_at = ? WHERE application_id = ?",
                                [$ratingAvailableAt, $application_id],
                                'si'
                            );
                        }

                        commitTransaction($conn);

                        executeQuery(
                            $conn,
                            "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                             VALUES (?, 'work_confirmation', 'Work Confirmed by Employer', 'The employer has confirmed the work is complete. {$ratingMsg}', ?, 'job', ?)",
                            [$appData['worker_id'], $appData['job_id'], "job-details.php?id={$appData['job_id']}"],
                            'iis'
                        );
                        $success = 'Work completion confirmed. ' . $ratingMsg;
                    } else {
                        rollbackTransaction($conn);
                        $error = 'Failed to confirm work. Only approved applications can be confirmed.';
                    }
                } catch (Exception $e) {
                    rollbackTransaction($conn);
                    error_log('Employer confirmation failed: ' . $e->getMessage());
                    $error = 'Failed to confirm work. Please try again.';
                }
            }
        }
    }
}

// Get job details
$jobSql = "SELECT j.*, u.full_name as employer_name, u.mobile_number as employer_phone, u.email as employer_email, u.region, u.province
           FROM job_posts j JOIN users u ON j.employer_id = u.user_id WHERE j.job_id = ?";
$job = fetchOne($conn, $jobSql, [$job_id], 'i');

if (!$job) { redirect('index.php'); }

// Check if current user has applied
$hasApplied = false;
$userApplication = null;
$isSaved = false;
if (isLoggedIn() && getCurrentUserType() == 'worker') {
    $userApplication = fetchOne($conn, "SELECT * FROM job_applications WHERE job_id = ? AND worker_id = ?", [$job_id, getCurrentUserId()], 'ii');
    $hasApplied = $userApplication !== null && $userApplication['application_status'] !== 'withdrawn';

    $saved = fetchOne(
        $conn,
        "SELECT interaction_id FROM user_interactions WHERE user_id = ? AND job_id = ? AND interaction_type = 'save' LIMIT 1",
        [getCurrentUserId(), $job_id],
        'ii'
    );
    $isSaved = $saved !== null;
}

// Get applications (for employer)
$applications = [];
if (isLoggedIn() && getCurrentUserId() == $job['employer_id']) {
    $appsSql = "SELECT ja.*, u.full_name, u.mobile_number, u.email, u.trust_score, u.city, u.province,
                GROUP_CONCAT(us.skill_name) as skills
                FROM job_applications ja
                JOIN users u ON ja.worker_id = u.user_id
                LEFT JOIN user_skills us ON u.user_id = us.user_id
                WHERE ja.job_id = ?
                GROUP BY ja.application_id
                ORDER BY ja.applied_at DESC";
    $applications = fetchAll($conn, $appsSql, [$job_id], 'i');
}

// Track view
if (isLoggedIn()) {
    executeQuery($conn, "INSERT INTO user_interactions (user_id, interaction_type, job_id) VALUES (?, 'view', ?)", [getCurrentUserId(), $job_id], 'ii');
}
?>

<div class="container">
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Job Title Header -->
    <div class="panel">
        <div class="section-header">
            <span class="header-square"></span>
            JOB DETAILS ─ <?php echo htmlspecialchars(strtoupper($job['job_title'])); ?>
        </div>
        <div class="panel-body" style="padding: 0.8rem 1rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; font-size: 0.82rem; color: var(--text-muted);">
                <span>
                    <i class="fas fa-building"></i>
                    <a href="employer-profile.php?id=<?php echo $job['employer_id']; ?>" style="color: var(--primary-blue-dark); text-decoration: none; font-weight: 500;">
                        <?php echo htmlspecialchars($job['employer_name']); ?>
                    </a>
                    <a href="employer-profile.php?id=<?php echo $job['employer_id']; ?>" style="font-size: 0.7rem; color: var(--text-muted); margin-left: 4px;">
                        <i class="fas fa-external-link-alt"></i> View Profile
                    </a>
                </span>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location_city'] . ', ' . $job['location_province']); ?></span>
                <span><i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?> / <?php echo $job['pay_type']; ?></span>
                <?php if (!empty($job['job_type'])):
                    $jobTypeInfo = getJobTypeInfo($job['job_type']);
                    if ($jobTypeInfo):
                ?>
                    <span class="tag" style="background: var(--<?php echo $jobTypeInfo['color']; ?>-light, #e8f4f8); color: var(--<?php echo $jobTypeInfo['color']; ?>-dark, #1a5276); font-size: 0.75rem;">
                        <i class="fas <?php echo $jobTypeInfo['icon']; ?>" style="margin-right: 4px;"></i>
                        <?php echo htmlspecialchars($jobTypeInfo['label']); ?>
                    </span>
                <?php endif; endif; ?>
                <?php if (!empty($job['remote_policy'])): ?>
                    <span><i class="fas fa-laptop-house"></i> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $job['remote_policy']))); ?></span>
                <?php endif; ?>
                <?php if (!empty($job['job_category'])): ?>
                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($job['job_category']); ?></span>
                <?php endif; ?>
                <span><i class="fas fa-clock"></i> Posted <?php echo timeAgo($job['created_at']); ?></span>
                <span class="tag tag-<?php echo $job['job_status'] == 'active' ? 'green' : 'gray'; ?>" style="font-size: 0.7rem;"><?php echo ucfirst($job['job_status']); ?></span>
            </div>
        </div>
    </div>

    <!-- Job Image Display -->
    <?php if (!empty($job['job_image'])): ?>
    <div class="panel" style="margin-bottom: 16px;">
        <div class="panel-body" style="padding: 0; overflow: hidden;">
            <img src="<?php echo htmlspecialchars($job['job_image']); ?>" alt="Job Area" style="width: 100%; max-height: 400px; object-fit: cover; display: block;">
        </div>
    </div>
    <?php endif; ?>

    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Description -->
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    JOB DESCRIPTION
                </div>
                <div class="panel-body">
                    <p style="color: var(--text-dark); line-height: 1.8; white-space: pre-wrap; font-size: 0.88rem;">
<?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($job['specific_address'])): ?>
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    SPECIFIC LOCATION
                </div>
                <div class="panel-body">
                    <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo nl2br(htmlspecialchars($job['specific_address'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    JOB TIMELINE &amp; SLOTS
                </div>
                <div class="panel-body">
                    <table class="data-table" style="font-size: 0.82rem;">
                        <?php if ($job['start_date']): ?>
                            <tr><td class="text-muted">Start Date</td><td><?php echo date('F d, Y', strtotime($job['start_date'])); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($job['end_date']): ?>
                            <tr><td class="text-muted">End Date</td><td><?php echo date('F d, Y', strtotime($job['end_date'])); ?></td></tr>
                        <?php endif; ?>
                        <tr><td class="text-muted">Available Slots</td><td><?php echo ($job['slots_available'] - $job['slots_filled']) . ' / ' . $job['slots_available']; ?></td></tr>
                    </table>
                </div>
            </div>

            <?php if (isLoggedIn() && getCurrentUserType() == 'worker'): ?>
            <div class="panel">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    QUICK ACTION
                </div>
                <div class="panel-body" style="display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center;">
                    <form method="POST" style="margin: 0;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="<?php echo $isSaved ? 'unsave_job' : 'save_job'; ?>">
                        <button type="submit" class="btn <?php echo $isSaved ? 'btn-outline' : 'btn-secondary'; ?> btn-small">
                            <i class="fas fa-bookmark"></i>
                            <?php echo $isSaved ? 'Remove Saved' : 'Save Job'; ?>
                        </button>
                    </form>
                    <span class="text-small text-muted">Save jobs to revisit them later from your dashboard.</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Application Form -->
            <?php if (isLoggedIn() && getCurrentUserType() == 'worker' && !$hasApplied && $job['job_status'] == 'active' && ($job['slots_filled'] < $job['slots_available'])): ?>
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    APPLY FOR THIS JOB
                </div>
                <div class="panel-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="apply">
                        <div class="form-group">
                            <label for="resume_file">Resume <span class="text-muted" style="font-weight: 400;">(PDF only, max 5MB)</span></label>
                            <input type="file" id="resume_file" name="resume_file" class="form-control" accept=".pdf,application/pdf">
                            <small class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem; display: block;">Upload your resume in PDF format to increase your chances of getting hired.</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Submit Application
                        </button>
                    </form>

                </div>
            </div>
            <?php elseif ($hasApplied): ?>
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    APPLICATION STATUS
                </div>
                <div class="panel-body">
                    <p style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-check-circle"></i> Applied on <?php echo date('F d, Y', strtotime($userApplication['applied_at'])); ?>
                    </p>
                    <span class="tag tag-<?php 
                        echo $userApplication['application_status'] == 'approved' ? 'green' : 
                            ($userApplication['application_status'] == 'rejected' ? 'red' : 'blue'); 
                    ?>">
                        <?php echo ucfirst($userApplication['application_status']); ?>
                    </span>

                    <?php if ($userApplication['application_status'] == 'pending'): ?>
                        <form method="POST" style="margin-top: 0.7rem;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="withdraw">
                            <button type="submit" class="btn btn-outline btn-small" onclick="return confirm('Withdraw this application?');">
                                <i class="fas fa-undo"></i> Withdraw Application
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Work Confirmation Section for Approved Jobs -->
                    <?php if ($userApplication['application_status'] == 'approved'): ?>
                        <div style="margin-top: 0.7rem; padding-top: 0.7rem; border-top: 1px solid var(--border-light);">
                            <?php if ($userApplication['worker_confirmed'] == 0): ?>
                                <form method="POST" style="margin-top: 0.7rem;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="confirm_work_complete">
                                    <button type="submit" class="btn btn-primary btn-small" style="width: 100%;" onclick="return confirm('Confirm that you have completed the work for this job?');">
                                        <i class="fas fa-check-circle"></i> Confirm Work Complete
                                    </button>
                                </form>
                            <?php else: ?>
                                <p style="font-size: 0.85rem; color: var(--primary-blue);">
                                    <i class="fas fa-check-circle"></i> You have confirmed work completion
                                    <?php if ($userApplication['employer_confirmed'] == 0): ?>
                                        <span class="text-muted">- Waiting for employer confirmation</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Rating Section for Completed Jobs -->
                    <?php if ($userApplication['application_status'] == 'approved' &&
                             $userApplication['worker_confirmed'] == 1 &&
                             $userApplication['employer_confirmed'] == 1):
                        $existingRating = fetchOne(
                            $conn,
                            "SELECT rating_id FROM job_ratings WHERE application_id = ? AND rating_type = 'worker_to_employer'",
                            [$userApplication['application_id']],
                            'i'
                        );

                        // Check if rating is available based on job type timing rules
                        $isRatingAvailable = true;
                        $ratingWaitMessage = '';

                        if (!empty($userApplication['rating_available_at'])) {
                            $ratingAvailableTime = strtotime($userApplication['rating_available_at']);
                            $currentTime = time();

                            if ($currentTime < $ratingAvailableTime) {
                                $isRatingAvailable = false;
                                $hoursRemaining = ceil(($ratingAvailableTime - $currentTime) / 3600);
                                $daysRemaining = ceil($hoursRemaining / 24);

                                if ($daysRemaining > 1) {
                                    $ratingWaitMessage = "Rating will be available in {$daysRemaining} days (cooling-off period for long-term jobs).";
                                } elseif ($hoursRemaining > 1) {
                                    $ratingWaitMessage = "Rating will be available in {$hoursRemaining} hours.";
                                } else {
                                    $ratingWaitMessage = "Rating will be available within the hour.";
                                }
                            }
                        }
                    ?>
                        <div style="margin-top: 0.7rem; padding-top: 0.7rem; border-top: 1px solid var(--border-light);">
                            <?php if ($existingRating): ?>
                                <p style="font-size: 0.85rem; color: var(--primary-blue);">
                                    <i class="fas fa-star"></i> You have already rated this employer
                                </p>
                            <?php elseif (!$isRatingAvailable): ?>
                                <div style="background: var(--off-white); padding: 0.8rem; border-radius: 6px; text-align: center;">
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($ratingWaitMessage); ?>
                                    </p>
                                    <small class="text-muted" style="display: block; margin-top: 4px;">
                                        Long-term jobs have a 3-day assessment period before ratings open.
                                    </small>
                                </div>
                            <?php else: ?>
                                <a href="rate-employer.php?app_id=<?php echo $userApplication['application_id']; ?>" class="btn btn-primary btn-small" style="width: 100%;">
                                    <i class="fas fa-star"></i> Rate This Employer
                                </a>
                                <?php if ($job['job_type'] == 'one_time'): ?>
                                    <small class="text-muted" style="display: block; margin-top: 4px; text-align: center;">
                                        One-time job: Rating available immediately
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Applications List (for employer) -->
            <?php if (isLoggedIn() && getCurrentUserId() == $job['employer_id']): ?>
            <div class="panel">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    APPLICATIONS (<?php echo count($applications); ?>)
                </div>
                <div class="panel-body">
                    <?php if (empty($applications)): ?>
                        <p class="text-muted text-center" style="padding: 1.5rem;">No applications yet.</p>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <div class="compact-job-item" style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-light);">
                                <div style="display: flex; gap: 0.8rem; align-items: flex-start;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">
                                        <?php echo strtoupper(substr($app['full_name'], 0, 1)); ?>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <strong style="font-size: 0.88rem;"><?php echo htmlspecialchars($app['full_name']); ?></strong>
                                            <span class="tag tag-<?php echo $app['application_status'] == 'approved' ? 'green' : ($app['application_status'] == 'rejected' ? 'red' : 'blue'); ?>" style="font-size: 0.65rem;">
                                                <?php echo ucfirst($app['application_status']); ?>
                                            </span>
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($app['city'] . ', ' . $app['province']); ?> &middot;
                                            <i class="fas fa-star"></i> <?php echo number_format($app['trust_score'], 2); ?> &middot;
                                            Applied <?php echo timeAgo($app['applied_at']); ?>
                                        </div>

                                        <?php if (!empty($app['skills'])): ?>
                                            <div style="margin-top: 4px;">
                                                <?php foreach (array_slice(explode(',', $app['skills']), 0, 4) as $skill): ?>
                                                    <span class="tag tag-pink" style="font-size: 0.6rem;"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app['cover_letter'])): ?>
                                            <div style="margin-top: 4px; font-size: 0.8rem; color: var(--text-dark); padding: 0.4rem; background: var(--off-white); border-radius: 4px;">
                                                <?php echo nl2br(htmlspecialchars(substr($app['cover_letter'], 0, 200))); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app['resume_file'])): ?>
                                            <div style="margin-top: 4px;">
                                                <a href="<?php echo htmlspecialchars($app['resume_file']); ?>" target="_blank" class="btn btn-outline btn-small" style="font-size: 0.7rem;">
                                                    <i class="fas fa-file-pdf"></i> View Resume
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <div style="margin-top: 6px; display: flex; gap: 0.4rem;">

                                            <?php if ($app['application_status'] == 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                    <button type="submit" class="btn btn-primary btn-small"><i class="fas fa-check"></i> Approve</button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                    <button type="submit" class="btn btn-outline btn-small"><i class="fas fa-times"></i> Reject</button>
                                                </form>
                                            <?php elseif ($app['application_status'] == 'approved'): ?>
                                                <?php if ($app['employer_confirmed'] == 0): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="confirm_work_complete">
                                                        <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                        <button type="submit" class="btn btn-primary btn-small" onclick="return confirm('Confirm that the work has been completed?');"><i class="fas fa-check-circle"></i> Confirm Complete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-small text-muted" style="font-size: 0.7rem;">
                                                        <i class="fas fa-check-circle"></i> Confirmed
                                                        <?php if ($app['worker_confirmed'] == 0): ?>
                                                            <span>- Waiting for worker</span>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <a href="worker-profile.php?id=<?php echo $app['worker_id']; ?>" class="btn btn-outline btn-small">
                                                <i class="fas fa-user"></i> View Profile
                                            </a>
                                            <a href="messages.php?user=<?php echo $app['worker_id']; ?>" class="btn btn-secondary btn-small">
                                                <i class="fas fa-envelope"></i> Message
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Skills -->
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    REQUIRED SKILLS
                </div>
                <div class="panel-body">
                    <?php if (!empty($job['required_skills'])): ?>
                        <div class="tech-tags">
                            <?php foreach (explode(',', $job['required_skills']) as $skill): ?>
                                <span class="tag tag-pink"><?php echo htmlspecialchars(trim($skill)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted" style="font-size: 0.82rem;">No specific skills required</p>
                    <?php endif; ?>

                    <?php if (!empty($job['preferred_skills'])): ?>
                        <div style="margin-top: 0.8rem; padding-top: 0.6rem; border-top: 1px solid var(--border-light);">
                            <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">PREFERRED</div>
                            <div class="tech-tags">
                                <?php foreach (explode(',', $job['preferred_skills']) as $skill): ?>
                                    <span class="tag tag-outline"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Compensation -->
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    COMPENSATION
                </div>
                <div class="panel-body text-center">
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--primary-blue-dark);">
                        <?php echo formatCurrency($job['pay_amount']); ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.8rem;">per <?php echo $job['pay_type']; ?></div>
                    <?php if ($job['advance_payment_amount'] > 0): ?>
                        <div style="margin-top: 0.6rem; padding: 0.5rem; background: var(--off-white); border-radius: 4px; font-size: 0.8rem;">
                            <strong>Advance:</strong> <?php echo formatCurrency($job['advance_payment_amount']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Employer Info -->
            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    EMPLOYER INFO
                </div>
                <div class="panel-body">
                    <table class="data-table" style="font-size: 0.78rem;">
                        <tr><td class="text-muted">Name</td><td><?php echo htmlspecialchars($job['employer_name']); ?></td></tr>
                        <tr><td class="text-muted">Region</td><td><?php echo htmlspecialchars($job['region'] ?? 'N/A'); ?></td></tr>
                        <tr><td class="text-muted">Province</td><td><?php echo htmlspecialchars($job['province'] ?? 'N/A'); ?></td></tr>
                    </table>
                </div>
            </div>

            <?php if (!isLoggedIn()): ?>
            <div class="widget">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    APPLY NOW
                </div>
                <div class="panel-body text-center">
                    <div class="notice-text" style="margin-bottom: 0.8rem;">Sign up or login to apply for this job</div>
                    <a href="signup.php" class="btn btn-primary btn-small" style="width: 100%; margin-bottom: 0.4rem;">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                    <a href="login.php" class="btn btn-outline btn-small" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
