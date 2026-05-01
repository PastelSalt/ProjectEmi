<?php
/**
 * Sign Up Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
require_once 'config/config.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    }

    $mobile_number = normalizePhilippineMobile(sanitizeInput($_POST['mobile_number'] ?? ''));
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = sanitizeInput($_POST['user_type'] ?? '');
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $region = sanitizeInput($_POST['region'] ?? '');
    $province = sanitizeInput($_POST['province'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $skills = (isset($_POST['skills']) && is_array($_POST['skills'])) ? $_POST['skills'] : [];
    
    if (!empty($error)) {
        // Preserve CSRF errors set above.
    } elseif (empty($mobile_number) || empty($password) || empty($user_type) || empty($full_name) || empty($region) || empty($province) || empty($city)) {
        $error = 'Please fill in all required fields.';
    } elseif (!isValidPhilippineMobile($mobile_number)) {
        $error = 'Please enter a valid Philippine mobile number.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!isValidRegionCode($region)) {
        $error = 'Invalid region selected.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $error = 'Password must contain at least one letter and one number.';
    } elseif (!in_array($user_type, ['worker', 'employer'], true)) {
        $error = 'Invalid user type selected.';
    } elseif (strlen($full_name) > 100 || strlen($province) > 100 || strlen($city) > 100) {
        $error = 'One or more fields exceed the allowed length.';
    } else {
        $conn = getDBConnection();
        
        $checkSql = "SELECT user_id FROM users WHERE mobile_number = ? OR (email IS NOT NULL AND email != '' AND email = ?)";
        $existing = fetchOne($conn, $checkSql, [$mobile_number, $email], 'ss');
        
        if ($existing) {
            $error = 'This mobile number is already registered.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $insertSql = "INSERT INTO users (mobile_number, email, password_hash, user_type, full_name, region, province, city) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = executeQuery($conn, $insertSql, 
                [$mobile_number, $email, $password_hash, $user_type, $full_name, $region, $province, $city],
                'ssssssss'
            );
            
            if ($stmt) {
                $user_id = $conn->insert_id;
                
                if ($user_type == 'worker' && !empty($skills)) {
                    foreach ($skills as $skill) {
                        $skill = sanitizeInput($skill);
                        if (!empty($skill) && strlen($skill) <= 100) {
                            $skillSql = "INSERT INTO user_skills (user_id, skill_name) VALUES (?, ?)";
                            executeQuery($conn, $skillSql, [$user_id, $skill], 'is');
                        }
                    }
                }
                
                $_SESSION['flash_success'] = 'Account created successfully! Please login to continue.';
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="rg site-modern auth-page">
    <div class="sakura-bg"></div>
    <div class="auth-container auth-modern">
        <div class="auth-shell auth-shell-signup">
            <aside class="auth-showcase">
                <div class="auth-showcase-brand">
                    <span class="logo-mark">R</span>
                    <span>Raket<span class="brand-accent">Go</span></span>
                </div>
                <h2>Build your profile and get discovered.</h2>
                <p>Create your account in minutes, complete your location and skills, and start connecting with real employers.</p>
                <ul class="auth-points">
                    <li><i class="fas fa-check-circle"></i> Worker and employer accounts in one flow</li>
                    <li><i class="fas fa-check-circle"></i> Region-based matching for faster opportunities</li>
                    <li><i class="fas fa-check-circle"></i> Skill-driven recommendations after signup</li>
                </ul>
                <a href="index.php" class="auth-back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </aside>

            <div class="auth-card auth-card-modern auth-card-wide">
                <div class="auth-band"><i class="fas fa-user-plus"></i> Create Account</div>

                <div class="auth-header auth-header-modern">
                    <h1>Join <?php echo htmlspecialchars(SITE_NAME); ?></h1>
                    <p>Set up your account details to start using the platform.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" data-validate>
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label for="user_type"><i class="fas fa-user-tag"></i> I am a *</label>
                        <select id="user_type" name="user_type" class="form-control" required>
                            <option value="">Select account type</option>
                            <option value="worker" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'worker') ? 'selected' : ''; ?>>Worker</option>
                            <option value="employer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'employer') ? 'selected' : ''; ?>>Employer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Juan Dela Cruz" required
                            value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="mobile_number"><i class="fas fa-mobile-alt"></i> Mobile Number *</label>
                            <input type="text" id="mobile_number" name="mobile_number" class="form-control" placeholder="09XXXXXXXXX" required
                                value="<?php echo isset($_POST['mobile_number']) ? htmlspecialchars($_POST['mobile_number']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email (Optional)</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="juan@example.com"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="grid grid-3">
                        <div class="form-group">
                            <label for="region"><i class="fas fa-map"></i> Region *</label>
                            <select id="region" name="region" class="form-control" required>
                                <option value="">Select region</option>
                                <?php foreach ($PHILIPPINES_REGIONS as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo (isset($_POST['region']) && $_POST['region'] == $code) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="province"><i class="fas fa-map-marker"></i> Province *</label>
                            <input type="text" id="province" name="province" class="form-control" placeholder="Province" required
                                value="<?php echo isset($_POST['province']) ? htmlspecialchars($_POST['province']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="city"><i class="fas fa-city"></i> City *</label>
                            <input type="text" id="city" name="city" class="form-control" placeholder="City" required
                                value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group" id="skills-section" style="display: none;">
                        <label for="skills"><i class="fas fa-tags"></i> Skills (for Workers)</label>
                        <div class="skills-tags mb-2"></div>
                        <input type="text" id="skills-input" class="form-control" placeholder="Type a skill and press Enter">
                        <small class="text-muted auth-inline-note">Press Enter or comma to add multiple skills</small>
                        <div id="skills-hidden"></div>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password *</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="auth-footer auth-footer-modern">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <p class="auth-footer-secondary"><a href="index.php">Browse jobs first</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            const skillsSection = document.getElementById('skills-section');
            if (this.value === 'worker') {
                skillsSection.style.display = 'block';
            } else {
                skillsSection.style.display = 'none';
            }
        });

        if (document.getElementById('user_type').value === 'worker') {
            document.getElementById('skills-section').style.display = 'block';
        }
        
        const skillsInput = document.getElementById('skills-input');
        if (skillsInput) {
            skillsInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const skill = this.value.trim().replace(',', '');
                    if (skill) {
                        addSkillTag(skill);
                        this.value = '';
                    }
                }
            });
        }
        
        function addSkillTag(skill) {
            const container = document.querySelector('.skills-tags');
            const hiddenContainer = document.getElementById('skills-hidden');

            const existingHidden = Array.from(hiddenContainer.querySelectorAll('input[name="skills[]"]'))
                .find(function(input) {
                    return input.value === skill;
                });

            if (existingHidden) {
                return;
            }
            
            const tag = document.createElement('span');
            tag.className = 'tag tag-pink skill-tag-chip';
            tag.innerHTML = skill + ' <i class="fas fa-times" style="cursor: pointer; margin-left: 4px;"></i>';
            tag.querySelector('i').addEventListener('click', function() {
                tag.remove();
                const hidden = Array.from(hiddenContainer.querySelectorAll('input[name="skills[]"]'))
                    .find(function(input) {
                        return input.value === skill;
                    });
                if (hidden) {
                    hidden.remove();
                }
            });
            container.appendChild(tag);
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'skills[]';
            hiddenInput.value = skill;
            hiddenContainer.appendChild(hiddenInput);
        }
    </script>
</body>
</html>
