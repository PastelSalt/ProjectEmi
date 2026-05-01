<?php
/**
 * Skill Learn Screen
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Learn New Skills';
require_once 'config/config.php';
require_once 'includes/header.php';

$conn = getDBConnection();

$post_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 10;
$allowedPostTypes = ['certification', 'training', 'course', 'workshop'];
if (!empty($post_type) && !in_array($post_type, $allowedPostTypes, true)) {
    $post_type = '';
}

$fromWhereSql = " FROM skill_posts sp JOIN users u ON sp.admin_id = u.user_id";
$params = [];
$types = '';
$whereClauses = [];

if (!empty($post_type)) {
    $whereClauses[] = "sp.post_type = ?";
    $params[] = $post_type;
    $types .= 's';
}
if (!empty($category)) {
    $whereClauses[] = "sp.category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($whereClauses)) {
    $fromWhereSql .= " WHERE " . implode(' AND ', $whereClauses);
}

$countSql = "SELECT COUNT(*) as total" . $fromWhereSql;
$countRow = empty($params) ? fetchOne($conn, $countSql) : fetchOne($conn, $countSql, $params, $types);
$totalPosts = (int)($countRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalPosts / $pageSize));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $pageSize;
$sql = "SELECT sp.*, u.full_name as admin_name"
    . $fromWhereSql
    . " ORDER BY sp.is_featured DESC, sp.created_at DESC"
    . " LIMIT ? OFFSET ?";

$postsParams = $params;
$postsParams[] = $pageSize;
$postsParams[] = $offset;
$postsTypes = $types . 'ii';

$posts = fetchAll($conn, $sql, $postsParams, $postsTypes);
$startItem = $totalPosts > 0 ? ($offset + 1) : 0;
$endItem = $totalPosts > 0 ? min($offset + $pageSize, $totalPosts) : 0;

$buildLearnPageUrl = function($targetPage = 1) use ($post_type, $category) {
    $qs = [];
    if (!empty($post_type)) {
        $qs['type'] = $post_type;
    }
    if (!empty($category)) {
        $qs['category'] = $category;
    }
    if ($targetPage > 1) {
        $qs['page'] = (int)$targetPage;
    }
    return 'skill-learn.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
};

$buildTypeUrl = function($targetType = '') use ($category) {
    $qs = [];
    if (!empty($targetType)) {
        $qs['type'] = $targetType;
    }
    if (!empty($category)) {
        $qs['category'] = $category;
    }
    return 'skill-learn.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
};

$buildCategoryUrl = function($targetCategory = '') use ($post_type) {
    $qs = [];
    if (!empty($post_type)) {
        $qs['type'] = $post_type;
    }
    if (!empty($targetCategory)) {
        $qs['category'] = $targetCategory;
    }
    return 'skill-learn.php' . (!empty($qs) ? '?' . http_build_query($qs) : '');
};

$categoriesSql = "SELECT DISTINCT category FROM skill_posts WHERE category IS NOT NULL AND category != ''";
$categories = fetchAll($conn, $categoriesSql);
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-group">
                    <select name="type" class="form-control" style="width: auto;">
                        <option value="">All Types</option>
                        <option value="certification" <?php echo $post_type == 'certification' ? 'selected' : ''; ?>>Certifications</option>
                        <option value="training" <?php echo $post_type == 'training' ? 'selected' : ''; ?>>Training</option>
                        <option value="course" <?php echo $post_type == 'course' ? 'selected' : ''; ?>>Courses</option>
                        <option value="workshop" <?php echo $post_type == 'workshop' ? 'selected' : ''; ?>>Workshops</option>
                    </select>
                    <select name="category" class="form-control" style="width: auto;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!empty($cat['category'])): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-small"><i class="fas fa-filter"></i> Filter</button>
                    <?php if (!empty($post_type) || !empty($category)): ?>
                        <a href="skill-learn.php" class="btn btn-outline btn-small"><i class="fas fa-times"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (isLoggedIn() && getCurrentUserType() == 'admin'): ?>
                <div style="margin-bottom: 16px; text-align: right;">
                    <a href="add-skill-post.php" class="btn btn-primary btn-small"><i class="fas fa-plus"></i> Create New Post</a>
                </div>
            <?php endif; ?>

            <!-- Posts List -->
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    SKILL LEARNING HUB
                    <span class="view-all"><?php echo (int)$totalPosts; ?> posts</span>
                </div>
                <div class="panel-body">
                    <?php if (empty($posts)): ?>
                        <div class="text-center" style="padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                            <p class="text-muted">No posts yet. Check back later!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="update-item">
                                <div class="update-item-title">
                                    <?php if ($post['is_featured']): ?>
                                        <span class="tag tag-yellow" style="font-size: 0.65rem;"><i class="fas fa-star"></i> Featured</span>
                                    <?php endif; ?>
                                    <span class="tag tag-<?php echo $post['post_type'] == 'certification' ? 'pink' : ($post['post_type'] == 'training' ? 'blue' : 'green'); ?>" style="font-size: 0.65rem;">
                                        <?php echo ucfirst($post['post_type']); ?>
                                    </span>
                                    <?php echo htmlspecialchars($post['post_title']); ?>
                                </div>
                                <div class="update-item-body">
                                    <?php echo nl2br(htmlspecialchars(substr($post['post_content'], 0, 250))); ?>
                                    <?php if (strlen($post['post_content']) > 250): ?>...<?php endif; ?>
                                </div>
                                <?php if (!empty($post['tags'])): ?>
                                    <div class="d-flex gap-1 flex-wrap mb-1">
                                        <?php foreach (array_slice(explode(',', $post['tags']), 0, 4) as $tag): ?>
                                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="update-item-meta d-flex justify-between align-center">
                                    <span>
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['admin_name']); ?>
                                        &middot; <i class="fas fa-clock"></i> <?php echo timeAgo($post['created_at']); ?>
                                        &middot; <i class="fas fa-eye"></i> <?php echo number_format($post['views_count']); ?>
                                        &middot; <i class="fas fa-heart"></i> <?php echo number_format($post['likes_count']); ?>
                                    </span>
                                    <?php $safeLinkUrl = sanitizeExternalUrl($post['link_url'] ?? ''); ?>
                                    <?php if (!empty($safeLinkUrl)): ?>
                                        <a href="<?php echo htmlspecialchars($safeLinkUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-small" onclick="trackPostView(<?php echo $post['post_id']; ?>)">
                                            <i class="fas fa-external-link-alt"></i> Learn More
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="listing-meta-row">
                            <span class="text-small text-muted">
                                Showing <?php echo (int)$startItem; ?>-<?php echo (int)$endItem; ?> of <?php echo (int)$totalPosts; ?> posts
                            </span>
                            <?php if ($totalPages > 1): ?>
                                <nav class="pagination-nav" aria-label="Learning posts pages">
                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    ?>

                                    <?php if ($page > 1): ?>
                                        <a class="page-link" href="<?php echo htmlspecialchars($buildLearnPageUrl($page - 1)); ?>">&laquo;</a>
                                    <?php endif; ?>

                                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                                        <a class="page-link <?php echo $p === $page ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildLearnPageUrl($p)); ?>"><?php echo $p; ?></a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a class="page-link" href="<?php echo htmlspecialchars($buildLearnPageUrl($page + 1)); ?>">&raquo;</a>
                                    <?php endif; ?>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!isLoggedIn()): ?>
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    GET PERSONALIZED RECOMMENDATIONS
                </div>
                <div class="panel-body" style="text-align: center; padding: 20px;">
                    <p class="text-muted mb-2">Sign up to get skill recommendations for your career</p>
                    <a href="signup.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Sign Up Now</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    LEARNING CATEGORIES
                </div>
                <div class="panel-body-compact">
                    <a href="<?php echo htmlspecialchars($buildTypeUrl('certification')); ?>" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-certificate"></i> Certifications</a>
                    <a href="<?php echo htmlspecialchars($buildTypeUrl('training')); ?>" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-chalkboard-teacher"></i> Training</a>
                    <a href="<?php echo htmlspecialchars($buildTypeUrl('course')); ?>" class="btn btn-white btn-small btn-block mb-1"><i class="fas fa-book-open"></i> Courses</a>
                    <a href="<?php echo htmlspecialchars($buildTypeUrl('workshop')); ?>" class="btn btn-white btn-small btn-block"><i class="fas fa-users"></i> Workshops</a>
                </div>
            </div>

            <?php if (!empty($categories)): ?>
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    TOPIC INDEX
                </div>
                <div class="panel-body">
                    <div class="tech-tags">
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!empty($cat['category'])): ?>
                                <a href="<?php echo htmlspecialchars($buildCategoryUrl($cat['category'])); ?>" class="tag tag-pink" style="text-decoration: none;"><?php echo htmlspecialchars($cat['category']); ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    ABOUT LEARNING HUB
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        Explore certifications, training programs, and courses to enhance your skills. 
                        All resources are curated by the RaketGo admin team.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function trackPostView(postId) {
    <?php if (isLoggedIn()): ?>
    fetch('api/track-interaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'view', post_id: postId })
    });
    <?php endif; ?>
}
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
