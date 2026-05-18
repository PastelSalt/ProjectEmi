<?php
/**
 * RaketKo Profile Page
 * Professional Social Profile with Career Content
 */
$page_title = 'RaketKo Profile';
require_once 'config/config.php';

$is_logged_in = isLoggedIn();

$conn = getDBConnection();
$current_user_id = $is_logged_in ? getCurrentUserId() : null;
$profile_user_id = (int)($_GET['id'] ?? ($current_user_id ?? 0));

// Initialize message variables
$success = '';
$error = '';

// Validate profile user exists
$profile_user = fetchOne($conn, "SELECT * FROM users WHERE user_id = ?", [$profile_user_id], 'i');
if (!$profile_user) {
    header('Location: raketko-feed.php');
    exit;
}

// Get or create social profile
$socialProfile = fetchOne($conn, "SELECT * FROM social_profiles WHERE user_id = ?", [$profile_user_id], 'i');
if (!$socialProfile) {
    executeQuery($conn, 
        "INSERT INTO social_profiles (user_id, bio, headline) VALUES (?, '', '')", 
        [$profile_user_id], 'i'
    );
    $socialProfile = fetchOne($conn, "SELECT * FROM social_profiles WHERE user_id = ?", [$profile_user_id], 'i');
}

// Handle profile editing (only for logged-in users and own profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in && $profile_user_id == $current_user_id) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_profile':
                $bio = sanitizeMultilineInput($_POST['bio'] ?? '');
                $headline = sanitizeInput($_POST['headline'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                $website = sanitizeInput($_POST['website'] ?? '');
                $linkedin_url = sanitizeInput($_POST['linkedin_url'] ?? '');
                $skills = sanitizeInput($_POST['skills'] ?? '');
                $interests = sanitizeInput($_POST['interests'] ?? '');
                
                $skills_array = !empty($skills) ? array_map('trim', explode(',', $skills)) : [];
                $interests_array = !empty($interests) ? array_map('trim', explode(',', $interests)) : [];
                
                executeQuery($conn, 
                    "UPDATE social_profiles SET bio = ?, headline = ?, location = ?, website = ?, linkedin_url = ?, 
                     skills = ?, interests = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?",
                    [$bio, $headline, $location, $website, $linkedin_url, json_encode($skills_array), json_encode($interests_array), $profile_user_id],
                    'sssssssi'
                );
                
                $success = 'Profile updated successfully!';
                break;
                
            case 'upload_cover_photo':
                if (!empty($_FILES['cover_photo']['name'])) {
                    $cover_photo = handleProfilePhotoUpload($_FILES['cover_photo'], 'covers');
                    if ($cover_photo) {
                        executeQuery($conn, "UPDATE social_profiles SET cover_photo = ? WHERE user_id = ?", [$cover_photo, $profile_user_id], 'si');
                        $success = 'Cover photo updated successfully!';
                    }
                }
                break;
        }
    }
}

// Get user's posts
$posts = fetchAll($conn, 
    "SELECT sp.*, 
            (SELECT COUNT(*) FROM social_post_likes WHERE post_id = sp.post_id) as likes_count,
            (SELECT COUNT(*) FROM social_post_comments WHERE post_id = sp.post_id) as comments_count,
            (SELECT COUNT(*) FROM social_post_shares WHERE post_id = sp.post_id) as shares_count
     FROM social_posts sp 
     WHERE sp.user_id = ? AND sp.visibility IN ('public', 'connections')
     ORDER BY sp.created_at DESC 
     LIMIT 10", 
    [$profile_user_id], 'i'
);

// Get connection status (only for logged-in users)
$connection_status = null;
if ($is_logged_in && $profile_user_id != $current_user_id) {
    $connection = fetchOne($conn, 
        "SELECT * FROM social_connections WHERE follower_id = ? AND following_id = ?", 
        [$current_user_id, $profile_user_id], 'ii'
    );
    $connection_status = $connection ? $connection['status'] : null;
}

