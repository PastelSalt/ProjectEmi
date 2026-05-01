<?php
/**
 * Login Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
require_once 'config/config.php';

$error = '';
$success = '';

if (!empty($_SESSION['flash_success'])) {
    $success = sanitizeInput($_SESSION['flash_success']);
    unset($_SESSION['flash_success']);
}

// If already logged in, redirect to appropriate dashboard or onboarding
if (isLoggedIn()) {
    $userType = getCurrentUserType();
    $conn = getDBConnection();
    $user = fetchOne($conn, "SELECT onboarding_completed FROM users WHERE user_id = ?", [getCurrentUserId()], 'i');
    closeDBConnection($conn);
    
    // Redirect to onboarding if not completed (for workers and employers)
    if ($user && !$user['onboarding_completed'] && in_array($userType, ['worker', 'employer'])) {
        redirect('onboarding.php');
    }
    
    if ($userType == 'worker') {
        redirect('dashboard-worker.php');
    } elseif ($userType == 'employer') {
        redirect('dashboard-employer.php');
    } elseif ($userType == 'admin') {
        redirect('dashboard-admin.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    }

    $mobile_number = normalizePhilippineMobile(sanitizeInput($_POST['mobile_number'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    if (empty($error) && (empty($mobile_number) || empty($password))) {
        $error = 'Please enter both mobile number and password.';
    } elseif (empty($error) && !isValidPhilippineMobile($mobile_number)) {
        $error = 'Please enter a valid Philippine mobile number.';
    } elseif (empty($error)) {
        $conn = getDBConnection();

        $retryAfter = 0;
        if (isRateLimitExceeded($conn, 'login', $mobile_number, 6, 900, 900, $retryAfter)) {
            $minutes = max(1, (int)ceil($retryAfter / 60));
            $error = 'Too many failed login attempts. Please try again in about ' . $minutes . ' minute(s).';
            closeDBConnection($conn);
        } else {
            $sql = "SELECT user_id, password_hash, user_type, full_name, account_status 
                    FROM users 
                    WHERE mobile_number = ?";
            $user = fetchOne($conn, $sql, [$mobile_number], 's');
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['account_status'] !== 'active') {
                    $error = 'Your account has been suspended or deleted.';
                } else {
                    clearRateLimit($conn, 'login', $mobile_number);
                    session_regenerate_id(true);
                    regenerateCsrfToken();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['full_name'] = $user['full_name'];
                    
                    $updateSql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                    executeQuery($conn, $updateSql, [$user['user_id']], 'i');
                    
                    // Check if onboarding is completed
                    if (!$user['onboarding_completed'] && in_array($user['user_type'], ['worker', 'employer'])) {
                        closeDBConnection($conn);
                        redirect('onboarding.php');
                    }
                    
                    closeDBConnection($conn);
                    if ($user['user_type'] == 'worker') {
                        redirect('dashboard-worker.php');
                    } elseif ($user['user_type'] == 'employer') {
                        redirect('dashboard-employer.php');
                    } elseif ($user['user_type'] == 'admin') {
                        redirect('dashboard-admin.php');
                    }
                }
            } else {
                registerRateLimitFailure($conn, 'login', $mobile_number, 6, 900, 900);
                $error = 'Invalid mobile number or password.';
            }
        
            closeDBConnection($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="rg site-modern auth-page">
    <div class="sakura-bg"></div>
    <div class="auth-container auth-modern">
        <div class="auth-shell">
            <aside class="auth-showcase">
                <div class="auth-showcase-brand">
                    <span class="logo-mark">R</span>
                    <span>Raket<span class="brand-accent">Go</span></span>
                </div>
                <h2>Welcome back to your raket flow.</h2>
                <p>Login to continue applying, messaging employers, and tracking every opportunity in one clean workspace.</p>
                <ul class="auth-points">
                    <li><i class="fas fa-check-circle"></i> Direct job matching and recommendations</li>
                    <li><i class="fas fa-check-circle"></i> Real-time notifications and messages</li>
                    <li><i class="fas fa-check-circle"></i> Secure account session and activity history</li>
                </ul>
                <a href="index.php" class="auth-back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </aside>

            <div class="auth-card auth-card-modern">
                <div class="auth-band"><i class="fas fa-shield-alt"></i> Secure Login</div>

                <div class="auth-header auth-header-modern">
                    <h1>Login to your account</h1>
                    <p>Use your registered mobile number and password.</p>
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
                        <label for="mobile_number">
                            <i class="fas fa-mobile-alt"></i> Mobile Number
                        </label>
                        <input 
                            type="text" 
                            id="mobile_number" 
                            name="mobile_number" 
                            class="form-control" 
                            placeholder="09XXXXXXXXX" 
                            required
                            value="<?php echo isset($_POST['mobile_number']) ? htmlspecialchars($_POST['mobile_number']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Enter your password" 
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="auth-footer auth-footer-modern">
                    <p>Don't have an account? <a href="signup.php">Create one now</a></p>
                    <p class="auth-footer-secondary"><a href="index.php">Browse jobs first</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
