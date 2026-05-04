<?php
/**
 * Authentication and Authorization Helper Class
 * Standardizes auth checks and user management
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */

class AuthHelper {
    
    /**
     * Require user to be logged in
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            self::redirect('login.php');
        }
    }
    
    /**
     * Require specific user type
     */
    public static function requireUserType($type) {
        self::requireLogin();
        if (self::getCurrentUserType() !== $type) {
            self::redirect('index.php');
        }
    }
    
    /**
     * Require one of multiple user types
     */
    public static function requireUserTypes($types) {
        self::requireLogin();
        if (!in_array(self::getCurrentUserType(), $types)) {
            self::redirect('index.php');
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user type
     */
    public static function getCurrentUserType() {
        return $_SESSION['user_type'] ?? null;
    }
    
    /**
     * Get current user data
     */
    public static function getCurrentUser($fields = '*') {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        static $userCache = [];
        $userId = self::getCurrentUserId();
        $cacheKey = $userId . '_' . $fields;
        
        if (!isset($userCache[$cacheKey])) {
            $conn = getDBConnection();
            $sql = "SELECT $fields FROM users WHERE user_id = ? AND account_status = 'active'";
            $userCache[$cacheKey] = fetchOne($conn, $sql, [$userId], 'i');
        }
        
        return $userCache[$cacheKey];
    }
    
    /**
     * Check if current user can access resource
     */
    public static function canAccessResource($resourceType, $resourceId) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $userId = self::getCurrentUserId();
        $userType = self::getCurrentUserType();
        
        switch ($resourceType) {
            case 'job':
                return self::canAccessJob($resourceId, $userId, $userType);
            case 'profile':
                return self::canAccessProfile($resourceId, $userId, $userType);
            case 'application':
                return self::canAccessApplication($resourceId, $userId, $userType);
            case 'message':
                return self::canAccessMessage($resourceId, $userId, $userType);
            default:
                return false;
        }
    }
    
    /**
     * Check if user can access job
     */
    private static function canAccessJob($jobId, $userId, $userType) {
        $conn = getDBConnection();
        
        if ($userType === 'admin') {
            return true;
        }
        
        if ($userType === 'employer') {
            // Check if user owns the job
            $sql = "SELECT job_id FROM job_posts WHERE job_id = ? AND employer_id = ?";
            return fetchOne($conn, $sql, [$jobId, $userId], 'ii') !== false;
        }
        
        if ($userType === 'worker') {
            // Workers can view all active jobs
            $sql = "SELECT job_id FROM job_posts WHERE job_id = ? AND job_status = 'active'";
            return fetchOne($conn, $sql, [$jobId], 'i') !== false;
        }
        
        return false;
    }
    
    /**
     * Check if user can access profile
     */
    private static function canAccessProfile($profileUserId, $userId, $userType) {
        // Users can always access their own profile
        if ($profileUserId == $userId) {
            return true;
        }
        
        // Admin can access any profile
        if ($userType === 'admin') {
            return true;
        }
        
        // Other users can access public profiles
        $conn = getDBConnection();
        $sql = "SELECT user_id FROM users WHERE user_id = ? AND account_status = 'active'";
        return fetchOne($conn, $sql, [$profileUserId], 'i') !== false;
    }
    
    /**
     * Check if user can access application
     */
    private static function canAccessApplication($applicationId, $userId, $userType) {
        $conn = getDBConnection();
        
        if ($userType === 'admin') {
            return true;
        }
        
        if ($userType === 'worker') {
            // Check if application belongs to worker
            $sql = "SELECT application_id FROM job_applications WHERE application_id = ? AND worker_id = ?";
            return fetchOne($conn, $sql, [$applicationId, $userId], 'ii') !== false;
        }
        
        if ($userType === 'employer') {
            // Check if application is for employer's job
            $sql = "SELECT ja.application_id 
                    FROM job_applications ja 
                    JOIN job_posts j ON ja.job_id = j.job_id 
                    WHERE ja.application_id = ? AND j.employer_id = ?";
            return fetchOne($conn, $sql, [$applicationId, $userId], 'ii') !== false;
        }
        
        return false;
    }
    
    /**
     * Check if user can access message
     */
    private static function canAccessMessage($messageId, $userId, $userType) {
        $conn = getDBConnection();
        
        if ($userType === 'admin') {
            return true;
        }
        
        // Check if user is sender or receiver
        $sql = "SELECT message_id FROM messages WHERE message_id = ? AND (sender_id = ? OR receiver_id = ?)";
        return fetchOne($conn, $sql, [$messageId, $userId, $userId], 'iii') !== false;
    }
    
    /**
     * Enforce active session user
     */
    public static function enforceActiveSessionUser() {
        if (!self::isLoggedIn()) {
            return;
        }
        
        $userId = self::getCurrentUserId();
        $conn = getDBConnection();
        
        $sql = "SELECT account_status FROM users WHERE user_id = ?";
        $user = fetchOne($conn, $sql, [$userId], 'i');
        
        if (!$user || $user['account_status'] !== 'active') {
            // Destroy session and redirect
            session_destroy();
            self::redirect('login.php');
        }
    }
    
    /**
     * Get user permissions
     */
    public static function getUserPermissions($userId = null) {
        if ($userId === null) {
            $userId = self::getCurrentUserId();
        }
        
        $userType = self::getCurrentUserType();
        
        $permissions = [
            'view_jobs' => false,
            'post_jobs' => false,
            'apply_jobs' => false,
            'manage_applications' => false,
            'view_profiles' => false,
            'manage_users' => false,
            'view_analytics' => false,
            'send_messages' => false,
            'create_posts' => false,
            'rate_users' => false
        ];
        
        switch ($userType) {
            case 'worker':
                $permissions = [
                    'view_jobs' => true,
                    'apply_jobs' => true,
                    'view_profiles' => true,
                    'send_messages' => true,
                    'create_posts' => true,
                    'rate_users' => true
                ];
                break;
            case 'employer':
                $permissions = [
                    'view_jobs' => true,
                    'post_jobs' => true,
                    'manage_applications' => true,
                    'view_profiles' => true,
                    'send_messages' => true,
                    'create_posts' => true,
                    'rate_users' => true
                ];
                break;
            case 'admin':
                $permissions = [
                    'view_jobs' => true,
                    'post_jobs' => true,
                    'apply_jobs' => true,
                    'manage_applications' => true,
                    'view_profiles' => true,
                    'manage_users' => true,
                    'view_analytics' => true,
                    'send_messages' => true,
                    'create_posts' => true,
                    'rate_users' => true
                ];
                break;
        }
        
        return $permissions;
    }
    
    /**
     * Check if user has specific permission
     */
    public static function hasPermission($permission, $userId = null) {
        $permissions = self::getUserPermissions($userId);
        return $permissions[$permission] ?? false;
    }
    
    /**
     * Require specific permission
     */
    public static function requirePermission($permission) {
        if (!self::hasPermission($permission)) {
            self::redirect('index.php');
        }
    }
    
    /**
     * Login user
     */
    public static function login($userId, $userType, $remember = false) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_type'] = $userType;
        $_SESSION['login_time'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            setcookie('remember_token', $token, $expires, '/', '', false, true);
            
            // Store token in database (you'd need to implement this)
            // $conn = getDBConnection();
            // executeQuery($conn, "INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)", [$userId, $token, date('Y-m-d H:i:s', $expires)], 'iss');
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            
            // Remove token from database
            // $conn = getDBConnection();
            // executeQuery($conn, "DELETE FROM remember_tokens WHERE token = ?", [$_COOKIE['remember_token']], 's');
        }
        
        // Destroy session
        session_destroy();
        
        // Start new session
        session_start();
    }
    
