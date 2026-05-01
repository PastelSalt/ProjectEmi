<?php
/**
 * Rate Employer Page (Worker)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Rate Employer';
require_once 'config/config.php';
requireUserType('worker');
require_once 'includes/header.php';

$conn = getDBConnection();
$worker_id = getCurrentUserId();
$application_id = (int)($_GET['app_id'] ?? 0);

$success = '';
$error = '';

// Fetch application and verify permissions
$application = fetchOne(
    $conn,
    "SELECT ja.*, j.job_title, u.full_name as employer_name, u.user_id as employer_id
     FROM job_applications ja
     JOIN job_posts j ON ja.job_id = j.job_id
     JOIN users u ON ja.employer_id = u.user_id
     WHERE ja.application_id = ? AND ja.worker_id = ? AND ja.application_status = 'approved' 
     AND ja.worker_confirmed = 1 AND ja.employer_confirmed = 1",
    [$application_id, $worker_id],
    'ii'
);

if (!$application) {
    redirect('dashboard-worker.php');
}

// Check if already rated
$existingRating = fetchOne(
    $conn,
    "SELECT rating_id FROM job_ratings WHERE application_id = ? AND rating_type = 'worker_to_employer'",
    [$application_id],
    'i'
);

if (!empty($existingRating)) {
    $_SESSION['flash_info'] = 'You have already rated this employer.';
    redirect('dashboard-worker.php');
}

// Check if rating is available (based on job type timing rules)
$ratingAvailable = true;
$ratingWaitInfo = '';

if (!empty($application['rating_available_at'])) {
    $ratingAvailableTime = strtotime($application['rating_available_at']);
    $currentTime = time();

    if ($currentTime < $ratingAvailableTime) {
        $ratingAvailable = false;
        $hoursRemaining = ceil(($ratingAvailableTime - $currentTime) / 3600);
        $daysRemaining = ceil($hoursRemaining / 24);

        if ($daysRemaining > 1) {
            $ratingWaitInfo = "Rating will be available in {$daysRemaining} days.";
        } elseif ($hoursRemaining > 1) {
            $ratingWaitInfo = "Rating will be available in {$hoursRemaining} hours.";
        } else {
            $ratingWaitInfo = "Rating will be available within the hour.";
        }
    }
}

if (!$ratingAvailable) {
    $_SESSION['flash_info'] = $ratingWaitInfo . ' Long-term jobs have a 3-day cooling-off period before ratings can be submitted.';
    redirect('dashboard-worker.php');
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    }

    $rating_stars = (int)($_POST['rating_stars'] ?? 0);
    $feedback = sanitizeMultilineInput($_POST['feedback'] ?? '');
    
    // Validate rating
    if ($rating_stars < 1 || $rating_stars > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } elseif (strlen($feedback) > 500) {
        $error = 'Feedback cannot exceed 500 characters.';
    } else {
        // Insert rating
        if (executeQuery(
            $conn,
            "INSERT INTO job_ratings (application_id, rater_id, ratee_id, rating_type, rating_stars, feedback, created_at)
             VALUES (?, ?, ?, 'worker_to_employer', ?, ?, NOW())",
            [$application_id, $worker_id, $application['employer_id'], $rating_stars, $feedback ?: null],
            'iiiis'
        )) {
            // Also insert into employer_reviews for public profile display
            executeQuery(
                $conn,
                "INSERT INTO employer_reviews (employer_id, worker_id, job_id, rating, review_text, is_public, created_at)
                 VALUES (?, ?, ?, ?, ?, TRUE, NOW())
                 ON DUPLICATE KEY UPDATE
                 rating = VALUES(rating),
                 review_text = VALUES(review_text),
                 updated_at = NOW()",
                [$application['employer_id'], $worker_id, $application['job_id'], $rating_stars, $feedback ?: null],
                'iiiis'
            );

            // Update employer's trust score
            $newScore = updateUserTrustScore($conn, $application['employer_id'], null, $worker_id);

            // Create notification for employer
            executeQuery(
                $conn,
                "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url, created_at)
                 VALUES (?, 'rating_received', 'New Rating Received', CONCAT('You received a ', ?, '-star rating from a worker'), ?, 'job', ?, NOW())",
                [$application['employer_id'], $rating_stars, $application['job_id'], 'employer-profile.php?id=' . $application['employer_id']],
                'iiis'
            );

            $_SESSION['flash_success'] = 'Thank you! Your rating has been submitted. The employer\'s trust score has been updated.';
            redirect('dashboard-worker.php');
        } else {
            $error = 'Failed to submit rating. Please try again.';
        }
    }
}
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    RATE THIS EMPLOYER
                </div>
                <div class="panel-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Employer Info -->
                    <div class="d-flex align-center gap-2 mb-3" style="padding-bottom: 16px; border-bottom: 1px solid #E8ECF5;">
                        <div class="message-avatar" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($application['employer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($application['employer_name']); ?></h4>
                            <p class="text-small text-muted" style="margin: 0;">
                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($application['job_title']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Rating Form -->
                    <form method="POST">
                        <?php echo csrfField(); ?>

                        <div class="form-group">
                            <label class="text-small" style="display: block; margin-bottom: 12px;">
                                <strong>How would you rate this employer? <span class="text-pink">*</span></strong>
                            </label>
                            <div style="display: flex; gap: 12px; font-size: 1.8rem;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label style="cursor: pointer; display: flex; align-items: center;">
                                        <input type="radio" name="rating_stars" value="<?php echo $i; ?>" style="margin-right: 8px; cursor: pointer;" required>
                                        <span class="star-icon" style="color: #FFD700;">
                                            <?php for ($j = 0; $j < $i; $j++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                            <?php for ($j = $i; $j < 5; $j++): ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="feedback" class="text-small"><strong>Additional Feedback (Optional)</strong></label>
                            <textarea id="feedback" name="feedback" class="form-control" rows="4" 
                                      placeholder="Share your experience working with this employer (max 500 characters)"
                                      maxlength="500"></textarea>
                            <small class="text-muted" style="display: block; margin-top: 4px;">
                                <span id="char-count">0</span>/500 characters
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Submit Rating
                            </button>
                            <a href="dashboard-worker.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    RATING GUIDE
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        <strong>5 Stars:</strong> Excellent employer, professional & fair<br><br>
                        <strong>4 Stars:</strong> Good employer, reliable<br><br>
                        <strong>3 Stars:</strong> Satisfactory employer<br><br>
                        <strong>2 Stars:</strong> Below expectations, issues encountered<br><br>
                        <strong>1 Star:</strong> Poor employer, significant problems
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    ABOUT RATINGS
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        Your honest rating helps build trust in our platform and helps employers improve. Ratings are averaged to calculate each user's trust score.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const feedback = document.getElementById('feedback');
    const charCount = document.getElementById('char-count');
    
    if (feedback) {
        feedback.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