// Get followers and following counts
$followers_count = fetchOne($conn, "SELECT COUNT(*) as count FROM social_connections WHERE following_id = ? AND status = 'accepted'", [$profile_user_id], 'i')['count'];
$following_count = fetchOne($conn, "SELECT COUNT(*) as count FROM social_connections WHERE follower_id = ? AND status = 'accepted'", [$profile_user_id], 'i')['count'];

// Get recent activity
$recent_activity = fetchAll($conn, 
    "SELECT sp.post_id, sp.title, sp.created_at, 'post' as activity_type
     FROM social_posts sp 
     WHERE sp.user_id = ? 
     UNION ALL
     SELECT sc.connection_id, CONCAT('Started following ', u.full_name), sc.created_at, 'follow' as activity_type
     FROM social_connections sc
     JOIN users u ON sc.following_id = u.user_id
     WHERE sc.follower_id = ? AND sc.status = 'accepted'
     ORDER BY created_at DESC 
     LIMIT 5", 
    [$profile_user_id, $profile_user_id], 'ii'
);

function handleProfilePhotoUpload($file, $type) {
    $upload_dir = "uploads/social_media/{$type}/";
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $filename = uniqid($type . '_', true) . '.' . $file_ext;
            $filepath = $upload_dir . $filename;
            
            $maxWidth = ($type === 'covers') ? 2400 : 800;
            $maxHeight = ($type === 'covers') ? 1200 : 800;

            if (saveUploadedImage($file['tmp_name'], $filepath, $maxWidth, $maxHeight)) {
                return $filepath;
            }
        }
    }
    
    return false;
}

closeDBConnection($conn);
require_once 'includes/header.php';
?>