    /**
     * Redirect to URL
     */
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    /**
     * Get login URL with return parameter
     */
    public static function getLoginUrl($returnUrl = null) {
        $loginUrl = 'login.php';
        if ($returnUrl) {
            $loginUrl .= '?return=' . urlencode($returnUrl);
        }
        return $loginUrl;
    }
    
    /**
     * Check login rate limit
     */
    public static function checkLoginRateLimit($mobile) {
        $conn = getDBConnection();
        
        // Clean old attempts (older than 15 minutes)
        $cleanupSql = "DELETE FROM auth_rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        executeQuery($conn, $cleanupSql);
        
        // Check recent attempts
        $checkSql = "SELECT COUNT(*) as attempt_count FROM auth_rate_limits WHERE mobile_number = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $result = fetchOne($conn, $checkSql, [$mobile], 's');
        
        if ($result && $result['attempt_count'] >= 6) {
            return false; // Rate limited
        }
        
        return true; // Allowed
    }
    
    /**
     * Record login attempt
     */
    public static function recordLoginAttempt($mobile, $success) {
        $conn = getDBConnection();
        
        $sql = "INSERT INTO auth_rate_limits (mobile_number, attempt_success) VALUES (?, ?)";
        executeQuery($conn, $sql, [$mobile, $success ? 1 : 0], 'si');
    }
}
