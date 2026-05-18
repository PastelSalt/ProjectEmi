<?php
/**
 * General Configuration File
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */

// Disable PCRE JIT to prevent memory allocation warnings
ini_set('pcre.jit', '0');

// Check required extensions
if (!extension_loaded('mbstring')) {
    die('The mbstring PHP extension is required. Please install and enable it.');
}

// Environment detection (local by default on localhost; production otherwise)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalHost = (stripos($host, 'localhost') === 0 || stripos($host, '127.0.0.1') === 0);
$appEnv = strtolower((string) (getenv('APP_ENV') ?: ($isLocalHost ? 'local' : 'production')));
if (!in_array($appEnv, ['local', 'development', 'staging', 'production'], true)) {
    $appEnv = 'production';
}

define('APP_ENV', $appEnv);
define('APP_DEBUG', in_array(APP_ENV, ['local', 'development'], true));

// Detect HTTPS for secure cookies and HSTS
$isHttpsRequest = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) ||
    (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
);

// Secure session defaults
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttpsRequest ? '1' : '0');

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $isHttpsRequest,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=Lax', '', $isHttpsRequest, true);
    }

    session_name('raketgo_session');
    session_start();
}

// Site configuration
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDirectory = ($scriptDirectory === '/' || $scriptDirectory === '.') ? '' : rtrim($scriptDirectory, '/');
$computedSiteUrl = ($isHttpsRequest ? 'https://' : 'http://') . $host . $scriptDirectory;

define('SITE_NAME', 'RaketGo');
define('SITE_URL', getenv('SITE_URL') ?: $computedSiteUrl);
define('SITE_AUTHOR', 'Moesoft');
define('BASE_PATH', __DIR__ . '/../');

// Upload directories
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
define('PROFILE_PICS_DIR', UPLOAD_DIR . 'profiles/');
define('JOB_IMAGES_DIR', UPLOAD_DIR . 'jobs/');
define('DOCUMENTS_DIR', UPLOAD_DIR . 'documents/');
define('POST_IMAGES_DIR', UPLOAD_DIR . 'posts/');
define('RESUMES_DIR', UPLOAD_DIR . 'resumes/');
define('PORTFOLIO_IMAGES_DIR', UPLOAD_DIR . 'portfolio/');
define('COMPANY_LOGOS_DIR', UPLOAD_DIR . 'company_logos/');

// Create upload directories if they don't exist
$dirs = [UPLOAD_DIR, PROFILE_PICS_DIR, JOB_IMAGES_DIR, DOCUMENTS_DIR, POST_IMAGES_DIR, RESUMES_DIR, PORTFOLIO_IMAGES_DIR, COMPANY_LOGOS_DIR];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');

// Timezone
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Manila');

