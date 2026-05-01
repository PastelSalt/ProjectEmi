<?php
/**
 * Manage Users Page (Admin)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Manage Users';
require_once 'config/config.php';
requireUserType('admin');
require_once 'includes/header.php';

$conn = getDBConnection();
$admin_id = getCurrentUserId();

$action = sanitizeInput($_GET['action'] ?? '');
$user_id = (int)($_GET['user_id'] ?? 0);
$search = sanitizeInput($_GET['search'] ?? '');
$filter_type = sanitizeInput($_GET['type'] ?? '');
$filter_status = sanitizeInput($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    }

    $action_type = sanitizeInput($_POST['action_type'] ?? '');
    $target_user_id = (int)($_POST['user_id'] ?? 0);
    $action_reason = sanitizeInput($_POST['action_reason'] ?? '');

    if ($action_type === 'suspend' && $target_user_id > 0) {
        if (executeQuery($conn, "UPDATE users SET account_status = 'suspended' WHERE user_id = ? AND user_type != 'admin'", [$target_user_id], 'i')) {
            $success = 'User suspended successfully.';
        } else {
            $error = 'Failed to suspend user.';
        }
    } elseif ($action_type === 'activate' && $target_user_id > 0) {
        if (executeQuery($conn, "UPDATE users SET account_status = 'active' WHERE user_id = ?", [$target_user_id], 'i')) {
            $success = 'User activated successfully.';
        } else {
            $error = 'Failed to activate user.';
        }
    } elseif ($action_type === 'delete' && $target_user_id > 0) {
        if (executeQuery($conn, "UPDATE users SET account_status = 'deleted' WHERE user_id = ? AND user_type != 'admin'", [$target_user_id], 'i')) {
            $success = 'User marked as deleted.';
        } else {
            $error = 'Failed to delete user.';
        }
    }
}

// Build query
$where = ["user_type != 'admin'"];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(full_name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term);
    $types .= 'ss';
}

if (!empty($filter_type)) {
    $where[] = "user_type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

if (!empty($filter_status)) {
    $where[] = "account_status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total count
$countResult = fetchOne($conn, "SELECT COUNT(*) as total FROM users $whereClause", $params, $types);
$total = $countResult['total'] ?? 0;
$total_pages = ceil($total / $per_page);

// Get users
$users = fetchAll($conn, "SELECT user_id, full_name, email, user_type, account_status, city, trust_score, created_at 
    FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?", 
    [...$params, $per_page, $offset], $types . 'ii');
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    MANAGE USERS
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

                    <!-- Filters -->
                    <form method="GET" class="filter-bar" style="margin-bottom: 16px; display: flex; gap: 12px; padding: 12px;">
                        <input type="text" name="search" placeholder="Search by name or email..." class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                        
                        <select name="type" class="form-control" style="width: 140px;">
                            <option value="">All Types</option>
                            <option value="worker" <?php echo $filter_type === 'worker' ? 'selected' : ''; ?>>Workers</option>
                            <option value="employer" <?php echo $filter_type === 'employer' ? 'selected' : ''; ?>>Employers</option>
                        </select>

                        <select name="status" class="form-control" style="width: 140px;">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="suspended" <?php echo $filter_status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            <option value="deleted" <?php echo $filter_status === 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                        </select>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="manage-users.php" class="btn btn-outline">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </form>

                    <!-- Users Table -->
                    <?php if (empty($users)): ?>
                        <div class="text-center" style="padding: 2rem;">
                            <i class="fas fa-users" style="font-size: 2rem; color: var(--text-light); display: block; margin-bottom: 0.5rem;"></i>
                            <p class="text-muted">No users found</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                                        <td class="text-small"><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><span class="tag tag-<?php echo $u['user_type'] == 'worker' ? 'pink' : 'blue'; ?>" style="font-size: 0.7rem;"><?php echo ucfirst($u['user_type']); ?></span></td>
                                        <td class="text-small"><?php echo htmlspecialchars($u['city'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="tag tag-<?php echo $u['account_status'] == 'active' ? 'green' : ($u['account_status'] == 'suspended' ? 'orange' : 'gray'); ?>" style="font-size: 0.7rem;">
                                                <?php echo ucfirst($u['account_status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-small text-pink" style="font-weight: 600;"><?php echo number_format($u['trust_score'], 2); ?></td>
                                        <td class="text-xs text-muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td>
                                            <?php if ($u['account_status'] === 'active'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action_type" value="suspend">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                    <button type="submit" class="btn btn-outline" style="font-size: 0.75rem; padding: 4px 8px;" 
                                                            onclick="return confirm('Suspend this user?');">
                                                        <i class="fas fa-pause"></i> Suspend
                                                    </button>
                                                </form>
                                            <?php elseif ($u['account_status'] === 'suspended'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action_type" value="activate">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                    <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 4px 8px;">
                                                        <i class="fas fa-play"></i> Activate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-nav" style="margin-top: 16px; justify-content: center;">
                                <?php if ($page > 1): ?>
                                    <a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo urlencode($filter_status); ?>&page=1" class="page-link">First</a>
                                    <a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $page - 1; ?>" class="page-link">Prev</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $i; ?>" 
                                       class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $page + 1; ?>" class="page-link">Next</a>
                                    <a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $total_pages; ?>" class="page-link">Last</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    USER STATS
                </div>
                <div class="site-info-grid">
                    <?php
                    $workerCount = fetchOne($conn, "SELECT COUNT(*) as count FROM users WHERE user_type = 'worker' AND account_status = 'active'");
                    $employerCount = fetchOne($conn, "SELECT COUNT(*) as count FROM users WHERE user_type = 'employer' AND account_status = 'active'");
                    $suspendedCount = fetchOne($conn, "SELECT COUNT(*) as count FROM users WHERE account_status = 'suspended'");
                    ?>
                    <div class="site-info-item">
                        <div class="site-info-value" style="color: #D792AC;"><?php echo number_format($workerCount['count'] ?? 0); ?></div>
                        <div class="site-info-label">Active Workers</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value" style="color: #5F96B3;"><?php echo number_format($employerCount['count'] ?? 0); ?></div>
                        <div class="site-info-label">Active Employers</div>
                    </div>
                    <div class="site-info-item">
                        <div class="site-info-value" style="color: #FF9566;"><?php echo number_format($suspendedCount['count'] ?? 0); ?></div>
                        <div class="site-info-label">Suspended</div>
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    QUICK ACTIONS
                </div>
                <div class="panel-body">
                    <a href="dashboard-admin.php" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