<div class="container">
    <!-- Profile Header -->
    <div class="raketko-profile-header" style="background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark)); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
        <?php if (!empty($socialProfile['cover_photo'])): ?>
            <div style="position: relative; margin-bottom: 1rem;">
                <img src="<?php echo htmlspecialchars($socialProfile['cover_photo']); ?>" 
                     alt="Cover Photo" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <?php if ($profile_user_id == $current_user_id): ?>
                    <form method="POST" enctype="multipart/form-data" style="position: absolute; top: 1rem; right: 1rem;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="upload_cover_photo">
                        <label for="cover_photo" class="btn btn-outline btn-small" style="background: rgba(255,255,255,0.9); color: var(--text-dark); cursor: pointer;">
                            <i class="fas fa-camera"></i> Change Cover
                        </label>
                        <input type="file" id="cover_photo" name="cover_photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div style="display: flex; gap: 2rem; align-items: flex-end;">
                <?php if (!empty($profile_user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($profile_user['profile_picture']); ?>" 
                         alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: white; color: var(--primary-blue); 
                                display: flex; align-items: center; justify-content: center; font-size: 3rem; border: 4px solid white;">
                        <?php echo mb_strtoupper(mb_substr($profile_user['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <div>
                    <h1 style="margin: 0; font-size: 2rem; font-weight: bold;">
                        <?php echo htmlspecialchars($profile_user['full_name']); ?>
                    </h1>
                    <p style="margin: 0.5rem 0; font-size: 1.1rem; opacity: 0.9;">
                        <?php echo htmlspecialchars($socialProfile['headline'] ?? 'Professional'); ?>
                    </p>
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.8;">
                        <?php echo htmlspecialchars($socialProfile['location'] ?? ($profile_user['city'] . ', ' . $profile_user['province'])); ?>
                    </p>
                    <?php if ($is_logged_in && $profile_user_id != $current_user_id): ?>
                        <div style="margin-top: 1rem;">
                            <a href="messages.php?user=<?php echo $profile_user_id; ?>" class="profile-message-btn">
                                <i class="fas fa-envelope"></i> Message
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <?php if ($is_logged_in && $profile_user_id == $current_user_id): ?>
                    <a href="raketko-feed.php" class="btn btn-outline" style="background: white; color: var(--primary-blue);">
                        <i class="fas fa-home"></i> Back to Feed
                    </a>
                <?php elseif ($is_logged_in): ?>
                    <?php if ($connection_status === 'accepted'): ?>
                        <button class="btn btn-outline" style="background: white; color: var(--primary-blue);" disabled>
                            <i class="fas fa-check"></i> Following
                        </button>
                    <?php elseif ($connection_status === 'pending'): ?>
                        <button class="btn btn-outline" style="background: white; color: var(--primary-blue);" disabled>
                            <i class="fas fa-clock"></i> Request Sent
                        </button>
                    <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="follow_user">
                            <input type="hidden" name="following_id" value="<?php echo $profile_user_id; ?>">
                            <button type="submit" class="btn btn-primary" style="background: white; color: var(--primary-blue);">
                                <i class="fas fa-user-plus"></i> Follow
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="background: white; color: var(--primary-blue);">
                        <i class="fas fa-sign-in-alt"></i> Login to Follow
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="raketko-profile-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
        <!-- Left Sidebar -->
        <div class="profile-sidebar">
            <!-- Profile Info -->
            <div class="panel" style="margin-bottom: 1.5rem;">
                <div class="section-header">
                    <span class="header-square"></span>
                    About
                </div>
                <div class="panel-body">
                    <?php if ($is_logged_in && $profile_user_id == $current_user_id): ?>
                        <button onclick="toggleEditProfile()" class="btn btn-outline btn-small" style="margin-bottom: 1rem;">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    <?php endif; ?>
                    
                    <div id="profileView">
                        <?php if (!empty($socialProfile['bio'])): ?>
                            <p style="margin-bottom: 1rem;"><?php echo nl2br(htmlspecialchars($socialProfile['bio'])); ?></p>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-style: italic;">No bio added yet.</p>
                        <?php endif; ?>
                        
                        <?php if (!empty($socialProfile['website'])): ?>
                            <div style="margin-bottom: 0.5rem;">
                                <i class="fas fa-globe"></i> 
                                <a href="<?php echo htmlspecialchars($socialProfile['website']); ?>" target="_blank" style="color: var(--primary-blue);">
                                    <?php echo htmlspecialchars($socialProfile['website']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($socialProfile['linkedin_url'])): ?>
                            <div style="margin-bottom: 0.5rem;">
                                <i class="fab fa-linkedin"></i> 
                                <a href="<?php echo htmlspecialchars($socialProfile['linkedin_url']); ?>" target="_blank" style="color: var(--primary-blue);">
                                    LinkedIn Profile
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($profile_user_id == $current_user_id): ?>
                        <div id="profileEdit" style="display: none;">
                            <form method="POST">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Headline</label>
                                    <input type="text" name="headline" value="<?php echo htmlspecialchars($socialProfile['headline'] ?? ''); ?>" class="form-control">
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Bio</label>
                                    <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($socialProfile['bio'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Location</label>
                                    <input type="text" name="location" value="<?php echo htmlspecialchars($socialProfile['location'] ?? ''); ?>" class="form-control">
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Website</label>
                                    <input type="url" name="website" value="<?php echo htmlspecialchars($socialProfile['website'] ?? ''); ?>" class="form-control">
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($socialProfile['linkedin_url'] ?? ''); ?>" class="form-control">
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Skills (comma-separated)</label>
                                    <input type="text" name="skills" value="<?php echo htmlspecialchars(implode(', ', json_decode($socialProfile['skills'] ?? '[]', true) ?? [])); ?>" class="form-control" placeholder="e.g., PHP, MySQL, Project Management">
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Interests (comma-separated)</label>
                                    <input type="text" name="interests" value="<?php echo htmlspecialchars(implode(', ', json_decode($socialProfile['interests'] ?? '[]', true) ?? [])); ?>" class="form-control" placeholder="e.g., Technology, Startups, Design">
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                    <button type="button" onclick="toggleEditProfile()" class="btn btn-outline">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats -->
            <div class="panel" style="margin-bottom: 1.5rem;">
                <div class="section-header">
                    <span class="header-square"></span>
                    Statistics
                </div>
                <div class="panel-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                        <div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-blue);"><?php echo $followers_count; ?></div>
                            <div style="font-size: 0.9rem; color: var(--text-muted);">Followers</div>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-blue);"><?php echo $following_count; ?></div>
                            <div style="font-size: 0.9rem; color: var(--text-muted);">Following</div>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-blue);"><?php echo count($posts); ?></div>
                            <div style="font-size: 0.9rem; color: var(--text-muted);">Posts</div>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-blue);">
                                <?php 
                                $total_engagement = array_sum(array_column($posts, 'likes_count')) + array_sum(array_column($posts, 'comments_count')) + array_sum(array_column($posts, 'shares_count'));
                                echo $total_engagement; 
                                ?>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-muted);">Engagement</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            <?php if (!empty($socialProfile['skills'])): ?>
                <div class="panel">
                    <div class="section-header">
                        <span class="header-square"></span>
                        Skills
                    </div>
                    <div class="panel-body">
                        <?php 
                        $skills = json_decode($socialProfile['skills'], true);
                        foreach ($skills as $skill): 
                        ?>
                            <span class="tag tag-blue" style="margin: 0.25rem; display: inline-block;">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="profile-main">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Tabs -->
            <div class="panel" style="margin-bottom: 1.5rem;">
                <div class="panel-body" style="padding: 0;">
                    <div style="display: flex; border-bottom: 1px solid var(--border-light);">
                        <button class="profile-tab active" onclick="showTab('posts')" style="flex: 1; padding: 1rem; border: none; background: none; cursor: pointer; border-bottom: 2px solid var(--primary-blue);">
                            <i class="fas fa-file-alt"></i> Posts
                        </button>
                        <button class="profile-tab" onclick="showTab('activity')" style="flex: 1; padding: 1rem; border: none; background: none; cursor: pointer;">
                            <i class="fas fa-history"></i> Activity
                        </button>
                        <button class="profile-tab" onclick="showTab('about')" style="flex: 1; padding: 1rem; border: none; background: none; cursor: pointer;">
                            <i class="fas fa-info-circle"></i> About
                        </button>
                    </div>
                </div>
            </div>

            <!-- Posts Tab -->
            <div id="posts-tab" class="tab-content">
                <?php if (empty($posts)): ?>
                    <div class="panel">
                        <div class="panel-body" style="text-align: center; padding: 3rem;">
                            <i class="fas fa-file-alt" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <h3 style="margin: 0; color: var(--text-dark);">No Posts Yet</h3>
                            <p style="margin: 1rem 0; color: var(--text-muted);">
                                <?php if ($profile_user_id == $current_user_id): ?>
                                    Start sharing your professional insights and experiences.
                                <?php else: ?>
                                    This user hasn't posted anything yet.
                                <?php endif; ?>
                            </p>
                            <?php if ($profile_user_id == $current_user_id): ?>
                                <a href="raketko-feed.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Your First Post
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="panel" style="margin-bottom: 1.5rem;">
                            <div class="panel-body">
                                <div style="margin-bottom: 1rem;">
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h4>
                                    <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">
                                        <?php echo ucfirst($post['post_type']); ?> • <?php echo timeAgo($post['created_at']); ?>
                                    </div>
                                    <div style="color: var(--text-dark); line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                    </div>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                                    <div style="display: flex; gap: 1rem;">
                                        <span style="color: var(--text-muted); font-size: 0.9rem;">
                                            <i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?>
                                        </span>
                                        <span style="color: var(--text-muted); font-size: 0.9rem;">
                                            <i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?>
                                        </span>
                                        <span style="color: var(--text-muted); font-size: 0.9rem;">
                                            <i class="fas fa-share"></i> <?php echo $post['shares_count']; ?>
                                        </span>
                                    </div>
                                    
                                    <a href="raketko-post.php?id=<?php echo $post['post_id']; ?>" class="btn btn-outline btn-small">
                                        View Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Activity Tab -->
            <div id="activity-tab" class="tab-content" style="display: none;">
                <div class="panel">
                    <div class="panel-body">
                        <h3 style="margin: 0 0 1rem 0; color: var(--text-dark);">Recent Activity</h3>
                        
                        <?php if (empty($recent_activity)): ?>
                            <p style="color: var(--text-muted);">No recent activity.</p>
                        <?php else: ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div style="padding: 1rem 0; border-bottom: 1px solid var(--border-light);">
                                    <div style="color: var(--text-dark); margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($activity['activity_type'] === 'post' ? $activity['title'] : $activity['activity_type']); ?>
                                    </div>
                                    <div style="color: var(--text-muted); font-size: 0.85rem;">
                                        <?php echo timeAgo($activity['created_at']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- About Tab -->
            <div id="about-tab" class="tab-content" style="display: none;">
                <div class="panel">
                    <div class="panel-body">
                        <h3 style="margin: 0 0 1rem 0; color: var(--text-dark);">About <?php echo htmlspecialchars($profile_user['full_name']); ?></h3>
                        
                        <div style="margin-bottom: 2rem;">
                            <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">Professional Summary</h4>
                            <?php if (!empty($socialProfile['bio'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($socialProfile['bio'])); ?></p>
                            <?php else: ?>
                                <p style="color: var(--text-muted); font-style: italic;">No bio available.</p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($socialProfile['skills'])): ?>
                            <div style="margin-bottom: 2rem;">
                                <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">Skills & Expertise</h4>
                                <div>
                                    <?php 
                                    $skills = json_decode($socialProfile['skills'], true);
                                    foreach ($skills as $skill): 
                                    ?>
                                        <span class="tag tag-blue" style="margin: 0.25rem; display: inline-block;">
                                            <?php echo htmlspecialchars($skill); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($socialProfile['interests'])): ?>
                            <div style="margin-bottom: 2rem;">
                                <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">Professional Interests</h4>
                                <div>
                                    <?php 
                                    $interests = json_decode($socialProfile['interests'], true);
                                    foreach ($interests as $interest): 
                                    ?>
                                        <span class="tag tag-green" style="margin: 0.25rem; display: inline-block;">
                                            <?php echo htmlspecialchars($interest); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">Contact Information</h4>
                            <div style="color: var(--text-dark);">
                                <div style="margin-bottom: 0.5rem;">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($socialProfile['location'] ?? ($profile_user['city'] . ', ' . $profile_user['province'])); ?>
                                </div>
                                <?php if (!empty($socialProfile['website'])): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Website:</strong> 
                                        <a href="<?php echo htmlspecialchars($socialProfile['website']); ?>" target="_blank" style="color: var(--primary-blue);">
                                            <?php echo htmlspecialchars($socialProfile['website']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($socialProfile['linkedin_url'])): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>LinkedIn:</strong> 
                                        <a href="<?php echo htmlspecialchars($socialProfile['linkedin_url']); ?>" target="_blank" style="color: var(--primary-blue);">
                                            View Profile
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleEditProfile() {
    const viewMode = document.getElementById('profileView');
    const editMode = document.getElementById('profileEdit');
    
    viewMode.style.display = viewMode.style.display === 'none' ? 'block' : 'none';
    editMode.style.display = editMode.style.display === 'none' ? 'block' : 'none';
}

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.profile-tab').forEach(button => {
        button.style.borderBottom = 'none';
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to clicked button
    event.target.style.borderBottom = '2px solid var(--primary-blue)';
}
</script>

<?php require_once 'includes/footer.php'; ?>