// Basic security headers
function sendSecurityHeaders() {
    if (headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('X-XSS-Protection: 0');
    header('Cross-Origin-Opener-Policy: same-origin');

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) ||
        (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
    );
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

sendSecurityHeaders();

// Job Types Configuration with metadata and validation rules
$JOB_TYPES_CONFIG = [
    'full_time' => [
        'label' => 'Full Time',
        'icon' => 'fa-briefcase',
        'color' => 'blue',
        'suggested_pay_types' => ['monthly', 'fixed'],
        'default_pay_type' => 'monthly',
        'min_duration_days' => 30,
        'description' => 'Long-term employment with regular hours'
    ],
    'part_time' => [
        'label' => 'Part Time',
        'icon' => 'fa-clock',
        'color' => 'pink',
        'suggested_pay_types' => ['hourly', 'daily', 'monthly'],
        'default_pay_type' => 'hourly',
        'min_duration_days' => 7,
        'description' => 'Flexible hours, less than full-time'
    ],
    'contractual' => [
        'label' => 'Contractual',
        'icon' => 'fa-file-contract',
        'color' => 'orange',
        'suggested_pay_types' => ['fixed', 'monthly'],
        'default_pay_type' => 'fixed',
        'min_duration_days' => 1,
        'description' => 'Project-based work with defined deliverables'
    ],
    'one_time' => [
        'label' => 'One-time / Gig',
        'icon' => 'fa-bolt',
        'color' => 'green',
        'suggested_pay_types' => ['fixed'],
        'default_pay_type' => 'fixed',
        'min_duration_days' => 1,
        'max_duration_days' => 7,
        'description' => 'Single task or short-term gig'
    ],
    'internship' => [
        'label' => 'Internship',
        'icon' => 'fa-graduation-cap',
        'color' => 'purple',
        'suggested_pay_types' => ['fixed', 'monthly', 'hourly'],
        'default_pay_type' => 'monthly',
        'min_duration_days' => 30,
        'description' => 'Learning opportunity for students/entry-level'
    ]
];

$ALLOWED_JOB_TYPES = array_keys($JOB_TYPES_CONFIG);

// Pay Types Configuration
$PAY_TYPES_CONFIG = [
    'fixed' => [
        'label' => 'Fixed Price',
        'icon' => 'fa-money-bill-wave',
        'description' => 'One-time payment for complete job'
    ],
    'hourly' => [
        'label' => 'Per Hour',
        'icon' => 'fa-clock',
        'description' => 'Payment based on hours worked'
    ],
    'daily' => [
        'label' => 'Per Day',
        'icon' => 'fa-calendar-day',
        'description' => 'Daily rate payment'
    ],
    'monthly' => [
        'label' => 'Per Month',
        'icon' => 'fa-calendar-alt',
        'description' => 'Monthly salary payment'
    ]
];

// Employer Subtypes Configuration
// Distinguishes between businesses and individual employers
$EMPLOYER_SUBTYPES = [
    'company' => [
        'label' => 'Company / Business',
        'icon' => 'fa-building',
        'color' => 'blue',
        'description' => 'Registered businesses, companies, and organizations hiring for various roles',
        'examples' => ['Construction Company', 'Retail Store', 'Tech Startup', 'Restaurant']
    ],
    'individual' => [
        'label' => 'Individual / Personal',
        'icon' => 'fa-user',
        'color' => 'green',
        'description' => 'Individuals who need help with personal tasks, one-time jobs, or home services',
        'examples' => ['Homeowner needs plumbing', 'Event organizer', 'Personal assistant needed', 'Moving help']
    ]
];

// Helper function to get employer subtype info
function getEmployerSubtypeInfo($subtype) {
    global $EMPLOYER_SUBTYPES;
    return $EMPLOYER_SUBTYPES[$subtype] ?? null;
}

// Helper function to get employer subtype label
function getEmployerSubtypeLabel($subtype) {
    $info = getEmployerSubtypeInfo($subtype);
    return $info ? $info['label'] : 'Employer';
}

$ALLOWED_PAY_TYPES = array_keys($PAY_TYPES_CONFIG);

// Helper function to validate job type and pay type combination
function isValidJobPayCombination($jobType, $payType) {
    global $JOB_TYPES_CONFIG;
    if (!isset($JOB_TYPES_CONFIG[$jobType])) {
        return false;
    }
    return in_array($payType, $JOB_TYPES_CONFIG[$jobType]['suggested_pay_types'], true);
}

// Helper function to get job type display info
function getJobTypeInfo($jobType) {
    global $JOB_TYPES_CONFIG;
    return $JOB_TYPES_CONFIG[$jobType] ?? null;
}

// Helper function to get pay type info
function getPayTypeInfo($payType) {
    global $PAY_TYPES_CONFIG;
    return $PAY_TYPES_CONFIG[$payType] ?? null;
}

// Helper function to validate job duration based on job type
function validateJobDuration($jobType, $startDate, $endDate) {
    global $JOB_TYPES_CONFIG;

    if (!isset($JOB_TYPES_CONFIG[$jobType])) {
        return ['valid' => false, 'error' => 'Invalid job type'];
    }

    $config = $JOB_TYPES_CONFIG[$jobType];

    // If no dates provided, skip duration validation
    if (empty($startDate) || empty($endDate)) {
        return ['valid' => true];
    }

    $start = strtotime($startDate);
    $end = strtotime($endDate);
    $durationDays = ($end - $start) / (60 * 60 * 24);

    if (isset($config['min_duration_days']) && $durationDays < $config['min_duration_days']) {
        return [
            'valid' => false,
            'error' => sprintf(
                '%s positions typically require at least %d days',
                $config['label'],
                $config['min_duration_days']
            )
        ];
    }

    if (isset($config['max_duration_days']) && $durationDays > $config['max_duration_days']) {
        return [
            'valid' => false,
            'error' => sprintf(
                '%s positions typically should not exceed %d days',
                $config['label'],
                $config['max_duration_days']
            )
        ];
    }

    return ['valid' => true];
}

// ============================================================================
// RAKETGO MATCHSCORE™ RECOMMENDATION ALGORITHM
// ============================================================================

// Algorithm weights configuration
$RECOMMENDER_WEIGHTS = [
    'skill_match' => 35,           // Direct skill matching (0-35 points)
    'behavioral' => 25,             // User's viewing/applying patterns (0-25 points)
    'collaborative' => 15,          // Similar users' preferences (0-15 points)
    'trust_compatibility' => 10,  // Trust score alignment (0-10 points)
    'location_proximity' => 10,     // Geographic proximity (0-10 points)
    'compensation_fit' => 5,       // Pay rate appropriateness (0-5 points)
    'job_type_preference' => 5,    // Job type alignment (0-5 points)
    'recency' => 3,                // Job freshness (0-3 points)
    'diversity_boost' => 2         // Novelty factor (0-2 points)
];

// Skill relevance levels for fuzzy matching
$SKILL_RELEVANCE_LEVELS = [
    'exact' => 1.0,        // Exact match
    'synonym' => 0.8,      // Known synonym (e.g., "Carpentry" ≈ "Woodworking")
    'related' => 0.5,      // Related skill (e.g., "JavaScript" ≈ "React")
    'category' => 0.3,     // Same category (e.g., both are programming languages)
    'none' => 0            // No relation
];

// Common skill synonyms/categories for matching
$SKILL_SYNONYMS = [
    'carpentry' => ['woodworking', 'furniture making', 'cabinet making'],
    'welding' => ['metalwork', 'fabrication', 'ironwork'],
    'plumbing' => ['pipe fitting', 'sanitation'],
    'electrical' => ['electrician', 'wiring', 'electrical work'],
    'javascript' => ['js', 'react', 'vue', 'angular', 'node.js'],
    'python' => ['django', 'flask', 'data science'],
    'photoshop' => ['photo editing', 'graphic design', 'adobe'],
    'excel' => ['spreadsheets', 'data entry', 'microsoft office'],
    'driving' => ['driver', 'chauffeur', 'delivery'],
    'cooking' => ['chef', 'kitchen', 'food preparation', 'culinary'],
    'cleaning' => ['housekeeping', 'janitorial', 'maintenance'],
    'gardening' => ['landscaping', 'lawn care', 'horticulture']
];

/**
 * Calculate skill match score using fuzzy matching
 * Returns score 0-35
 */
function calculateSkillAffinityScore($userSkills, $jobRequiredSkills, $jobPreferredSkills = '') {
    global $SKILL_SYNONYMS, $SKILL_RELEVANCE_LEVELS;

    if (empty($userSkills) || (empty($jobRequiredSkills) && empty($jobPreferredSkills))) {
        return 0;
    }

    $userSkillsLower = array_map('strtolower', $userSkills);
    $requiredSkills = array_filter(array_map('trim', explode(',', strtolower($jobRequiredSkills))));
    $preferredSkills = array_filter(array_map('trim', explode(',', strtolower($jobPreferredSkills))));

    $totalWeight = 0;
    $matchedWeight = 0;

    // Required skills have 2x weight
    foreach ($requiredSkills as $reqSkill) {
        $totalWeight += 2;
        $bestMatch = 0;

        foreach ($userSkillsLower as $userSkill) {
            $matchScore = calculateSkillSimilarity($userSkill, $reqSkill);
            $bestMatch = max($bestMatch, $matchScore);
        }

        $matchedWeight += 2 * $bestMatch;
    }

    // Preferred skills have 1x weight
    foreach ($preferredSkills as $prefSkill) {
        $totalWeight += 1;
        $bestMatch = 0;

        foreach ($userSkillsLower as $userSkill) {
            $matchScore = calculateSkillSimilarity($userSkill, $prefSkill);
            $bestMatch = max($bestMatch, $matchScore);
        }

        $matchedWeight += 1 * $bestMatch;
    }

    if ($totalWeight == 0) return 0;

    // Scale to 0-35
    $coverage = $matchedWeight / $totalWeight;
    return round(35 * min(1, $coverage));
}

/**
 * Calculate similarity between two skills (0-1)
 */
function calculateSkillSimilarity($skill1, $skill2) {
    global $SKILL_SYNONYMS, $SKILL_RELEVANCE_LEVELS;

    // Exact match
    if ($skill1 === $skill2) {
        return $SKILL_RELEVANCE_LEVELS['exact'];
    }

    // Check synonyms
    if (isset($SKILL_SYNONYMS[$skill1])) {
        if (in_array($skill2, $SKILL_SYNONYMS[$skill1])) {
            return $SKILL_RELEVANCE_LEVELS['synonym'];
        }
    }
    if (isset($SKILL_SYNONYMS[$skill2])) {
        if (in_array($skill1, $SKILL_SYNONYMS[$skill2])) {
            return $SKILL_RELEVANCE_LEVELS['synonym'];
        }
    }

    // Partial match (substring)
    if (strpos($skill1, $skill2) !== false || strpos($skill2, $skill1) !== false) {
        return $SKILL_RELEVANCE_LEVELS['related'];
    }

    // Levenshtein distance for typos/variations
    $distance = levenshtein($skill1, $skill2);
    $maxLen = max(strlen($skill1), strlen($skill2));
    if ($maxLen > 0 && $distance / $maxLen < 0.3) {
        return $SKILL_RELEVANCE_LEVELS['related'];
    }

    return $SKILL_RELEVANCE_LEVELS['none'];
}

/**
 * Calculate behavioral score based on user's interaction history
 * Returns score 0-25
 */
function calculateBehavioralScore($conn, $userId, $jobId, $jobCategory, $jobType, $employerId) {
    $score = 0;

    // Check if user viewed this job before
    $viewed = fetchOne($conn,
        "SELECT COUNT(*) as count FROM user_interactions WHERE user_id = ? AND job_id = ? AND interaction_type = 'view'",
        [$userId, $jobId], 'ii'
    );
    if ($viewed && $viewed['count'] > 0) {
        $score += 5; // User showed interest
    }

    // Check if user saved similar jobs
    $savedSimilar = fetchOne($conn,
        "SELECT COUNT(*) as count FROM user_interactions ui
         JOIN job_posts j ON ui.job_id = j.job_id
         WHERE ui.user_id = ? AND ui.interaction_type = 'save'
         AND (j.job_category = ? OR j.job_type = ?)",
        [$userId, $jobCategory, $jobType], 'iss'
    );
    if ($savedSimilar && $savedSimilar['count'] > 0) {
        $score += 8; // User saves similar jobs
    }

    // Check if user applied to jobs from this employer before
    $appliedToEmployer = fetchOne($conn,
        "SELECT COUNT(*) as count FROM job_applications WHERE worker_id = ? AND employer_id = ?",
        [$userId, $employerId], 'ii'
    );
    if ($appliedToEmployer && $appliedToEmployer['count'] > 0) {
        $score += 7; // Past relationship with employer
    }

    // Check messaging history with this employer
    $messagedEmployer = fetchOne($conn,
        "SELECT COUNT(*) as count FROM messages
         WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)",
        [$userId, $employerId, $employerId, $userId], 'iiii'
    );
    if ($messagedEmployer && $messagedEmployer['count'] > 0) {
        $score += 5; // Active communication
    }

    return min(25, $score);
}

