<?php
/**
 * Home Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Home';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get filter parameters
$location_region = isset($_GET['region']) ? sanitizeInput($_GET['region']) : '';
$location_city = isset($_GET['city']) ? sanitizeInput($_GET['city']) : '';
$search_query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$job_category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$job_type_filter = isset($_GET['job_type']) ? sanitizeInput($_GET['job_type']) : '';
$remote_policy = isset($_GET['remote_policy']) ? sanitizeInput($_GET['remote_policy']) : '';
$job_sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 12;

if (!empty($location_region) && !isValidRegionCode($location_region)) {
    $location_region = '';
}

// Validate job type filter
if (!empty($job_type_filter) && !in_array($job_type_filter, $ALLOWED_JOB_TYPES, true)) {
    $job_type_filter = '';
}

// Build query
$allowedSort = ['newest', 'pay_high', 'pay_low'];
if (!in_array($job_sort, $allowedSort, true)) {
    $job_sort = 'newest';
}

$allowedRemotePolicies = ['on_site', 'hybrid', 'fully_remote'];
if (!empty($remote_policy) && !in_array($remote_policy, $allowedRemotePolicies, true)) {
    $remote_policy = '';
}

$fromWhereSql = " FROM job_posts j
                  JOIN users u ON j.employer_id = u.user_id
                  WHERE j.job_status = 'active'";

$params = [];
$types = '';

if (!empty($location_region)) {
    $fromWhereSql .= " AND j.location_region = ?";
    $params[] = $location_region;
    $types .= 's';
}

if (!empty($location_city)) {
    $fromWhereSql .= " AND j.location_city LIKE ?";
    $params[] = "%{$location_city}%";
    $types .= 's';
}

if (!empty($search_query)) {
    $fromWhereSql .= " AND (j.job_title LIKE ? OR j.job_description LIKE ? OR j.required_skills LIKE ?)";
    $searchTerm = "%{$search_query}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if (!empty($job_category)) {
    $fromWhereSql .= " AND j.job_category = ?";
    $params[] = $job_category;
    $types .= 's';
}

if (!empty($remote_policy)) {
    $fromWhereSql .= " AND j.remote_policy = ?";
    $params[] = $remote_policy;
    $types .= 's';
}

if (!empty($job_type_filter)) {
    $fromWhereSql .= " AND j.job_type = ?";
    $params[] = $job_type_filter;
    $types .= 's';
}

$countSql = "SELECT COUNT(*) as total" . $fromWhereSql;
$countRow = empty($params) ? fetchOne($conn, $countSql) : fetchOne($conn, $countSql, $params, $types);
$totalJobs = (int)($countRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalJobs / $pageSize));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $pageSize;
$orderBySql = " ORDER BY j.created_at DESC";
if ($job_sort === 'pay_high') {
    $orderBySql = " ORDER BY j.pay_amount DESC, j.created_at DESC";
} elseif ($job_sort === 'pay_low') {
    $orderBySql = " ORDER BY j.pay_amount ASC, j.created_at DESC";
}

$jobsSql = "SELECT j.*, u.full_name as employer_name, u.region, u.province, u.city"
    . $fromWhereSql
    . $orderBySql
    . " LIMIT ? OFFSET ?";

$jobsParams = $params;
$jobsParams[] = $pageSize;
$jobsParams[] = $offset;
$jobsTypes = $types . 'ii';

$startItem = $totalJobs > 0 ? ($offset + 1) : 0;
$endItem = $totalJobs > 0 ? min($offset + $pageSize, $totalJobs) : 0;

$jobs = fetchAll($conn, $jobsSql, $jobsParams, $jobsTypes);

// Get latest announcements
$announcementsSql = "SELECT sp.*, u.full_name as admin_name 
                     FROM skill_posts sp 
                     JOIN users u ON sp.admin_id = u.user_id 
                     WHERE sp.is_featured = 1 
                     ORDER BY sp.created_at DESC 
                     LIMIT 3";
$announcements = fetchAll($conn, $announcementsSql);

// Get job categories for filter
$categoriesSql = "SELECT DISTINCT job_category FROM job_posts WHERE job_category IS NOT NULL AND job_category != ''";
$categories = fetchAll($conn, $categoriesSql);

if (!empty($location_region) && isset($PHILIPPINES_REGIONS[$location_region])) {
    $_SESSION['last_region'] = $location_region;
}
$lastRegion = $_SESSION['last_region'] ?? '';

$regionCounts = array_fill_keys(array_keys($PHILIPPINES_REGIONS), 0);
$regionCountRows = fetchAll(
    $conn,
    "SELECT location_region, COUNT(*) as total
     FROM job_posts
     WHERE job_status = 'active' AND location_region IS NOT NULL AND location_region != ''
     GROUP BY location_region"
);
foreach ($regionCountRows as $rc) {
    if (isset($regionCounts[$rc['location_region']])) {
        $regionCounts[$rc['location_region']] = (int)$rc['total'];
    }
}

$regionGroups = [
    'Luzon' => ['NCR', 'CAR', 'Region I', 'Region II', 'Region III', 'Region IV-A', 'Region IV-B', 'Region V'],
    'Visayas' => ['Region VI', 'Region VII', 'Region VIII'],
    'Mindanao' => ['Region IX', 'Region X', 'Region XI', 'Region XII', 'Region XIII', 'BARMM']
];

$regionShort = [
    'NCR' => 'NCR',
    'CAR' => 'CAR',
    'Region I' => 'I',
    'Region II' => 'II',
    'Region III' => 'III',
    'Region IV-A' => 'IV-A',
    'Region IV-B' => 'IV-B',
    'Region V' => 'V',
    'Region VI' => 'VI',
    'Region VII' => 'VII',
    'Region VIII' => 'VIII',
    'Region IX' => 'IX',
    'Region X' => 'X',
    'Region XI' => 'XI',
    'Region XII' => 'XII',
    'Region XIII' => 'XIII',
    'BARMM' => 'BARMM'
];

$regionBlockMap = [
    'CAR' => ['group' => 'luzon', 'col' => 4, 'row' => 1, 'colSpan' => 1, 'rowSpan' => 3],
    'Region I' => ['group' => 'luzon', 'col' => 3, 'row' => 1, 'colSpan' => 1, 'rowSpan' => 3],
    'Region II' => ['group' => 'luzon', 'col' => 5, 'row' => 1, 'colSpan' => 1, 'rowSpan' => 3],
    'Region III' => ['group' => 'luzon', 'col' => 3, 'row' => 4, 'colSpan' => 3, 'rowSpan' => 1, 'widthScale' => 0.75],
    'NCR' => ['group' => 'luzon', 'col' => 4, 'row' => 5, 'colSpan' => 1, 'rowSpan' => 1],
    'Region IV-A' => ['group' => 'luzon', 'col' => 5, 'row' => 6, 'colSpan' => 2, 'rowSpan' => 1],
    'Region V' => ['group' => 'luzon', 'col' => 7, 'row' => 6, 'colSpan' => 2, 'rowSpan' => 1],
    'Region IV-B' => ['group' => 'luzon', 'col' => 1, 'row' => 6, 'colSpan' => 2, 'rowSpan' => 3],
    'Region VI' => ['group' => 'visayas', 'col' => 3, 'row' => 7, 'colSpan' => 2, 'rowSpan' => 1],
    'Region VII' => ['group' => 'visayas', 'col' => 5, 'row' => 7, 'colSpan' => 1, 'rowSpan' => 2],
    'Region VIII' => ['group' => 'visayas', 'col' => 7, 'row' => 7, 'colSpan' => 2, 'rowSpan' => 1],
    'Region IX' => ['group' => 'mindanao', 'col' => 2, 'row' => 9, 'colSpan' => 2, 'rowSpan' => 1],
    'Region X' => ['group' => 'mindanao', 'col' => 4, 'row' => 9, 'colSpan' => 2, 'rowSpan' => 1],
    'Region XIII' => ['group' => 'mindanao', 'col' => 6, 'row' => 9, 'colSpan' => 2, 'rowSpan' => 1],
    'Region XII' => ['group' => 'mindanao', 'col' => 4, 'row' => 10, 'colSpan' => 2, 'rowSpan' => 1],
    'Region XI' => ['group' => 'mindanao', 'col' => 6, 'row' => 10, 'colSpan' => 2, 'rowSpan' => 1],
    'BARMM' => ['group' => 'mindanao', 'col' => 2, 'row' => 11, 'colSpan' => 2, 'rowSpan' => 1]
];

$buildRegionUrl = function($regionCode = '', $targetPage = 1) use ($location_region, $location_city, $search_query, $job_category, $remote_policy, $job_sort, $job_type_filter) {
    $qs = [];
    if (!empty($regionCode)) {
        $qs['region'] = $regionCode;
    }
    if (!empty($location_city)) {
        $qs['city'] = $location_city;
    }
    if (!empty($search_query)) {
        $qs['q'] = $search_query;
    }
    if (!empty($job_category)) {
        $qs['category'] = $job_category;
    }
    if (!empty($remote_policy)) {
        $qs['remote_policy'] = $remote_policy;
    }
    if (!empty($job_type_filter)) {
        $qs['job_type'] = $job_type_filter;
    }
    if ($job_sort !== 'newest') {
        $qs['sort'] = $job_sort;
    }
    if ($targetPage > 1) {
        $qs['page'] = (int)$targetPage;
    }
    return 'index.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
};

$buildJobsPageUrl = function($targetPage = 1) use ($location_region, $location_city, $search_query, $job_category, $remote_policy, $job_sort, $job_type_filter) {
    $qs = [];
    if (!empty($location_region)) {
        $qs['region'] = $location_region;
    }
    if (!empty($location_city)) {
        $qs['city'] = $location_city;
    }
    if (!empty($search_query)) {
        $qs['q'] = $search_query;
    }
    if (!empty($job_category)) {
        $qs['category'] = $job_category;
    }
    if (!empty($remote_policy)) {
        $qs['remote_policy'] = $remote_policy;
    }
    if (!empty($job_type_filter)) {
        $qs['job_type'] = $job_type_filter;
    }
    if ($job_sort !== 'newest') {
        $qs['sort'] = $job_sort;
    }
    if ($targetPage > 1) {
        $qs['page'] = (int)$targetPage;
    }
    return 'index.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
};

// Helper function to build filter URLs for job type widget
function buildFilterUrl($filterName, $filterValue) {
    $qs = [];

    // Copy all current GET parameters
    $params = ['region', 'city', 'q', 'category', 'job_type', 'remote_policy', 'sort', 'page'];
    foreach ($params as $param) {
        if (isset($_GET[$param]) && !empty($_GET[$param])) {
            $qs[$param] = $_GET[$param];
        }
    }

    // Update or remove the specific filter
    if (empty($filterValue)) {
        unset($qs[$filterName]);
    } else {
        $qs[$filterName] = $filterValue;
    }

    // Reset to page 1 when changing filters
    unset($qs['page']);

    return 'index.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
}

$popularRegions = array_filter($regionCounts, function($count) {
    return $count > 0;
});
arsort($popularRegions);
$popularRegions = array_slice($popularRegions, 0, 6, true);

$newJobsTodayRow = fetchOne(
    $conn,
    "SELECT COUNT(*) AS total FROM job_posts WHERE job_status = 'active' AND DATE(created_at) = CURDATE()"
);
$newJobsToday = (int)($newJobsTodayRow['total'] ?? 0);

$activeEmployersRow = fetchOne(
    $conn,
    "SELECT COUNT(DISTINCT employer_id) AS total FROM job_posts WHERE job_status = 'active'"
);
$activeEmployers = (int)($activeEmployersRow['total'] ?? 0);
$regionsCovered = count(array_filter($regionCounts, function($count) {
    return $count > 0;
}));

$isLoggedIn = isLoggedIn();
$currentUserType = $isLoggedIn ? getCurrentUserType() : '';

$getStartedData = [
    'titleIcon' => 'fas fa-bolt',
    'title' => 'Get Started',
    'steps' => [],
    'actions' => []
];

if (!$isLoggedIn) {
    $getStartedData['steps'] = [
        'Create a worker or employer account.',
        'Complete your location and profile details.',
        'Start applying or posting jobs right away.'
    ];
    $getStartedData['actions'] = [
        ['href' => 'signup.php', 'class' => 'btn btn-primary btn-small btn-block mb-1', 'icon' => 'fas fa-user-plus', 'label' => 'Create Account'],
        ['href' => 'login.php', 'class' => 'btn btn-outline btn-small btn-block', 'icon' => 'fas fa-sign-in-alt', 'label' => 'Login']
    ];
} elseif ($currentUserType === 'worker') {
    $getStartedData['steps'] = [
        'Add or update your skills in your dashboard.',
        'Check recommended jobs in For You.',
        'Apply and monitor your application status.'
    ];
    $getStartedData['actions'] = [
        ['href' => 'dashboard-worker.php', 'class' => 'btn btn-primary btn-small btn-block mb-1', 'icon' => 'fas fa-tachometer-alt', 'label' => 'My Dashboard'],
        ['href' => 'for-you.php', 'class' => 'btn btn-white btn-small btn-block', 'icon' => 'fas fa-star', 'label' => 'View Recommendations']
    ];
} elseif ($currentUserType === 'employer') {
    $getStartedData['steps'] = [
        'Post a job with clear requirements.',
        'Review applicants in your dashboard.',
        'Contact shortlisted workers through messages.'
    ];
    $getStartedData['actions'] = [
        ['href' => 'post-job.php', 'class' => 'btn btn-primary btn-small btn-block mb-1', 'icon' => 'fas fa-plus', 'label' => 'Post a Job'],
        ['href' => 'dashboard-employer.php', 'class' => 'btn btn-white btn-small btn-block', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Employer Dashboard']
    ];
} else {
    $getStartedData['steps'] = [
        'Review user activity and recent job posts.',
        'Monitor announcements and learning content.',
        'Keep platform records organized and updated.'
    ];
    $getStartedData['actions'] = [
        ['href' => 'dashboard-admin.php', 'class' => 'btn btn-primary btn-small btn-block', 'icon' => 'fas fa-shield-alt', 'label' => 'Admin Dashboard']
    ];
}

$quickLinks = [];
if ($isLoggedIn) {
    if ($currentUserType === 'worker') {
        $quickLinks[] = ['href' => 'for-you.php', 'icon' => 'fas fa-star', 'label' => 'For You'];
        $quickLinks[] = ['href' => 'dashboard-worker.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'];
    } elseif ($currentUserType === 'employer') {
        $quickLinks[] = ['href' => 'post-job.php', 'icon' => 'fas fa-plus', 'label' => 'Post Job'];
        $quickLinks[] = ['href' => 'dashboard-employer.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'];
    }
    $quickLinks[] = ['href' => 'skill-learn.php', 'icon' => 'fas fa-graduation-cap', 'label' => 'Learn Skills'];
    $quickLinks[] = ['href' => 'messages.php', 'icon' => 'fas fa-envelope', 'label' => 'Messages'];
}
?>

<div class="container home-ui-refresh">
    <div class="layout-two-col">
        <!-- ===== MAIN CONTENT ===== -->
        <div class="home-main-content">
            <!-- Hero -->
            <section class="home-hero">
                <div class="hero-label"><span class="pulse"></span> <?php echo (int)$newJobsToday; ?> new jobs today</div>
                <h1>Find your next <span>raket</span><br>anywhere in the PH</h1>
                <p class="hero-sub">Connect with employers across the Philippines. Apply fast, chat directly, and get hired sooner.</p>

                <form method="GET" action="" class="hero-search-form">
                    <div class="search-bar home-search-bar hero-search-bar">
                        <i class="fas fa-search"></i>
                        <input 
                            type="text" 
                            name="q" 
                            class="form-control" 
                            placeholder="Search jobs, skills, or employers..."
                            value="<?php echo htmlspecialchars($search_query); ?>"
                        >
                        <span class="sdiv" aria-hidden="true"></span>
                        <select name="region" class="search-select" aria-label="Filter by region">
                            <option value="">All Regions</option>
                            <?php foreach ($PHILIPPINES_REGIONS as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo $location_region == $code ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="sdiv" aria-hidden="true"></span>
                        <select name="category" class="search-select" aria-label="Filter by category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php if (!empty($cat['job_category'])): ?>
                                    <option value="<?php echo htmlspecialchars($cat['job_category']); ?>" <?php echo $job_category == $cat['job_category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['job_category']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                    <div class="hero-search-extra">
                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            placeholder="City"
                            value="<?php echo htmlspecialchars($location_city); ?>"
                        >
                        <select name="sort" class="form-control">
                            <option value="newest" <?php echo $job_sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="pay_high" <?php echo $job_sort === 'pay_high' ? 'selected' : ''; ?>>Pay: High to Low</option>
                            <option value="pay_low" <?php echo $job_sort === 'pay_low' ? 'selected' : ''; ?>>Pay: Low to High</option>
                        </select>
                        <select name="remote_policy" class="form-control" aria-label="Filter by work arrangement">
                            <option value="">All Arrangements</option>
                            <option value="on_site" <?php echo $remote_policy === 'on_site' ? 'selected' : ''; ?>>On-Site</option>
                            <option value="hybrid" <?php echo $remote_policy === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                            <option value="fully_remote" <?php echo $remote_policy === 'fully_remote' ? 'selected' : ''; ?>>Fully Remote</option>
                        </select>
                        <?php if (!empty($location_region) || !empty($location_city) || !empty($job_category) || !empty($search_query) || !empty($remote_policy) || !empty($job_type_filter) || $job_sort !== 'newest'): ?>
                            <a href="index.php" class="btn btn-outline btn-small">Clear Filters</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="hero-stats">
                    <div>
                        <div class="stat-num"><?php echo number_format((int)$totalJobs); ?>+</div>
                        <div class="stat-lbl">Active jobs</div>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo number_format((int)$activeEmployers); ?>+</div>
                        <div class="stat-lbl">Employers</div>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo (int)$regionsCovered; ?></div>
                        <div class="stat-lbl">Regions covered</div>
                    </div>
                </div>
            </section>

            <script>
                window.openRegionMapModal = window.openRegionMapModal || function () {
                    window.location.hash = 'region-map-modal';
                };
                window.closeRegionMapModal = window.closeRegionMapModal || function () {
                    if (window.location.hash === '#region-map-modal') {
                        history.pushState('', document.title, window.location.pathname + window.location.search);
                    }
                };
            </script>

            <!-- Region Picker (Baitoru-inspired QOL) -->
            <div class="panel location-picker-panel home-location-panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    FIND JOBS BY REGION
                    <?php if (!empty($location_region) && isset($PHILIPPINES_REGIONS[$location_region])): ?>
                        <span class="view-all">Current: <?php echo htmlspecialchars($PHILIPPINES_REGIONS[$location_region]); ?></span>
                    <?php endif; ?>
                </div>
                <div class="panel-body">
                    <?php if (!empty($lastRegion) && $lastRegion !== $location_region && isset($PHILIPPINES_REGIONS[$lastRegion])): ?>
                        <div class="region-picker-top-actions">
                            <a href="<?php echo htmlspecialchars($buildRegionUrl($lastRegion)); ?>" class="btn btn-white btn-small">
                                <i class="fas fa-history"></i> Continue in <?php echo htmlspecialchars($PHILIPPINES_REGIONS[$lastRegion]); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="region-map-launcher">
                        <p class="region-map-launch-copy">
                            Open the full regional map to browse by Luzon, Visayas, and Mindanao with live job counts.
                        </p>
                        <a href="#region-map-modal" class="btn btn-primary btn-small js-open-region-map">
                            <i class="fas fa-map"></i> Open Regional Map
                        </a>
                        <?php if (!empty($location_region)): ?>
                            <a href="<?php echo htmlspecialchars($buildRegionUrl('')); ?>" class="btn btn-outline btn-small region-clear-btn">
                                <i class="fas fa-times"></i> Clear Region Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="region-map-modal" id="region-map-modal" aria-hidden="true">
                <a href="#" class="region-map-backdrop js-close-region-map" aria-label="Close regional map"></a>
                <div class="region-map-dialog" role="dialog" aria-modal="true" aria-labelledby="region-map-title">
                    <a href="#" class="region-map-close js-close-region-map" aria-label="Close regional map">
                        <i class="fas fa-times"></i>
                    </a>
                    <div class="region-map-modal-header">
                        <h3 id="region-map-title">Find Jobs by Region</h3>
                        <p>Tap a region tile or chip to apply your location filter.</p>
                    </div>
                    <div class="location-picker-layout">
                        <div class="ph-map-area">
                            <div class="ph-map-title">Philippines Regional Map</div>
                            <div class="ph-map-subtitle">Simple block-style map: tap any tile to filter jobs near you.</div>
                            <div class="ph-map-canvas">
                                <div class="ph-block-map-grid" role="img" aria-label="Interactive Philippines block map by region">
                                    <?php foreach ($regionBlockMap as $code => $layout): ?>
                                        <?php
                                        $count = $regionCounts[$code] ?? 0;
                                        $isActive = $location_region === $code;
                                        $regionName = $PHILIPPINES_REGIONS[$code] ?? $code;
                                        ?>
                                        <a
                                            href="<?php echo htmlspecialchars($buildRegionUrl($code)); ?>"
                                            class="ph-region-tile group-<?php echo htmlspecialchars($layout['group']); ?> <?php echo $isActive ? 'active' : ''; ?> <?php echo $count === 0 ? 'is-empty' : ''; ?>"
                                            style="--col: <?php echo (int)$layout['col']; ?>; --row: <?php echo (int)$layout['row']; ?>; --col-span: <?php echo (int)$layout['colSpan']; ?>; --row-span: <?php echo (int)$layout['rowSpan']; ?>; --tile-width-scale: <?php echo isset($layout['widthScale']) ? number_format((float)$layout['widthScale'], 2, '.', '') : '1'; ?>;"
                                            aria-label="<?php echo htmlspecialchars($regionName . ' (' . $count . ' jobs)'); ?>"
                                            title="<?php echo htmlspecialchars($regionName); ?>"
                                        >
                                            <span class="tile-short"><?php echo htmlspecialchars($regionShort[$code] ?? $code); ?></span>
                                            <span class="tile-count"><?php echo (int)$count; ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="ph-map-legend">
                                <span class="legend-item"><i class="legend-dot available"></i> With Jobs</span>
                                <span class="legend-item"><i class="legend-dot active"></i> Selected</span>
                                <span class="legend-item"><i class="legend-dot empty"></i> No Active Jobs</span>
                            </div>
                        </div>

                        <div class="region-quick-list">
                            <?php foreach ($regionGroups as $groupName => $groupCodes): ?>
                                <div class="region-group-card">
                                    <div class="region-group-title"><?php echo htmlspecialchars($groupName); ?></div>
                                    <div class="region-chip-grid">
                                        <?php foreach ($groupCodes as $code): ?>
                                            <a href="<?php echo htmlspecialchars($buildRegionUrl($code)); ?>" class="region-chip <?php echo $location_region === $code ? 'active' : ''; ?> <?php echo ($regionCounts[$code] ?? 0) === 0 ? 'is-empty' : ''; ?>">
                                                <span><?php echo htmlspecialchars($PHILIPPINES_REGIONS[$code]); ?></span>
                                                <small><?php echo (int)($regionCounts[$code] ?? 0); ?></small>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (!empty($popularRegions)): ?>
                                <div class="region-group-card region-popular-card">
                                    <div class="region-group-title"><i class="fas fa-fire"></i> Popular Right Now</div>
                                    <div class="region-chip-grid">
                                        <?php foreach ($popularRegions as $code => $count): ?>
                                            <a href="<?php echo htmlspecialchars($buildRegionUrl($code)); ?>" class="region-chip <?php echo $location_region === $code ? 'active' : ''; ?>">
                                                <span><?php echo htmlspecialchars($PHILIPPINES_REGIONS[$code]); ?></span>
                                                <small><?php echo (int)$count; ?></small>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Jobs Section -->
            <div class="panel home-jobs-panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    LATEST JOB OPPORTUNITIES
                    <a href="index.php" class="view-all">View All &raquo;</a>
                </div>
                <div class="panel-body">
                    <div class="job-skeleton-list" aria-hidden="true">
                        <div class="job-skeleton-card"></div>
                        <div class="job-skeleton-card"></div>
                        <div class="job-skeleton-card"></div>
                    </div>
                    <?php if (empty($jobs)): ?>
                        <div class="empty-jobs-state" role="status" aria-live="polite">
                            <div class="empty-jobs-illustration" aria-hidden="true">
                                <span class="empty-spark spark-a"></span>
                                <span class="empty-spark spark-b"></span>
                                <span class="empty-spark spark-c"></span>
                                <div class="empty-jobs-icon-wrap">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                            </div>
                            <h3>No jobs found yet</h3>
                            <p>Try widening your search or clear one filter to reveal more opportunities.</p>
                            <a href="index.php" class="btn btn-outline btn-small">
                                <i class="fas fa-sync-alt"></i> Reset Filters
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <div class="compact-job-item job-bento-card" onclick="window.location.href='job-details.php?id=<?php echo $job['job_id']; ?>'" style="cursor: pointer;">
                                <div class="d-flex justify-between align-center job-bento-head">
                                    <h4 class="job-bento-title">
                                        <?php echo htmlspecialchars($job['job_title']); ?>
                                    </h4>
                                    <span class="text-xs text-muted job-bento-time"><?php echo timeAgo($job['created_at']); ?></span>
                                </div>
                                <div class="d-flex gap-2 flex-wrap job-bento-meta" style="margin-top: 4px;">
                                    <span class="text-small text-muted job-bento-meta-item">
                                        <i class="fas fa-building"></i>
                                        <a href="employer-profile.php?id=<?php echo $job['employer_id']; ?>"
                                           style="color: inherit; text-decoration: none;"
                                           onclick="event.stopPropagation();">
                                            <?php echo htmlspecialchars($job['employer_name']); ?>
                                        </a>
                                    </span>
                                    <span class="text-small text-muted job-bento-meta-item">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location_city']); ?>
                                    </span>
                                    <span class="text-small text-muted job-bento-meta-item">
                                        <i class="fas fa-peso-sign"></i> <?php echo formatCurrency($job['pay_amount']); ?>/<?php echo $job['pay_type']; ?>
                                    </span>
                                </div>

                                <div class="job-bento-pills">
                                    <?php
                                    $jobTypeInfo = getJobTypeInfo($job['job_type']);
                                    if ($jobTypeInfo):
                                    ?>
                                        <span class="tag job-pill job-pill-type" style="background: var(--<?php echo $jobTypeInfo['color']; ?>-light, var(--off-white)); color: var(--<?php echo $jobTypeInfo['color']; ?>-dark, var(--primary-blue-dark));">
                                            <i class="fas <?php echo $jobTypeInfo['icon']; ?>" style="font-size: 0.7rem; margin-right: 3px;"></i>
                                            <?php echo htmlspecialchars($jobTypeInfo['label']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="tag job-pill job-pill-pay">
                                        <span class="job-pill-dot" aria-hidden="true"></span>
                                        <?php echo htmlspecialchars(ucfirst($job['pay_type'])); ?> pay
                                    </span>
                                    <?php if (!empty($job['job_category'])): ?>
                                        <span class="tag job-pill job-pill-category">
                                            <span class="job-pill-dot" aria-hidden="true"></span>
                                            <?php echo htmlspecialchars($job['job_category']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['remote_policy'])): ?>
                                        <span class="tag job-pill job-pill-remote">
                                            <span class="job-pill-dot" aria-hidden="true"></span>
                                            <?php
                                            $remoteLabels = [
                                                'on_site' => 'On-Site',
                                                'hybrid' => 'Hybrid',
                                                'fully_remote' => 'Fully Remote'
                                            ];
                                            echo htmlspecialchars($remoteLabels[$job['remote_policy']] ?? ucfirst(str_replace('_', ' ', $job['remote_policy'])));
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($job['required_skills'])): ?>
                                    <div class="d-flex gap-1 flex-wrap job-bento-skills" style="margin-top: 6px;">
                                        <?php
                                        $skills = array_values(array_filter(array_map('trim', explode(',', $job['required_skills']))));
                                        $displaySkills = array_slice($skills, 0, 4);
                                        foreach ($displaySkills as $skill):
                                        ?>
                                            <span class="tag job-pill job-pill-skill">
                                                <span class="job-pill-dot" aria-hidden="true"></span>
                                                <?php echo htmlspecialchars($skill); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($skills) > 4): ?>
                                            <span class="tag job-pill job-pill-more">
                                                <span class="job-pill-dot" aria-hidden="true"></span>
                                                +<?php echo count($skills) - 4; ?> more
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="listing-meta-row">
                            <span class="text-small text-muted">
                                Showing <?php echo (int)$startItem; ?>-<?php echo (int)$endItem; ?> of <?php echo (int)$totalJobs; ?> jobs
                            </span>
                            <?php if ($totalPages > 1): ?>
                                <nav class="pagination-nav" aria-label="Job list pages">
                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    ?>

                                    <?php if ($page > 1): ?>
                                        <a class="page-link" href="<?php echo htmlspecialchars($buildJobsPageUrl($page - 1)); ?>">&laquo;</a>
                                    <?php endif; ?>

                                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                                        <a class="page-link <?php echo $p === $page ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildJobsPageUrl($p)); ?>"><?php echo $p; ?></a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a class="page-link" href="<?php echo htmlspecialchars($buildJobsPageUrl($page + 1)); ?>">&raquo;</a>
                                    <?php endif; ?>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Call to Action for guests -->
            <?php if (!$isLoggedIn): ?>
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    JOIN RAKETGO
                </div>
                <div class="panel-body" style="text-align: center; padding: 24px;">
                    <p style="margin-bottom: 12px;">Join workers and employers on RaketGo</p>
                    <div class="d-flex gap-2 justify-center">
                        <a href="signup.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                        <a href="skill-learn.php" class="btn btn-secondary">
                            <i class="fas fa-graduation-cap"></i> Browse Resources
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== SIDEBAR ===== -->
        <div class="sidebar home-sidebar-shell">
            <!-- Get Started Widget -->
            <div class="widget sidebar-section">
                <div class="section-header section-header-green sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="<?php echo htmlspecialchars($getStartedData['titleIcon']); ?>"></i> <?php echo htmlspecialchars($getStartedData['title']); ?></span>
                </div>
                <div class="panel-body">
                    <ul class="get-started-list">
                        <?php foreach ($getStartedData['steps'] as $index => $step): ?>
                            <li><strong><?php echo (int)($index + 1); ?>.</strong> <?php echo htmlspecialchars($step); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="get-started-actions">
                        <?php foreach ($getStartedData['actions'] as $action): ?>
                            <a href="<?php echo htmlspecialchars($action['href']); ?>" class="<?php echo htmlspecialchars($action['class']); ?>">
                                <i class="<?php echo htmlspecialchars($action['icon']); ?>"></i> <?php echo htmlspecialchars($action['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Job Type Filter Widget -->
            <div class="widget sidebar-section">
                <div class="section-header section-header-pink sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-briefcase"></i> Filter by Job Type</span>
                </div>
                <div class="panel-body" style="padding: 0.6rem 0.8rem;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <?php foreach ($JOB_TYPES_CONFIG as $key => $config): ?>
                            <?php
                            $isActive = $job_type_filter === $key;
                            $typeUrl = $isActive
                                ? htmlspecialchars(buildFilterUrl('job_type', ''))
                                : htmlspecialchars(buildFilterUrl('job_type', $key));
                            $activeClass = $isActive ? 'active' : '';
                            ?>
                            <a href="<?php echo $typeUrl; ?>" class="btn btn-small <?php echo $isActive ? 'btn-primary' : 'btn-white'; ?>" style="text-align: left; display: flex; align-items: center; gap: 8px; font-size: 0.8rem;">
                                <i class="fas <?php echo $config['icon']; ?>"></i>
                                <?php echo htmlspecialchars($config['label']); ?>
                                <?php if ($isActive): ?>
                                    <i class="fas fa-check" style="margin-left: auto; font-size: 0.7rem;"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Updates & Announcements Widget -->
            <?php if (!empty($announcements)): ?>
            <div class="widget sidebar-section">
                <div class="section-header section-header-pink sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-newspaper"></i> Updates & Announcements</span>
                </div>
                <div class="panel-body">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="headline-item announcement-item">
                            <span class="headline-badge announcement">Announcement</span>
                            <div class="headline-title"><?php echo htmlspecialchars($ann['post_title']); ?></div>
                            <div class="headline-snippet">
                                <?php echo htmlspecialchars(mb_substr($ann['post_content'], 0, 110, 'UTF-8')); ?><?php echo mb_strlen($ann['post_content'], 'UTF-8') > 110 ? '...' : ''; ?>
                            </div>
                            <div class="headline-date"><i class="fas fa-clock"></i> <?php echo date('Y.m.d', strtotime($ann['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- How It Works Widget -->
            <div class="widget sidebar-section">
                <div class="section-header section-header-gray sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-cogs"></i> How RaketGo Works</span>
                </div>
                <div class="panel-body">
                    <ul class="how-list">
                        <li>
                            <span class="how-step">1</span>
                            <span>Browse jobs by location, category, and skills.</span>
                        </li>
                        <li>
                            <span class="how-step">2</span>
                            <span>Open a job post and apply with a short message.</span>
                        </li>
                        <li>
                            <span class="how-step">3</span>
                            <span>Chat directly with employers for faster hiring.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Helpful Tips Widget -->
            <div class="widget sidebar-section">
                <div class="section-header sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-lightbulb"></i> Helpful Tips</span>
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        Keep your profile details and skills updated so employers can find you quickly.
                        Use the <a href="for-you.php">For You</a> page for personalized recommendations.
                    </div>
                </div>
            </div>

            <!-- Quick Links Widget -->
            <?php if ($isLoggedIn): ?>
            <div class="widget sidebar-section">
                <div class="section-header section-header-green sidebar-section-header">
                    <span class="sidebar-title-inline"><i class="fas fa-link"></i> Quick Links</span>
                </div>
                <div class="panel-body-compact">
                    <?php foreach ($quickLinks as $index => $link): ?>
                        <a href="<?php echo htmlspecialchars($link['href']); ?>" class="btn btn-white btn-small btn-block <?php echo $index < (count($quickLinks) - 1) ? 'mb-1' : ''; ?>">
                            <i class="<?php echo htmlspecialchars($link['icon']); ?>"></i> <?php echo htmlspecialchars($link['label']); ?>
                        </a>
                    <?php endforeach; ?>
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
