<?php
/**
 * Rate Worker Page (Employer)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Rate Worker';
require_once 'config/config.php';
requireUserType('employer');
require_once 'includes/header.php';

$conn = getDBConnection();
$employer_id = getCurrentUserId();
$application_id = (int)($_GET['app_id'] ?? 0);

$success = '';
$error = '';

// Fetch application and verify permissions
$application = fetchOne(
    $conn,
    "SELECT ja.*, j.job_title, u.full_name as worker_name, u.user_id as worker_id
     FROM job_applications ja
     JOIN job_posts j ON ja.job_id = j.job_id
     JOIN users u ON ja.worker_id = u.user_id
     WHERE ja.application_id = ? AND ja.employer_id = ? AND ja.application_status = 'approved' 
     AND ja.worker_confirmed = 1 AND ja.employer_confirmed = 1",
    [$application_id, $employer_id],
    'ii'
);

if (!$application) {
    redirect('dashboard-employer.php');
}

// Check if already rated
$existingRating = fetchOne(
    $conn,
    "SELECT rating_id FROM job_ratings WHERE application_id = ? AND rating_type = 'employer_to_worker'",
    [$application_id],
    'i'
);

if (!empty($existingRating)) {
    $_SESSION['flash_info'] = 'You have already rated this worker.';
    redirect('dashboard-employer.php');
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
             VALUES (?, ?, ?, 'employer_to_worker', ?, ?, NOW())",
            [$application_id, $employer_id, $application['worker_id'], $rating_stars, $feedback ?: null],
            'iiiis'
        )) {
            // Update worker's trust score
            $newScore = updateUserTrustScore($conn, $application['worker_id'], null, $employer_id);
            
            // Create notification for worker
            executeQuery(
                $conn,
                "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url, created_at)
                 VALUES (?, 'rating_received', 'New Rating Received', CONCAT('You received a ', ?, '-star rating from an employer'), ?, 'job', ?, NOW())",
                [$application['worker_id'], $rating_stars, $application['job_id'], 'job-details.php?id=' . $application['job_id']],
                'iiis'
            );
            
            $_SESSION['flash_success'] = 'Thank you! Your rating has been submitted. The worker\'s trust score has been updated.';
            redirect('dashboard-employer.php');
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
                    RATE THIS WORKER
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

                    <!-- Worker Info -->
                    <div class="d-flex align-center gap-2 mb-3" style="padding-bottom: 16px; border-bottom: 1px solid #E8ECF5;">
                        <div class="message-avatar" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($application['worker_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($application['worker_name']); ?></h4>
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
                                <strong>How would you rate this worker? <span class="text-pink">*</span></strong>
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
                                      placeholder="Share your experience working with this worker (max 500 characters)"
                                      maxlength="500"></textarea>
                            <small class="text-muted" style="display: block; margin-top: 4px;">
                                <span id="char-count">0</span>/500 characters
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Submit Rating
                            </button>
                            <a href="dashboard-employer.php" class="btn btn-outline">
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
                        <strong>5 Stars:</strong> Excellent work, highly recommended<br><br>
                        <strong>4 Stars:</strong> Good work, met expectations<br><br>
                        <strong>3 Stars:</strong> Satisfactory work, adequate performance<br><br>
                        <strong>2 Stars:</strong> Below expectations, some issues<br><br>
                        <strong>1 Star:</strong> Poor work, significant problems
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
                        Your honest rating helps build trust in our platform and helps workers improve their services. Ratings are averaged to calculate each user's trust score.
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