/**
 * Calculate collaborative filtering score
 * Returns score 0-15
 */
function calculateCollaborativeScore($conn, $userId, $jobId) {
    // Find users with similar skills who liked this job
    $similarUsersLiked = fetchOne($conn,
        "SELECT COUNT(DISTINCT ui.user_id) as count
         FROM user_interactions ui
         JOIN user_skills us1 ON ui.user_id = us1.user_id
         JOIN user_skills us2 ON us1.skill_name = us2.skill_name AND us2.user_id = ?
         WHERE ui.job_id = ? AND ui.interaction_type IN ('save', 'view')
         AND ui.user_id != ?",
        [$userId, $jobId, $userId], 'iii'
    );

    if ($similarUsersLiked && $similarUsersLiked['count'] > 0) {
        // Scale: 1 similar user = 3 points, max 15
        return min(15, $similarUsersLiked['count'] * 3);
    }

    return 0;
}

/**
 * Calculate trust compatibility score
 * Returns score 0-10
 */
function calculateTrustCompatibilityScore($userTrustScore, $employerTrustScore, $jobSlotsAvailable) {
    $score = 0;

    // Base score from user's trust (0-5)
    $score += min(5, $userTrustScore / 2);

    // Employer trust bonus (0-3)
    if ($employerTrustScore > 4.0) {
        $score += 3;
    } elseif ($employerTrustScore > 3.0) {
        $score += 2;
    } elseif ($employerTrustScore > 2.0) {
        $score += 1;
    }

    // Urgency bonus - fewer slots = more competitive (0-2)
    if ($jobSlotsAvailable == 1) {
        $score += 2; // Very competitive
    } elseif ($jobSlotsAvailable <= 3) {
        $score += 1;
    }

    return min(10, $score);
}

