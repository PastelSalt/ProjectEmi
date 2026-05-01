<?php
/**
 * Onboarding Wizard for New Users
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Welcome - Complete Your Profile';
require_once 'config/config.php';

// Only allow logged-in users who haven't completed onboarding
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = getCurrentUserId();
$user_type = getCurrentUserType();
$conn = getDBConnection();

// Check if user has already completed onboarding
$user = fetchOne($conn, "SELECT onboarding_completed, profile_picture, bio FROM users WHERE user_id = ?", [$user_id], 'i');
if ($user && $user['onboarding_completed']) {
    redirect($user_type === 'worker' ? 'dashboard-worker.php' : 'dashboard-employer.php');
}

$success = '';
$error = '';
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($current_step < 1) $current_step = 1;
if ($current_step > 4) $current_step = 4;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';
        
        // Step 1: Profile Photo & Basic Info
        if ($action === 'step1') {
            $bio = sanitizeMultilineInput($_POST['bio'] ?? '');
            
            // Handle profile picture upload
            $profile_picture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_picture'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = mime_content_type($file['tmp_name']);
                
                if (in_array($fileType, $allowedMimeTypes, true) && $file['size'] <= 2 * 1024 * 1024) {
                    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $uniqueFileName = uniqid('profile_', true) . '_' . $user_id . '.' . $fileExtension;
                    $destination = PROFILE_PICS_DIR . $uniqueFileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $profile_picture = 'uploads/profiles/' . $uniqueFileName;
                    }
                }
            }
            
            if ($profile_picture) {
                executeQuery($conn, "UPDATE users SET bio = ?, profile_picture = ? WHERE user_id = ?", [$bio, $profile_picture, $user_id], 'ssi');
            } else {
                executeQuery($conn, "UPDATE users SET bio = ? WHERE user_id = ?", [$bio, $user_id], 'si');
            }
            
            redirect('onboarding.php?step=2');
        }
        
        // Step 2: Skills (for workers) or Company Info (for employers)
        elseif ($action === 'step2') {
            if ($user_type === 'worker') {
                $skills = isset($_POST['skills']) ? $_POST['skills'] : [];
                // Clear existing skills and add new ones
                executeQuery($conn, "DELETE FROM user_skills WHERE user_id = ?", [$user_id], 'i');
                foreach ($skills as $skill) {
                    $skill = sanitizeInput($skill);
                    if (!empty($skill)) {
                        executeQuery($conn, "INSERT INTO user_skills (user_id, skill_name) VALUES (?, ?)", [$user_id, $skill], 'is');
                    }
                }
            } else {
                $employer_subtype = sanitizeInput($_POST['employer_subtype'] ?? '');
                $company_name = sanitizeInput($_POST['company_name'] ?? '');
                if (in_array($employer_subtype, ['company', 'individual'])) {
                    executeQuery($conn, "UPDATE users SET employer_subtype = ?, company_name = ? WHERE user_id = ?", 
                        [$employer_subtype, $company_name, $user_id], 'ssi');
                }
            }
            redirect('onboarding.php?step=3');
        }
        
        // Step 3: Notification Preferences
        elseif ($action === 'step3') {
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
            $job_alerts = isset($_POST['job_alerts']) ? 1 : 0;
            $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;
            
            // Insert or update notification preferences
            $existing = fetchOne($conn, "SELECT id FROM notification_preferences WHERE user_id = ?", [$user_id], 'i');
            if ($existing) {
                executeQuery($conn, 
                    "UPDATE notification_preferences SET email_notifications = ?, sms_notifications = ?, job_alerts = ?, message_notifications = ? WHERE user_id = ?",
                    [$email_notifications, $sms_notifications, $job_alerts, $message_notifications, $user_id], 'iiiii'
                );
            } else {
                executeQuery($conn,
                    "INSERT INTO notification_preferences (user_id, email_notifications, sms_notifications, job_alerts, message_notifications) VALUES (?, ?, ?, ?, ?)",
                    [$user_id, $email_notifications, $sms_notifications, $job_alerts, $message_notifications], 'iiiii'
                );
            }
            redirect('onboarding.php?step=4');
        }
        
        // Step 4: Complete onboarding
        elseif ($action === 'complete') {
            executeQuery($conn, "UPDATE users SET onboarding_completed = 1, onboarding_completed_at = NOW() WHERE user_id = ?", [$user_id], 'i');
            $_SESSION['flash_success'] = 'Welcome to ' . SITE_NAME . '! Your profile is now complete.';
            redirect($user_type === 'worker' ? 'dashboard-worker.php' : 'dashboard-employer.php');
        }
    }
}

// Get user's current skills (for workers)
$user_skills = [];
if ($user_type === 'worker') {
    $skills_result = fetchAll($conn, "SELECT skill_name FROM user_skills WHERE user_id = ?", [$user_id], 'i');
    foreach ($skills_result as $s) {
        $user_skills[] = $s['skill_name'];
    }
}

// Get notification preferences
$notif_prefs = fetchOne($conn, 
    "SELECT * FROM notification_preferences WHERE user_id = ?", 
    [$user_id], 'i'
) ?: ['email_notifications' => 1, 'sms_notifications' => 0, 'job_alerts' => 1, 'message_notifications' => 1];

closeDBConnection($conn);

require_once 'includes/header.php';
?>

<div class="container">
    <div class="onboarding-container" style="max-width: 700px; margin: 2rem auto;">
        <!-- Progress Bar -->
        <div class="onboarding-progress" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="progress-step <?php echo $i <= $current_step ? 'active' : ''; ?> <?php echo $i < $current_step ? 'completed' : ''; ?>" 
                         style="text-align: center; flex: 1;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $i <= $current_step ? 'var(--primary-blue)' : 'var(--gray-lightest)'; ?>; 
                                    color: <?php echo $i <= $current_step ? 'white' : 'var(--text-muted)'; ?>; display: flex; align-items: center; justify-content: center; 
                                    margin: 0 auto 0.5rem; font-weight: bold; border: 3px solid <?php echo $i < $current_step ? 'var(--success-green)' : ($i == $current_step ? 'var(--primary-blue)' : 'var(--gray-mid)'); ?>;">
                            <?php echo $i < $current_step ? '✓' : $i; ?>
                        </div>
                        <small style="color: <?php echo $i == $current_step ? 'var(--primary-blue)' : 'var(--text-muted)'; ?>; font-weight: <?php echo $i == $current_step ? 'bold' : 'normal'; ?>;">
                            <?php 
                            $step_labels = ['Profile', $user_type === 'worker' ? 'Skills' : 'Company', 'Notifications', 'Done!'];
                            echo $step_labels[$i-1];
                            ?>
                        </small>
                    </div>
                    <?php if ($i < 4): ?>
                        <div style="flex: 1; height: 3px; background: <?php echo $i < $current_step ? 'var(--success-green)' : 'var(--gray-lightest)'; ?>; margin-top: 18px;"></div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Step Content -->
        <div class="panel">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin-bottom: 1rem;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($current_step === 1): ?>
                <!-- Step 1: Profile Photo & Bio -->
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    Step 1: Tell Us About Yourself
                </div>
                <div class="panel-body">
                    <p class="text-muted" style="margin-bottom: 1.5rem;">Let's start by adding a profile photo and telling others a bit about you.</p>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="step1">
                        
                        <div class="form-group">
                            <label><strong>Profile Photo</strong> <span class="text-muted">(optional)</span></label>
                            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                                <div id="photo-preview" style="width: 100px; height: 100px; border-radius: 50%; background: var(--gray-lightest); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px dashed var(--gray-mid);">
                                    <i class="fas fa-user" style="font-size: 2rem; color: var(--gray-mid);"></i>
                                </div>
                                <div>
                                    <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/jpeg,image/png,image/webp" style="margin-bottom: 0.5rem;">
                                    <small class="text-muted">Max 2MB. JPEG, PNG, or WebP.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio"><strong>Bio / About You</strong> <span class="text-muted">(optional)</span></label>
                            <textarea name="bio" id="bio" class="form-control" rows="4" maxlength="500" 
                                      placeholder="<?php echo $user_type === 'worker' ? 'Describe your experience, skills, and what kind of work you are looking for...' : 'Describe your company and the type of workers you are looking for...'; ?>"></textarea>
                            <small class="text-muted"><span id="bio-count">0</span>/500 characters</small>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <a href="<?php echo $user_type === 'worker' ? 'dashboard-worker.php' : 'dashboard-employer.php'; ?>" class="btn btn-outline">Skip for now</a>
                            <button type="submit" class="btn btn-primary">Continue <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </form>
                </div>

            <?php elseif ($current_step === 2): ?>
                <!-- Step 2: Skills or Company Info -->
                <div class="section-header section-header-blue">
                    <span class="header-square"></span>
                    Step 2: <?php echo $user_type === 'worker' ? 'Your Skills' : 'Company Information'; ?>
                </div>
                <div class="panel-body">
                    <?php if ($user_type === 'worker'): ?>
                        <p class="text-muted" style="margin-bottom: 1.5rem;">Add skills to help employers find you for the right jobs.</p>
                        
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="step2">
                            
                            <div class="form-group">
                                <label><strong>Your Skills</strong></label>
                                <div id="skills-container" style="margin-bottom: 1rem;">
                                    <?php foreach ($user_skills as $skill): ?>
                                        <span class="tag tag-pink" style="display: inline-flex; align-items: center; gap: 0.5rem; margin: 0.2rem;">
                                            <?php echo htmlspecialchars($skill); ?>
                                            <button type="button" onclick="removeSkill(this)" style="background: none; border: none; cursor: pointer; color: inherit;">×</button>
                                            <input type="hidden" name="skills[]" value="<?php echo htmlspecialchars($skill); ?>">
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="text" id="skill-input" class="form-control" placeholder="Add a skill (e.g., PHP, Cooking, Graphic Design)" maxlength="50">
                                    <button type="button" onclick="addSkill()" class="btn btn-secondary">Add</button>
                                </div>
                                <small class="text-muted">Press Enter or click Add to add a skill</small>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                                <a href="onboarding.php?step=1" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
                                <button type="submit" class="btn btn-primary">Continue <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </form>
                        
                        <script>
                        function addSkill() {
                            const input = document.getElementById('skill-input');
                            const skill = input.value.trim();
                            if (skill) {
                                const container = document.getElementById('skills-container');
                                const span = document.createElement('span');
                                span.className = 'tag tag-pink';
                                span.style.cssText = 'display: inline-flex; align-items: center; gap: 0.5rem; margin: 0.2rem;';
                                span.innerHTML = skill + ' <button type="button" onclick="removeSkill(this)" style="background: none; border: none; cursor: pointer; color: inherit;">×</button><input type="hidden" name="skills[]" value="' + skill.replace(/"/g, '&quot;') + '">';
                                container.appendChild(span);
                                input.value = '';
                            }
                        }
                        function removeSkill(btn) {
                            btn.parentElement.remove();
                        }
                        document.getElementById('skill-input').addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                addSkill();
                            }
                        });
                        </script>
                    <?php else: ?>
                        <p class="text-muted" style="margin-bottom: 1.5rem;">Tell us about your organization.</p>
                        
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="step2">
                            
                            <div class="form-group">
                                <label><strong>Employer Type</strong></label>
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <label style="flex: 1; min-width: 200px; padding: 1rem; border: 2px solid var(--border-light); border-radius: 8px; cursor: pointer; text-align: center;" class="employer-type-option">
                                        <input type="radio" name="employer_subtype" value="company" required style="display: none;">
                                        <i class="fas fa-building" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 0.5rem;"></i>
                                        <div><strong>Company</strong></div>
                                        <small class="text-muted">Business or organization</small>
                                    </label>
                                    <label style="flex: 1; min-width: 200px; padding: 1rem; border: 2px solid var(--border-light); border-radius: 8px; cursor: pointer; text-align: center;" class="employer-type-option">
                                        <input type="radio" name="employer_subtype" value="individual" required style="display: none;">
                                        <i class="fas fa-user" style="font-size: 2rem; color: var(--green-badge); margin-bottom: 0.5rem;"></i>
                                        <div><strong>Individual</strong></div>
                                        <small class="text-muted">Personal tasks or small jobs</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="company_name"><strong>Company/Organization Name</strong> <span class="text-muted">(optional)</span></label>
                                <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Your company or organization name">
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                                <a href="onboarding.php?step=1" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
                                <button type="submit" class="btn btn-primary">Continue <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </form>
                        
                        <script>
                        document.querySelectorAll('.employer-type-option').forEach(option => {
                            option.addEventListener('click', function() {
                                document.querySelectorAll('.employer-type-option').forEach(o => o.style.borderColor = 'var(--border-light)');
                                this.style.borderColor = 'var(--primary-blue)';
                                this.querySelector('input').checked = true;
                            });
                        });
                        </script>
                    <?php endif; ?>
                </div>

            <?php elseif ($current_step === 3): ?>
                <!-- Step 3: Notification Preferences -->
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    Step 3: Notification Preferences
                </div>
                <div class="panel-body">
                    <p class="text-muted" style="margin-bottom: 1.5rem;">Choose how you want to be notified about important updates.</p>
                    
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="step3">
                        
                        <div style="display: grid; gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="email_notifications" value="1" <?php echo $notif_prefs['email_notifications'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-envelope" style="color: var(--primary-blue);"></i> Email Notifications</strong></div>
                                    <small class="text-muted">Receive updates about your applications, messages, and job matches</small>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="sms_notifications" value="1" <?php echo $notif_prefs['sms_notifications'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-sms" style="color: var(--green-badge);"></i> SMS Notifications</strong></div>
                                    <small class="text-muted">Get text messages for urgent updates (standard rates apply)</small>
                                </div>
                            </label>
                            
                            <?php if ($user_type === 'worker'): ?>
                            <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="job_alerts" value="1" <?php echo $notif_prefs['job_alerts'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-briefcase" style="color: var(--yellow-badge);"></i> Job Alerts</strong></div>
                                    <small class="text-muted">Get notified when new jobs matching your skills are posted</small>
                                </div>
                            </label>
                            <?php endif; ?>
                            
                            <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="message_notifications" value="1" <?php echo $notif_prefs['message_notifications'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                <div style="flex: 1;">
                                    <div><strong><i class="fas fa-comment" style="color: var(--purple-light);"></i> Message Notifications</strong></div>
                                    <small class="text-muted">Be notified when you receive new messages</small>
                                </div>
                            </label>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <a href="onboarding.php?step=2" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
                            <button type="submit" class="btn btn-primary">Continue <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </form>
                </div>

            <?php elseif ($current_step === 4): ?>
                <!-- Step 4: Welcome / Done -->
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    You're All Set!
                </div>
                <div class="panel-body text-center" style="padding: 2rem;">
                    <div style="font-size: 4rem; color: var(--success-green); margin-bottom: 1rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 style="margin-bottom: 1rem;">Welcome to <?php echo SITE_NAME; ?>!</h2>
                    <p class="text-muted" style="margin-bottom: 2rem; font-size: 1.1rem;">
                        Your profile is now complete. You can start <?php echo $user_type === 'worker' ? 'browsing and applying for jobs' : 'posting jobs and finding workers'; ?> right away!
                    </p>
                    
                    <div style="display: grid; gap: 1rem; max-width: 400px; margin: 0 auto 2rem;">
                        <?php if ($user_type === 'worker'): ?>
                            <a href="for-you.php" class="btn btn-primary btn-large"><i class="fas fa-star"></i> View Recommended Jobs</a>
                            <a href="index.php" class="btn btn-outline btn-large"><i class="fas fa-search"></i> Browse All Jobs</a>
                        <?php else: ?>
                            <a href="post-job.php" class="btn btn-primary btn-large"><i class="fas fa-plus"></i> Post Your First Job</a>
                            <a href="dashboard-employer.php" class="btn btn-outline btn-large"><i class="fas fa-tachometer-alt"></i> Go to Dashboard</a>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="complete">
                        <button type="submit" class="btn btn-secondary btn-small">Get Started <i class="fas fa-arrow-right"></i></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Photo preview
document.getElementById('profile_picture')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
            document.getElementById('photo-preview').style.border = '2px solid var(--primary-blue)';
        };
        reader.readAsDataURL(file);
    }
});

// Bio character counter
document.getElementById('bio')?.addEventListener('input', function() {
    document.getElementById('bio-count').textContent = this.value.length;
});
</script>

<?php require_once 'includes/footer.php'; ?>
