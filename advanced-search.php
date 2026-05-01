<?php
/**
 * Advanced Search Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Advanced Search';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get all filter parameters
$search_query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$location_region = isset($_GET['region']) ? sanitizeInput($_GET['region']) : '';
$location_province = isset($_GET['province']) ? sanitizeInput($_GET['province']) : '';
$location_city = isset($_GET['city']) ? sanitizeInput($_GET['city']) : '';
$job_category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$job_type_filter = isset($_GET['job_type']) ? sanitizeInput($_GET['job_type']) : '';
$remote_policy = isset($_GET['remote_policy']) ? sanitizeInput($_GET['remote_policy']) : '';
$pay_min = isset($_GET['pay_min']) ? (float)$_GET['pay_min'] : 0;
$pay_max = isset($_GET['pay_max']) ? (float)$_GET['pay_max'] : 0;
$skills_required = isset($_GET['skills']) ? sanitizeInput($_GET['skills']) : '';
$experience_level = isset($_GET['experience']) ? sanitizeInput($_GET['experience']) : '';
$employer_rating = isset($_GET['employer_rating']) ? (float)$_GET['employer_rating'] : 0;
$date_posted = isset($_GET['date_posted']) ? sanitizeInput($_GET['date_posted']) : '';
$job_sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'relevance';

$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 12;

// Build the query
$fromWhereSql = " FROM job_posts j
                  JOIN users u ON j.employer_id = u.user_id
                  WHERE j.job_status = 'active'";

$params = [];
$types = '';

// Text search
if (!empty($search_query)) {
    $fromWhereSql .= " AND (j.job_title LIKE ? OR j.job_description LIKE ? OR j.required_skills LIKE ? OR j.preferred_skills LIKE ?)";
    $searchTerm = "%{$search_query}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

// Location filters
if (!empty($location_region)) {
    $fromWhereSql .= " AND j.location_region = ?";
    $params[] = $location_region;
    $types .= 's';
}

if (!empty($location_province)) {
    $fromWhereSql .= " AND j.location_province LIKE ?";
    $params[] = "%{$location_province}%";
    $types .= 's';
}

if (!empty($location_city)) {
    $fromWhereSql .= " AND j.location_city LIKE ?";
    $params[] = "%{$location_city}%";
    $types .= 's';
}

// Category filter
if (!empty($job_category)) {
    $fromWhereSql .= " AND j.job_category = ?";
    $params[] = $job_category;
    $types .= 's';
}

// Job type filter
if (!empty($job_type_filter)) {
    $fromWhereSql .= " AND j.job_type = ?";
    $params[] = $job_type_filter;
    $types .= 's';
}

// Remote policy filter
if (!empty($remote_policy)) {
    $fromWhereSql .= " AND j.remote_policy = ?";
    $params[] = $remote_policy;
    $types .= 's';
}

// Pay range filter
if ($pay_min > 0) {
    $fromWhereSql .= " AND j.pay_amount >= ?";
    $params[] = $pay_min;
    $types .= 'd';
}

if ($pay_max > 0) {
    $fromWhereSql .= " AND j.pay_amount <= ?";
    $params[] = $pay_max;
    $types .= 'd';
}

// Skills filter
if (!empty($skills_required)) {
    $skillsArray = array_map('trim', explode(',', $skills_required));
    foreach ($skillsArray as $skill) {
        if (!empty($skill)) {
            $fromWhereSql .= " AND (j.required_skills LIKE ? OR j.preferred_skills LIKE ?)";
            $skillTerm = "%{$skill}%";
            $params[] = $skillTerm;
            $params[] = $skillTerm;
            $types .= 'ss';
        }
    }
}

// Employer rating filter
if ($employer_rating > 0) {
    $fromWhereSql .= " AND u.trust_score >= ?";
    $params[] = $employer_rating;
    $types .= 'd';
}

// Date posted filter
if (!empty($date_posted)) {
    switch ($date_posted) {
        case '24h':
            $fromWhereSql .= " AND j.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            break;
        case '3d':
            $fromWhereSql .= " AND j.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)";
            break;
        case '7d':
            $fromWhereSql .= " AND j.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case '14d':
            $fromWhereSql .= " AND j.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
            break;
        case '30d':
            $fromWhereSql .= " AND j.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

// Count total results
$countSql = "SELECT COUNT(*) as total" . $fromWhereSql;
$countRow = empty($params) ? fetchOne($conn, $countSql) : fetchOne($conn, $countSql, $params, $types);
$totalJobs = (int)($countRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalJobs / $pageSize));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $pageSize;

// Sorting
$orderBySql = " ORDER BY ";
switch ($job_sort) {
    case 'pay_high':
        $orderBySql .= "j.pay_amount DESC, j.created_at DESC";
        break;
    case 'pay_low':
        $orderBySql .= "j.pay_amount ASC, j.created_at DESC";
        break;
    case 'newest':
        $orderBySql .= "j.created_at DESC";
        break;
    default:
        $orderBySql .= "j.created_at DESC";
}

// Main query
$jobsSql = "SELECT j.*, u.full_name as employer_name, u.trust_score as employer_trust_score, u.region, u.province, u.city"
    . $fromWhereSql
    . $orderBySql
    . " LIMIT ? OFFSET ?";

$jobsParams = $params;
$jobsParams[] = $pageSize;
$jobsParams[] = $offset;
$jobsTypes = $types . 'ii';

$jobs = fetchAll($conn, $jobsSql, $jobsParams, $jobsTypes);

// Get filter options
$categories = fetchAll($conn, "SELECT DISTINCT job_category FROM job_posts WHERE job_category IS NOT NULL AND job_category != '' ORDER BY job_category");
$provinces = fetchAll($conn, "SELECT DISTINCT location_province as province FROM job_posts WHERE location_province IS NOT NULL AND location_province != '' ORDER BY location_province");

// Build URL helper
function buildAdvancedSearchUrl($params) {
    $qs = [];
    foreach ($params as $key => $value) {
        if (!empty($value)) {
            $qs[$key] = $value;
        }
    }
    return 'advanced-search.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
}

$currentParams = [
    'q' => $search_query,
    'region' => $location_region,
    'province' => $location_province,
    'city' => $location_city,
    'category' => $job_category,
    'job_type' => $job_type_filter,
    'remote_policy' => $remote_policy,
    'pay_min' => $pay_min,
    'pay_max' => $pay_max,
    'skills' => $skills_required,
    'experience' => $experience_level,
    'employer_rating' => $employer_rating,
    'date_posted' => $date_posted,
    'sort' => $job_sort
];
?>

<div class="container">
    <div class="panel">
        <div class="section-header">
            <span class="header-square"></span>
            ADVANCED JOB SEARCH
        </div>
        <div class="panel-body">
            <form method="GET" action="" class="advanced-search-form">
                <!-- Main Search -->
                <div class="form-group">
                    <label for="q"><strong>Keywords</strong></label>
                    <input type="text" id="q" name="q" class="form-control" 
                           placeholder="Job title, skills, or description..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <!-- Location Filters -->
                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" class="form-control">
                            <option value="">All Regions</option>
                            <?php foreach ($PHILIPPINES_REGIONS as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo $location_region === $code ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <select id="province" name="province" class="form-control">
                            <option value="">All Provinces</option>
                            <?php foreach ($provinces as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['province']); ?>" <?php echo $location_province === $p['province'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['province']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" 
                               placeholder="City name"
                               value="<?php echo htmlspecialchars($location_city); ?>">
                    </div>
                </div>

                <!-- Job Details -->
                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php if (!empty($cat['job_category'])): ?>
                                    <option value="<?php echo htmlspecialchars($cat['job_category']); ?>" <?php echo $job_category === $cat['job_category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['job_category']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="job_type">Job Type</label>
                        <select id="job_type" name="job_type" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach ($JOB_TYPES_CONFIG as $key => $config): ?>
                                <option value="<?php echo $key; ?>" <?php echo $job_type_filter === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($config['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="remote_policy">Work Arrangement</label>
                        <select id="remote_policy" name="remote_policy" class="form-control">
                            <option value="">All Arrangements</option>
                            <option value="on_site" <?php echo $remote_policy === 'on_site' ? 'selected' : ''; ?>>On-Site</option>
                            <option value="hybrid" <?php echo $remote_policy === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                            <option value="fully_remote" <?php echo $remote_policy === 'fully_remote' ? 'selected' : ''; ?>>Fully Remote</option>
                        </select>
                    </div>
                </div>

                <!-- Pay Range -->
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="pay_min">Minimum Pay (₱)</label>
                        <input type="number" id="pay_min" name="pay_min" class="form-control" 
                               placeholder="0" min="0" step="0.01"
                               value="<?php echo $pay_min > 0 ? $pay_min : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="pay_max">Maximum Pay (₱)</label>
                        <input type="number" id="pay_max" name="pay_max" class="form-control" 
                               placeholder="No limit" min="0" step="0.01"
                               value="<?php echo $pay_max > 0 ? $pay_max : ''; ?>">
                    </div>
                </div>

                <!-- Skills & Rating -->
                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="skills">Required Skills <span class="text-muted">(comma-separated)</span></label>
                        <input type="text" id="skills" name="skills" class="form-control" 
                               placeholder="e.g., PHP, MySQL, JavaScript"
                               value="<?php echo htmlspecialchars($skills_required); ?>">
                    </div>
                    <div class="form-group">
                        <label for="employer_rating">Minimum Employer Rating</label>
                        <select id="employer_rating" name="employer_rating" class="form-control">
                            <option value="0">Any Rating</option>
                            <option value="4" <?php echo $employer_rating == 4 ? 'selected' : ''; ?>>4+ Stars</option>
                            <option value="3" <?php echo $employer_rating == 3 ? 'selected' : ''; ?>>3+ Stars</option>
                            <option value="2" <?php echo $employer_rating == 2 ? 'selected' : ''; ?>>2+ Stars</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_posted">Date Posted</label>
                        <select id="date_posted" name="date_posted" class="form-control">
                            <option value="">Any Time</option>
                            <option value="24h" <?php echo $date_posted === '24h' ? 'selected' : ''; ?>>Last 24 Hours</option>
                            <option value="3d" <?php echo $date_posted === '3d' ? 'selected' : ''; ?>>Last 3 Days</option>
                            <option value="7d" <?php echo $date_posted === '7d' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="14d" <?php echo $date_posted === '14d' ? 'selected' : ''; ?>>Last 14 Days</option>
                            <option value="30d" <?php echo $date_posted === '30d' ? 'selected' : ''; ?>>Last 30 Days</option>
                        </select>
                    </div>
                </div>

                <!-- Sort & Submit -->
                <div class="form-row" style="display: flex; gap: 1rem; margin-top: 1.5rem; align-items: flex-end;">
                    <div class="form-group" style="flex: 0 0 200px;">
                        <label for="sort">Sort By</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="newest" <?php echo $job_sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="pay_high" <?php echo $job_sort === 'pay_high' ? 'selected' : ''; ?>>Pay: High to Low</option>
                            <option value="pay_low" <?php echo $job_sort === 'pay_low' ? 'selected' : ''; ?>>Pay: Low to High</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-search"></i> Search Jobs
                        </button>
                    </div>
                    <div class="form-group" style="flex: 0 0 auto;">
                        <a href="advanced-search.php" class="btn btn-outline">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <?php if (!empty($search_query) || !empty($location_region) || !empty($job_category) || $pay_min > 0 || $pay_max > 0 || !empty($skills_required)): ?>
    <div class="panel" style="margin-top: 1.5rem;">
        <div class="section-header section-header-blue">
            <span class="header-square"></span>
            SEARCH RESULTS
            <span class="view-all"><?php echo number_format($totalJobs); ?> jobs found</span>
        </div>
        <div class="panel-body">
            <?php if (empty($jobs)): ?>
                <div class="text-center" style="padding: 2rem;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--gray-mid); margin-bottom: 1rem;"></i>
                    <h3>No jobs found</h3>
                    <p class="text-muted">Try adjusting your search criteria to find more results.</p>
                </div>
            <?php else: ?>
                <div class="job-list">
                    <?php foreach ($jobs as $job): ?>
                        <div class="compact-job-item">
                            <div class="d-flex align-center gap-2">
                                <div class="message-avatar" style="width: 50px; height: 50px; font-size: 1.1rem;">
                                    <?php echo mb_strtoupper(mb_substr($job['employer_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 0.3rem;">
                                        <a href="job-details.php?id=<?php echo $job['job_id']; ?>" style="color: var(--text-dark); text-decoration: none;">
                                            <?php echo htmlspecialchars($job['job_title']); ?>
                                        </a>
                                    </h4>
                                    <div class="d-flex gap-2 flex-wrap" style="font-size: 0.8rem; color: var(--text-muted);">
                                        <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($job['employer_name']); ?></span>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location_city'] . ', ' . $job['location_province']); ?></span>
                                        <span><i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?>/<?php echo $job['pay_type']; ?></span>
                                        <?php if ($job['employer_trust_score'] > 0): ?>
                                            <span><i class="fas fa-star" style="color: #FFD700;"></i> <?php echo number_format($job['employer_trust_score'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($job['required_skills'])): ?>
                                        <div style="margin-top: 0.4rem;">
                                            <?php foreach (array_slice(explode(',', $job['required_skills']), 0, 4) as $skill): ?>
                                                <span class="tag tag-pink" style="font-size: 0.65rem;"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--text-muted);">
                                        <i class="fas fa-clock"></i> Posted <?php echo timeAgo($job['created_at']); ?>
                                        <?php if (!empty($job['remote_policy']) && $job['remote_policy'] !== 'on_site'): ?>
                                            <span class="tag tag-blue" style="font-size: 0.65rem; margin-left: 0.5rem;">
                                                <?php echo $job['remote_policy'] === 'fully_remote' ? 'Fully Remote' : 'Hybrid'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary btn-small">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination" style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo buildAdvancedSearchUrl(array_merge($currentParams, ['page' => $page - 1])); ?>" class="btn btn-outline btn-small">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary btn-small"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo buildAdvancedSearchUrl(array_merge($currentParams, ['page' => $i])); ?>" class="btn btn-outline btn-small"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo buildAdvancedSearchUrl(array_merge($currentParams, ['page' => $page + 1])); ?>" class="btn btn-outline btn-small">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