/**
 * Calculate location proximity score
 * Returns score 0-10
 */
function calculateLocationProximityScore($userRegion, $userProvince, $userCity, $jobRegion, $jobProvince, $jobCity) {
    if ($userCity === $jobCity) {
        return 10; // Same city - perfect match
    }
    if ($userProvince === $jobProvince) {
        return 7; // Same province
    }
    if ($userRegion === $jobRegion) {
        return 4; // Same region
    }

    // Adjacent regions (simplified - could be expanded with actual adjacency data)
    $adjacentRegions = [
        'NCR' => ['Region IV-A', 'Region III'],
        'Region IV-A' => ['NCR', 'Region IV-B', 'Region V'],
        'Region III' => ['NCR', 'Region II', 'Region I'],
    ];

    if (isset($adjacentRegions[$userRegion]) && in_array($jobRegion, $adjacentRegions[$userRegion])) {
        return 2; // Adjacent region
    }

    return 0;
}

/**
 * Calculate compensation fit score
 * Returns score 0-5
 */
function calculateCompensationFitScore($userId, $jobPayAmount, $jobPayType, $conn) {
    // Get user's historical pay rates
    $historicalRates = fetchOne($conn,
        "SELECT AVG(pay_amount) as avg_pay, pay_type
         FROM job_applications ja
         JOIN job_posts j ON ja.job_id = j.job_id
         WHERE ja.worker_id = ? AND ja.application_status IN ('approved', 'completed')
         GROUP BY j.pay_type",
        [$userId], 'i'
    );

    if (!$historicalRates) {
        // No history - neutral score
        return 3;
    }

    // Compare job pay to user's average for same pay type
    $avgPay = (float)$historicalRates['avg_pay'];
    if ($avgPay == 0) return 3;

    $ratio = $jobPayAmount / $avgPay;

    if ($ratio >= 1.2) return 5; // 20%+ above average
    if ($ratio >= 1.0) return 4; // At or above average
    if ($ratio >= 0.8) return 3; // Within 20% below
    if ($ratio >= 0.6) return 2; // 20-40% below
    return 1; // Significantly below
}

/**
 * Calculate job type preference score
 * Returns score 0-5
 */
function calculateJobTypePreferenceScore($conn, $userId, $jobType) {
    // Check user's application history for this job type
    $typeHistory = fetchOne($conn,
        "SELECT COUNT(*) as count FROM job_applications ja
         JOIN job_posts j ON ja.job_id = j.job_id
         WHERE ja.worker_id = ? AND j.job_type = ?",
        [$userId, $jobType], 'is'
    );

    if ($typeHistory && $typeHistory['count'] > 0) {
        return min(5, 2 + $typeHistory['count']); // 2 base + 1 per application, max 5
    }

    return 2; // Neutral for unknown preferences
}

/**
 * Calculate recency score
 * Returns score 0-3
 */
function calculateRecencyScore($createdAt) {
    $hoursAgo = (time() - strtotime($createdAt)) / 3600;

    if ($hoursAgo < 24) return 3;        // Less than 1 day
    if ($hoursAgo < 72) return 2;        // Less than 3 days
    if ($hoursAgo < 168) return 1;       // Less than 1 week
    return 0;                            // Older than 1 week
}

/**
 * Calculate diversity boost (novelty factor)
 * Returns score 0-2
 */
function calculateDiversityBoost($conn, $userId, $jobCategory, $currentRecommendations) {
    // Check if this category already appears in current recommendations
    $categoryCount = 0;
    foreach ($currentRecommendations as $rec) {
        if (isset($rec['job_category']) && $rec['job_category'] === $jobCategory) {
            $categoryCount++;
        }
    }

    // Penalize over-representation
    if ($categoryCount >= 5) return 0;
    if ($categoryCount >= 3) return 1;
    return 2; // Boost for under-represented categories
}

/**
 * Main MatchScore calculation function
 * Returns array with total score and component breakdown
 */
function calculateMatchScore($conn, $userId, $job, $userData, $currentRecommendations = []) {
    global $RECOMMENDER_WEIGHTS;

    $breakdown = [];

    // 1. Skill Match Score (0-35)
    $userSkills = [];
    $skillsResult = fetchAll($conn, "SELECT skill_name FROM user_skills WHERE user_id = ?", [$userId], 'i');
    foreach ($skillsResult as $s) $userSkills[] = $s['skill_name'];

    $breakdown['skill_match'] = calculateSkillAffinityScore(
        $userSkills,
        $job['required_skills'] ?? '',
        $job['preferred_skills'] ?? ''
    );

    // 2. Behavioral Score (0-25)
    $breakdown['behavioral'] = calculateBehavioralScore(
        $conn, $userId, $job['job_id'], $job['job_category'] ?? '',
        $job['job_type'] ?? '', $job['employer_id']
    );

    // 3. Collaborative Score (0-15)
    $breakdown['collaborative'] = calculateCollaborativeScore($conn, $userId, $job['job_id']);

    // 4. Trust Compatibility (0-10)
    $breakdown['trust_compatibility'] = calculateTrustCompatibilityScore(
        $userData['trust_score'] ?? 0,
        $job['employer_trust_score'] ?? 3.0,
        $job['slots_available'] ?? 1
    );

    // 5. Location Proximity (0-10)
    $breakdown['location_proximity'] = calculateLocationProximityScore(
        $userData['region'] ?? '',
        $userData['province'] ?? '',
        $userData['city'] ?? '',
        $job['location_region'] ?? '',
        $job['location_province'] ?? '',
        $job['location_city'] ?? ''
    );

    // 6. Compensation Fit (0-5)
    $breakdown['compensation_fit'] = calculateCompensationFitScore(
        $userId, $job['pay_amount'], $job['pay_type'], $conn
    );

    // 7. Job Type Preference (0-5)
    $breakdown['job_type_preference'] = calculateJobTypePreferenceScore($conn, $userId, $job['job_type'] ?? '');

    // 8. Recency (0-3)
    $breakdown['recency'] = calculateRecencyScore($job['created_at']);

    // 9. Diversity Boost (0-2)
    $breakdown['diversity_boost'] = calculateDiversityBoost(
        $conn, $userId, $job['job_category'] ?? '', $currentRecommendations
    );

    // Calculate weighted total
    $totalScore = 0;
    $maxPossible = 0;
    foreach ($RECOMMENDER_WEIGHTS as $component => $weight) {
        $totalScore += $breakdown[$component];
        $maxPossible += $weight;
    }

    // Normalize to 0-100 scale
    $normalizedScore = ($maxPossible > 0) ? round(($totalScore / $maxPossible) * 100) : 0;

    return [
        'total' => $normalizedScore,
        'raw_total' => $totalScore,
        'max_possible' => $maxPossible,
        'breakdown' => $breakdown,
        'match_tier' => getMatchTier($normalizedScore)
    ];
}

/**
 * Get match quality tier based on score
 */
function getMatchTier($score) {
    if ($score >= 85) return 'excellent';
    if ($score >= 70) return 'very_good';
    if ($score >= 55) return 'good';
    if ($score >= 40) return 'fair';
    return 'low';
}

/**
 * Get match tier display info
 */
function getMatchTierInfo($tier) {
    $tiers = [
        'excellent' => ['label' => 'Excellent Match', 'color' => 'green', 'icon' => 'fa-star'],
        'very_good' => ['label' => 'Very Good Match', 'color' => 'blue', 'icon' => 'fa-thumbs-up'],
        'good' => ['label' => 'Good Match', 'color' => 'pink', 'icon' => 'fa-check'],
        'fair' => ['label' => 'Fair Match', 'color' => 'orange', 'icon' => 'fa-minus'],
        'low' => ['label' => 'Low Match', 'color' => 'gray', 'icon' => 'fa-arrow-down']
    ];
    return $tiers[$tier] ?? $tiers['low'];
}

// Philippines regions (simplified list)
$PHILIPPINES_REGIONS = [
    'NCR' => 'National Capital Region',
    'CAR' => 'Cordillera Administrative Region',
    'Region I' => 'Ilocos Region',
    'Region II' => 'Cagayan Valley',
    'Region III' => 'Central Luzon',
    'Region IV-A' => 'CALABARZON',
    'Region IV-B' => 'MIMAROPA',
    'Region V' => 'Bicol Region',
    'Region VI' => 'Western Visayas',
    'Region VII' => 'Central Visayas',
    'Region VIII' => 'Eastern Visayas',
    'Region IX' => 'Zamboanga Peninsula',
    'Region X' => 'Northern Mindanao',
    'Region XI' => 'Davao Region',
    'Region XII' => 'SOCCSKSARGEN',
    'Region XIII' => 'Caraga',
    'BARMM' => 'Bangsamoro Autonomous Region in Muslim Mindanao'
];

// Helper function to sanitize input
// Note: htmlspecialchars is intentionally NOT applied here - it belongs at
// output time (all echo points already call htmlspecialchars). Applying it
// here would cause double-encoding of stored data.
function sanitizeInput($data) {
    $data = (string)$data;
    $data = str_replace("\0", '', $data);
    return trim($data);
}

function sanitizeMultilineInput($data) {
    $data = (string)$data;
    $data = str_replace("\0", '', $data);
    $data = preg_replace("/\r\n?|\n/", "\n", $data);
    return trim($data);
}

function isAnimatedGif($filePath) {
    $contents = @file_get_contents($filePath, false, null, 0, 200000);
    if ($contents === false) {
        return false;
    }

    return preg_match_all('/\x00\x21\xF9\x04.{4}\x00\x2C/s', $contents) > 1;
}

function saveUploadedImage($tmpPath, $destination, $maxWidth = 1920, $maxHeight = 1920, $quality = 82) {
    if (!extension_loaded('gd')) {
        return move_uploaded_file($tmpPath, $destination);
    }

    $info = @getimagesize($tmpPath);
    if ($info === false) {
        return false;
    }

    $mime = $info['mime'] ?? '';
    $src = null;

    switch ($mime) {
        case 'image/jpeg':
            $src = @imagecreatefromjpeg($tmpPath);
            break;
        case 'image/png':
            $src = @imagecreatefrompng($tmpPath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
                $src = @imagecreatefromwebp($tmpPath);
            } else {
                return move_uploaded_file($tmpPath, $destination);
            }
            break;
        case 'image/gif':
            if (isAnimatedGif($tmpPath)) {
                return move_uploaded_file($tmpPath, $destination);
            }
            $src = @imagecreatefromgif($tmpPath);
            break;
        default:
            return false;
    }

    if (!$src) {
        return false;
    }

    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($tmpPath);
        $orientation = $exif['Orientation'] ?? 1;
        if ($orientation === 3) {
            $src = imagerotate($src, 180, 0);
        } elseif ($orientation === 6) {
            $src = imagerotate($src, -90, 0);
        } elseif ($orientation === 8) {
            $src = imagerotate($src, 90, 0);
        }
    }

    $srcWidth = imagesx($src);
    $srcHeight = imagesy($src);
    $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight, 1);
    $newWidth = (int)round($srcWidth * $scale);
    $newHeight = (int)round($srcHeight * $scale);

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    if (in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

    $result = false;
    switch ($mime) {
        case 'image/jpeg':
            $result = imagejpeg($dst, $destination, $quality);
            break;
        case 'image/png':
            $result = imagepng($dst, $destination, 6);
            break;
        case 'image/webp':
            $result = imagewebp($dst, $destination, $quality);
            break;
        case 'image/gif':
            $result = imagegif($dst, $destination);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);

    if (!$result) {
        return move_uploaded_file($tmpPath, $destination);
    }

    return true;
}

function sanitizeInternalUrl($url, $fallback = 'index.php') {
    $url = trim((string)$url);
    if ($url === '') {
        return $fallback;
    }

    if (preg_match('/[\r\n]/', $url)) {
        return $fallback;
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return $fallback;
    }

    if (isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }

    $path = $parts['path'] ?? '';
    if (strpos($path, '..') !== false) {
        return $fallback;
    }

    return ltrim($url, '/');
}

function sanitizeExternalUrl($url) {
    $url = trim((string)$url);
    if ($url === '') {
        return '';
    }

    $validated = filter_var($url, FILTER_VALIDATE_URL);
    if ($validated === false) {
        return '';
    }

    $scheme = strtolower((string)parse_url($validated, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return '';
    }

    return $validated;
}

function normalizePhilippineMobile($mobile) {
    $mobile = preg_replace('/\s+/', '', (string)$mobile);
    if (strpos($mobile, '+63') === 0) {
        $mobile = '0' . substr($mobile, 3);
    }
    return $mobile;
}

function isValidPhilippineMobile($mobile) {
    return (bool)preg_match('/^09\d{9}$/', normalizePhilippineMobile($mobile));
}

function isValidRegionCode($regionCode) {
    global $PHILIPPINES_REGIONS;
    return isset($PHILIPPINES_REGIONS[$regionCode]);
}

// Helper function to redirect
function redirect($url) {
    header('Location: ' . sanitizeInternalUrl($url, 'index.php'));
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function to get current user type
function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

// CSRF helpers
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function regenerateCsrfToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

// Helper function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Helper function to require specific user type
function requireUserType($type) {
    requireLogin();
    if (getCurrentUserType() !== $type) {
        redirect('index.php');
    }
}

// Helper function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Helper function to time ago format
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'Just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

// Helper function to calculate trust score from ratings
function calculateUserTrustScore($conn, $user_id) {
    $result = fetchOne(
        $conn,
        "SELECT COUNT(*) as rating_count, AVG(rating_stars) as avg_rating FROM job_ratings WHERE ratee_id = ?",
        [$user_id],
        'i'
    );
    
    if (!$result || $result['rating_count'] == 0) {
        return 0.00;
    }
    
    // Calculate weighted average, capped at 5.00
    $trustScore = min((float)$result['avg_rating'], 5.00);
    return round($trustScore, 2);
}

// Helper function to update user trust score and log the change
function updateUserTrustScore($conn, $user_id, $rating_id = null, $triggered_by = null) {
    // Get current trust score
    $user = fetchOne($conn, "SELECT trust_score FROM users WHERE user_id = ?", [$user_id], 'i');
    $oldScore = $user['trust_score'] ?? 0.00;
    
    // Calculate new trust score
    $newScore = calculateUserTrustScore($conn, $user_id);
    
    // Update users table if score changed
    if ($oldScore != $newScore) {
        executeQuery(
            $conn,
            "UPDATE users SET trust_score = ?, updated_at = NOW() WHERE user_id = ?",
            [$newScore, $user_id],
            'di'
        );
        
        // Log the update
        executeQuery(
            $conn,
            "INSERT INTO trust_score_updates (user_id, old_score, new_score, reason, related_rating_id, triggered_by, created_at)
             VALUES (?, ?, ?, 'rating_received', ?, ?, NOW())",
            [$user_id, $oldScore, $newScore, $rating_id, $triggered_by],
            'idii'
        );
    }
    
    return $newScore;
}

require_once 'database.php';

function getClientIp() {
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? ''
    ];

    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }

        $ip = trim(explode(',', $candidate)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '0.0.0.0';
}

function buildRateLimitKey($scope, $identifier) {
    return hash('sha256', strtolower($scope . '|' . $identifier . '|' . getClientIp()));
}

function hasRateLimitTable($conn) {
    // Check each time to avoid stale state from persistent connections
    $result = $conn->query("SHOW TABLES LIKE 'auth_rate_limits'");
    $available = ($result && $result->num_rows > 0);
    if ($result) {
        $result->close();
    }

    return $available;
}

function isRateLimitExceeded($conn, $scope, $identifier, $maxAttempts = 6, $windowSeconds = 900, $lockSeconds = 900, &$retryAfterSeconds = 0) {
    $retryAfterSeconds = 0;
    if (!hasRateLimitTable($conn)) {
        return false;
    }

    $key = buildRateLimitKey($scope, $identifier);
    $row = fetchOne($conn, "SELECT attempts, window_started_at, locked_until FROM auth_rate_limits WHERE throttle_key = ?", [$key], 's');

    if (!$row) {
        return false;
    }

    $now = time();
    $windowStartedAt = strtotime((string)$row['window_started_at']) ?: $now;
    $lockedUntilTs = !empty($row['locked_until']) ? strtotime((string)$row['locked_until']) : 0;

    if ($lockedUntilTs > $now) {
        $retryAfterSeconds = max(1, $lockedUntilTs - $now);
        return true;
    }

    if (($windowStartedAt + $windowSeconds) < $now) {
        executeQuery(
            $conn,
            "UPDATE auth_rate_limits SET attempts = 0, window_started_at = NOW(), locked_until = NULL WHERE throttle_key = ?",
            [$key],
            's'
        );
        return false;
    }

    return ((int)$row['attempts'] >= $maxAttempts);
}

function registerRateLimitFailure($conn, $scope, $identifier, $maxAttempts = 6, $windowSeconds = 900, $lockSeconds = 900) {
    if (!hasRateLimitTable($conn)) {
        return;
    }

    $key = buildRateLimitKey($scope, $identifier);
    $row = fetchOne($conn, "SELECT attempts, window_started_at FROM auth_rate_limits WHERE throttle_key = ?", [$key], 's');

    if (!$row) {
        executeQuery(
            $conn,
            "INSERT INTO auth_rate_limits (throttle_key, scope, attempts, window_started_at, last_attempt_at, locked_until)
             VALUES (?, ?, 1, NOW(), NOW(), NULL)",
            [$key, $scope],
            'ss'
        );
        return;
    }

    $now = time();
    $windowStartedAt = strtotime((string)$row['window_started_at']) ?: $now;
    $windowExpired = ($windowStartedAt + $windowSeconds) < $now;
    $attempts = $windowExpired ? 1 : ((int)$row['attempts'] + 1);
    $shouldLock = $attempts >= $maxAttempts;

    executeQuery(
        $conn,
        "UPDATE auth_rate_limits
         SET attempts = ?,
             window_started_at = " . ($windowExpired ? "NOW()" : "window_started_at") . ",
             last_attempt_at = NOW(),
             locked_until = " . ($shouldLock ? "DATE_ADD(NOW(), INTERVAL ? SECOND)" : "NULL") . "
         WHERE throttle_key = ?",
        $shouldLock ? [$attempts, $lockSeconds, $key] : [$attempts, $key],
        $shouldLock ? 'iis' : 'is'
    );
}

function clearRateLimit($conn, $scope, $identifier) {
    if (!hasRateLimitTable($conn)) {
        return;
    }

    $key = buildRateLimitKey($scope, $identifier);
    executeQuery($conn, "DELETE FROM auth_rate_limits WHERE throttle_key = ?", [$key], 's');
}

function enforceActiveSessionUser() {
    static $checked = false;
    if ($checked || !isLoggedIn()) {
        return;
    }
    $checked = true;

    $conn = getDBConnection();
    $row = fetchOne($conn, "SELECT account_status FROM users WHERE user_id = ?", [getCurrentUserId()], 'i');
    closeDBConnection($conn);

    if (!$row || $row['account_status'] !== 'active') {
        session_unset();
        session_destroy();
        redirect('login.php?session=expired');
    }
}

enforceActiveSessionUser();
?>
