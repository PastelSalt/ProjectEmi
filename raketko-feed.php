<?php
/**
 * RaketKo Social Feed
 * Professional Social Media for RaketGo Users
 */
$page_title = 'RaketKo - Professional Network';
require_once 'config/config.php';
require_once 'includes/header.php';

$is_logged_in = isLoggedIn();

$conn = getDBConnection();
$user_id = $is_logged_in ? getCurrentUserId() : null;
$user_type = $is_logged_in ? getCurrentUserType() : null;

// Initialize variables
$success = '';
$error = '';

// Handle form submissions (only for logged-in users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
    $action = $_POST['action'] ?? '';
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'create_post':
                $title = sanitizeInput($_POST['title'] ?? '');
                $content = sanitizeMultilineInput($_POST['content'] ?? '');
                $post_type = sanitizeInput($_POST['post_type'] ?? 'career_update');
                $visibility = sanitizeInput($_POST['visibility'] ?? 'public');
                
                if (empty($title) || empty($content)) {
                    $error = 'Title and content are required.';
                } else {
                    // Handle media uploads
                    $media_urls = [];
                    if (!empty($_FILES['media']['name'][0])) {
                        $media_urls = handleMediaUpload($_FILES['media']);
                    }
                    
                    // Extract hashtags and mentions
                    $hashtags = extractHashtags($content);
                    $mentions = extractMentions($content);
                    
                    $result = executeQuery($conn, 
                        "INSERT INTO social_posts (user_id, post_type, title, content, media_urls, hashtags, mentions, visibility) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$user_id, $post_type, $title, $content, json_encode($media_urls), json_encode($hashtags), json_encode($mentions), $visibility],
                        'ssssssss'
                    );
                    
                    if ($result) {
                        $success = 'Post created successfully!';
                        // Update trending topics
                        updateTrendingTopics($hashtags);
                        // Create activity feed entries
                        createActivityFeed($user_id, 'post', $conn->insert_id, $conn);
                    } else {
                        $error = 'Failed to create post. Please try again.';
                    }
                }
                break;

            case 'add_comment':
                $post_id = (int)($_POST['post_id'] ?? 0);
                $comment_content = sanitizeMultilineInput($_POST['comment_content'] ?? '');

                if ($post_id <= 0 || $comment_content === '') {
                    $error = 'Comment cannot be empty.';
                } else {
                    if (strlen($comment_content) > 1000) {
                        $comment_content = substr($comment_content, 0, 1000);
                    }

                    $mentions = extractMentions($comment_content);
                    $result = executeQuery(
                        $conn,
                        "INSERT INTO social_post_comments (post_id, user_id, content, mentions) VALUES (?, ?, ?, ?)",
                        [$post_id, $user_id, $comment_content, json_encode($mentions)],
                        'iiss'
                    );

                    if ($result) {
                        executeQuery($conn, "UPDATE social_posts SET comments_count = comments_count + 1 WHERE post_id = ?", [$post_id], 'i');
                        createSocialNotification($post_id, 'comment', $user_id, $conn);
                        $success = 'Comment posted.';
                    } else {
                        $error = 'Failed to post comment. Please try again.';
                    }
                }
                break;

            case 'share_post':
                $post_id = (int)($_POST['post_id'] ?? 0);
                $share_type = sanitizeInput($_POST['share_type'] ?? 'repost');
                $share_comment = sanitizeMultilineInput($_POST['share_comment'] ?? '');

                if ($post_id <= 0) {
                    $error = 'Invalid post.';
                } else {
                    $existing = fetchOne(
                        $conn,
                        "SELECT share_id FROM social_post_shares WHERE post_id = ? AND user_id = ?",
                        [$post_id, $user_id],
                        'ii'
                    );

                    if ($existing) {
                        $error = 'You already shared this post.';
                    } else {
                        if (strlen($share_comment) > 500) {
                            $share_comment = substr($share_comment, 0, 500);
                        }

                        $result = executeQuery(
                            $conn,
                            "INSERT INTO social_post_shares (post_id, user_id, share_type, share_comment) VALUES (?, ?, ?, ?)",
                            [$post_id, $user_id, $share_type, $share_comment],
                            'iiss'
                        );

                        if ($result) {
                            executeQuery($conn, "UPDATE social_posts SET shares_count = shares_count + 1 WHERE post_id = ?", [$post_id], 'i');
                            createSocialNotification($post_id, 'share', $user_id, $conn);
                            $success = 'Post shared.';
                        } else {
                            $error = 'Failed to share post. Please try again.';
                        }
                    }
                }
                break;
                
            case 'like_post':
                $post_id = (int)($_POST['post_id'] ?? 0);
                if ($post_id > 0) {
                    $existing = fetchOne($conn, "SELECT * FROM social_post_likes WHERE post_id = ? AND user_id = ?", [$post_id, $user_id], 'ii');
                    if (!$existing) {
                        executeQuery($conn, "INSERT INTO social_post_likes (post_id, user_id) VALUES (?, ?)", [$post_id, $user_id], 'ii');
                        executeQuery($conn, "UPDATE social_posts SET likes_count = likes_count + 1 WHERE post_id = ?", [$post_id], 'i');
                        // Create notification
                        createSocialNotification($post_id, 'like', $user_id, $conn);
                    }
                }
                break;
                
            case 'unlike_post':
                $post_id = (int)($_POST['post_id'] ?? 0);
                if ($post_id > 0) {
                    executeQuery($conn, "DELETE FROM social_post_likes WHERE post_id = ? AND user_id = ?", [$post_id, $user_id], 'ii');
                    executeQuery($conn, "UPDATE social_posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE post_id = ?", [$post_id], 'i');
                }
                break;
                
            case 'follow_user':
                $following_id = (int)($_POST['following_id'] ?? 0);
                if ($following_id > 0 && $following_id != $user_id) {
                    $existing = fetchOne($conn, "SELECT * FROM social_connections WHERE follower_id = ? AND following_id = ?", [$user_id, $following_id], 'ii');
                    if (!$existing) {
                        executeQuery($conn, "INSERT INTO social_connections (follower_id, following_id, status) VALUES (?, ?, 'accepted')", [$user_id, $following_id], 'ii');
                        createSocialNotification($following_id, 'follow', $user_id, $conn);
                    }
                }
                break;
                
            case 'unfollow_user':
                $following_id = (int)($_POST['following_id'] ?? 0);
                if ($following_id > 0) {
                    executeQuery($conn, "DELETE FROM social_connections WHERE follower_id = ? AND following_id = ?", [$user_id, $following_id], 'ii');
                }
                break;
        }
    }
}

// Get user's social profile (only if logged in)
$socialProfile = null;
if ($is_logged_in) {
    // Get user info from main users table first
    $userInfo = fetchOne($conn, "SELECT full_name, profile_picture FROM users WHERE user_id = ?", [$user_id], 'i');
    
    $socialProfile = fetchOne($conn, "SELECT * FROM social_profiles WHERE user_id = ?", [$user_id], 'i');
    if (!$socialProfile) {
        // Create social profile if it doesn't exist
        executeQuery($conn, 
            "INSERT INTO social_profiles (user_id, bio, headline) VALUES (?, '', '')", 
            [$user_id], 'i'
        );
        $socialProfile = fetchOne($conn, "SELECT * FROM social_profiles WHERE user_id = ?", [$user_id], 'i');
    }
    
    // Merge user info with social profile
    if ($userInfo) {
        $socialProfile['full_name'] = $userInfo['full_name'];
        if (empty($socialProfile['profile_picture'])) {
            $socialProfile['profile_picture'] = $userInfo['profile_picture'];
        }
    }
}

// Get feed posts (public posts for everyone, additional posts for logged-in users)
$posts = getSocialFeed($user_id, $conn, $is_logged_in);

// Get trending topics
$trendingTopics = fetchAll($conn, 
    "SELECT * FROM trending_topics WHERE is_trending = TRUE ORDER BY trending_score DESC LIMIT 10", 
    [], ''
);

// Get suggested connections
$suggestedConnections = getSuggestedConnections($user_id, $conn);

function getSocialFeed($user_id, $conn, $is_logged_in) {
    if ($is_logged_in) {
        $sql = "SELECT sp.*, u.full_name, u.profile_picture, u.user_type, 
            CASE WHEN spl.user_id IS NOT NULL THEN TRUE ELSE FALSE END as user_liked,
            (SELECT COUNT(*) FROM social_post_likes WHERE post_id = sp.post_id) as likes_count,
            (SELECT COUNT(*) FROM social_post_comments WHERE post_id = sp.post_id) as comment_count,
            (SELECT COUNT(*) FROM social_post_shares WHERE post_id = sp.post_id) as shares_count
            FROM social_posts sp
                JOIN users u ON sp.user_id = u.user_id
                LEFT JOIN social_post_likes spl ON sp.post_id = spl.post_id AND spl.user_id = ?
                WHERE sp.visibility = 'public' 
                   OR (sp.visibility = 'connections' AND sp.user_id IN (
                       SELECT following_id FROM social_connections WHERE follower_id = ? AND status = 'accepted'
                   ))
                   OR sp.user_id = ?
                ORDER BY sp.is_pinned DESC, sp.created_at DESC
                LIMIT 20";
        
        return fetchAll($conn, $sql, [$user_id, $user_id, $user_id], 'iii');
    } else {
        // Public users only see public posts
        $sql = "SELECT sp.*, u.full_name, u.profile_picture, u.user_type, 
            FALSE as user_liked,
            (SELECT COUNT(*) FROM social_post_likes WHERE post_id = sp.post_id) as likes_count,
            (SELECT COUNT(*) FROM social_post_comments WHERE post_id = sp.post_id) as comment_count,
            (SELECT COUNT(*) FROM social_post_shares WHERE post_id = sp.post_id) as shares_count
            FROM social_posts sp
                JOIN users u ON sp.user_id = u.user_id
                WHERE sp.visibility = 'public'
                ORDER BY sp.is_pinned DESC, sp.created_at DESC
                LIMIT 20";
        
        return fetchAll($conn, $sql, [], '');
    }
}

function getSuggestedConnections($user_id, $conn) {
    if (!$user_id) return []; // No suggestions for non-logged users
    
    // Suggest users based on similar skills, location, or mutual connections
    $sql = "SELECT DISTINCT u.user_id, u.full_name, u.profile_picture, u.user_type, u.city, u.province,
            (SELECT COUNT(*) FROM social_connections WHERE follower_id = ? AND following_id = u.user_id) as is_following
            FROM users u
            WHERE u.user_id != ? 
              AND u.user_id NOT IN (SELECT following_id FROM social_connections WHERE follower_id = ?)
              AND u.account_status = 'active'
            ORDER BY RAND()
            LIMIT 5";
    
    return fetchAll($conn, $sql, [$user_id, $user_id, $user_id], 'iii');
}

function extractHashtags($content) {
    preg_match_all('/#(\w+)/', $content, $matches);
    return array_unique($matches[1]);
}

function extractMentions($content) {
    preg_match_all('/@(\w+)/', $content, $matches);
    return array_unique($matches[1]);
}

function handleMediaUpload($files) {
    $media_urls = [];
    $upload_dir = 'uploads/social_media/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'pdf'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $filename = uniqid('media_', true) . '.' . $file_ext;
                $filepath = $upload_dir . $filename;

                $isImage = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                if ($isImage) {
                    if (saveUploadedImage($files['tmp_name'][$key], $filepath, 1600, 1600)) {
                        $media_urls[] = $filepath;
                    }
                } else {
                    if (move_uploaded_file($files['tmp_name'][$key], $filepath)) {
                        $media_urls[] = $filepath;
                    }
                }
            }
        }
    }
    
    return $media_urls;
}

function updateTrendingTopics($hashtags) {
    global $conn;
    
    foreach ($hashtags as $hashtag) {
        $hashtag = '#' . $hashtag;
        executeQuery($conn, 
            "INSERT INTO trending_topics (hashtag, usage_count) VALUES (?, 1)
             ON DUPLICATE KEY UPDATE usage_count = usage_count + 1, updated_at = CURRENT_TIMESTAMP",
            [$hashtag], 's'
        );
    }
}

function createSocialNotification($target_id, $type, $actor_id, $conn) {
    $post = fetchOne($conn, "SELECT user_id, title FROM social_posts WHERE post_id = ?", [$target_id], 'i');
    if ($post && $post['user_id'] != $actor_id) {
        $message = getNotificationMessage($type, $actor_id, $post['title']);
        executeQuery($conn, 
            "INSERT INTO social_notifications (user_id, type, actor_id, target_id, target_type, message) 
             VALUES (?, ?, ?, ?, 'post', ?)",
            [$post['user_id'], $type, $actor_id, $target_id, $message], 'siiss'
        );
    }
}

function getNotificationMessage($type, $actor_id, $target_title) {
    $actor = fetchOne($GLOBALS['conn'], "SELECT full_name FROM users WHERE user_id = ?", [$actor_id], 'i');
    $actor_name = $actor['full_name'] ?? 'Someone';
    
    switch ($type) {
        case 'like':
            return "$actor_name liked your post: $target_title";
        case 'comment':
            return "$actor_name commented on your post: $target_title";
        case 'share':
            return "$actor_name shared your post: $target_title";
        case 'follow':
            return "$actor_name started following you";
        default:
            return "$actor_name interacted with your content";
    }
}

function createActivityFeed($user_id, $activity_type, $target_id, $conn) {
    $followers = fetchAll($conn, 
        "SELECT follower_id FROM social_connections WHERE following_id = ? AND status = 'accepted'", 
        [$user_id], 'i'
    );
    
    foreach ($followers as $follower) {
        executeQuery($conn, 
            "INSERT INTO user_activity_feed (user_id, activity_type, actor_id, target_id) 
             VALUES (?, ?, ?, ?)",
            [$follower['follower_id'], $activity_type, $user_id, $target_id], 'iiii'
        );
    }
}

?>

<div class="container">
    <div class="raketko-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 style="margin: 0; color: var(--text-dark); font-size: 2rem;">
                    <span style="color: var(--primary-blue);">Raket</span>Ko
                </h1>
                <p style="margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 1rem;">
                    Professional Network for Filipino Career Growth
                </p>
            </div>
            <div class="nav-right" style="display: flex; gap: 1rem;">
                <?php if ($is_logged_in): ?>
                    <a href="raketko-profile.php?id=<?php echo $user_id; ?>" class="btn btn-outline">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="raketko-notifications.php" class="btn btn-outline">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login to Participate
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="raketko-layout" style="display: grid; grid-template-columns: 280px 1fr 320px; gap: 1.5rem; max-width: 1280px; margin: 0 auto;">
        
        <!-- Main Feed Content -->
        <div style="grid-column: 1 / -1; display: grid; grid-template-columns: 280px 1fr 320px; gap: 1.5rem;">
            
            <!-- Left Sidebar - Navigation -->
            <div class="raketko-sidebar">
                <!-- User Profile Mini Card -->
                <div class="panel" style="margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                            <?php if (!empty($socialProfile['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($socialProfile['profile_picture']); ?>" 
                                     alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--sana-red); color: white; 
                                            display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: bold;">
                                    <?php echo mb_strtoupper(mb_substr($socialProfile['full_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; color: var(--text-dark); font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($socialProfile['full_name'] ?? 'User'); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    @<?php echo strtolower(str_replace(' ', '', $socialProfile['full_name'] ?? 'user')); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div style="display: flex; justify-content: space-around; padding: 0.75rem 0; border-top: 1px solid var(--border-light);">
                            <div style="text-align: center;">
                                <div style="font-weight: bold; color: var(--text-dark); font-size: 0.9rem;"><?php echo $socialProfile['posts_count'] ?? 0; ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Posts</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-weight: bold; color: var(--text-dark); font-size: 0.9rem;"><?php echo $socialProfile['followers_count'] ?? 0; ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Followers</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-weight: bold; color: var(--text-dark); font-size: 0.9rem;"><?php echo $socialProfile['following_count'] ?? 0; ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Following</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="panel" style="border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 0.5rem;">
                        <nav style="display: flex; flex-direction: column;">
                            <a href="raketko-feed.php" class="nav-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 12px; text-decoration: none; color: var(--text-dark); background: var(--sana-pink-bg); margin-bottom: 0.25rem;">
                                <i class="fas fa-home" style="width: 20px; text-align: center;"></i>
                                <span style="font-weight: 600;">Home</span>
                            </a>
                            <a href="raketko-profile.php?id=<?php echo $user_id; ?>" class="nav-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 12px; text-decoration: none; color: var(--text-dark); margin-bottom: 0.25rem;">
                                <i class="fas fa-user" style="width: 20px; text-align: center;"></i>
                                <span>Profile</span>
                            </a>
                            <a href="raketko-notifications.php" class="nav-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 12px; text-decoration: none; color: var(--text-dark); margin-bottom: 0.25rem;">
                                <i class="fas fa-bell" style="width: 20px; text-align: center;"></i>
                                <span>Notifications</span>
                            </a>
                            <a href="messages.php" class="nav-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 12px; text-decoration: none; color: var(--text-dark); margin-bottom: 0.25rem;">
                                <i class="fas fa-envelope" style="width: 20px; text-align: center;"></i>
                                <span>Messages</span>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Feed -->
            <div class="raketko-main">
                <!-- Prominent Posting UI -->
                <?php if ($is_logged_in): ?>
                <div class="panel" style="margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 1rem;">
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <?php if (!empty($socialProfile['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($socialProfile['profile_picture']); ?>" 
                                     alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--sana-red); color: white; 
                                            display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: bold; flex-shrink: 0;">
                                    <?php echo mb_strtoupper(mb_substr($socialProfile['full_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <div onclick="togglePostForm()" style="background: var(--sana-pink-bg); border: 1px solid var(--sana-pink); border-radius: 24px; padding: 0.75rem 1rem; cursor: pointer; transition: all 0.2s ease;">
                                    <span style="color: var(--charcoal-mid); font-size: 0.9rem;">What's happening in your career?</span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="postForm" style="display: none; margin-top: 1rem;">
                            <form method="POST" enctype="multipart/form-data">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="create_post">
                                
                                <div style="display: flex; gap: 0.75rem; align-items: flex-start; margin-bottom: 1rem;">
                                    <?php if (!empty($socialProfile['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($socialProfile['profile_picture']); ?>" 
                                             alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--sana-red); color: white; 
                                                    display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: bold; flex-shrink: 0;">
                                            <?php echo mb_strtoupper(mb_substr($socialProfile['full_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <input type="text" name="title" class="form-control" placeholder="Add a short title" maxlength="120" required
                                               style="margin-bottom: 0.5rem; border: 1px solid var(--border-light); border-radius: 10px; padding: 0.5rem 0.75rem; font-size: 0.95rem;">
                                        <textarea name="content" placeholder="Share your professional insights..." class="form-control" rows="3" style="border: none; background: transparent; padding: 0.5rem 0; resize: none; font-size: 1rem;" required></textarea>
                                        <input type="file" id="postMedia" name="media[]" accept="image/*,video/mp4,video/quicktime" multiple style="display: none;">
                                        <div id="postMediaPreview" style="display: none; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;"></div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-top: 1px solid var(--border-light);">
                                    <div style="display: flex; gap: 1rem;">
                                        <button type="button" class="post-action-btn" onclick="document.getElementById('postMedia').click();" aria-label="Add photos or video"
                                                style="background: none; border: none; color: var(--sana-red); cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.4rem;">
                                            <i class="fas fa-image"></i>
                                            <span style="font-size: 0.8rem;">Media</span>
                                        </button>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <select name="post_type" class="form-control" style="border: 1px solid var(--sana-pink); border-radius: 8px; padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                            <option value="career_update">Career Update</option>
                                            <option value="achievement">Achievement</option>
                                            <option value="insight">Professional Insight</option>
                                            <option value="professional_tip">Professional Tip</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary" style="background: var(--sana-red); border: none; border-radius: 20px; padding: 0.5rem 1.5rem; font-weight: 600;">
                                            Post
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="panel" style="margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 2rem; text-align: center;">
                        <i class="fas fa-lock" style="font-size: 3rem; color: var(--sana-pink); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Join the Professional Network</h3>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Connect with professionals and share your career insights</p>
                        <a href="login.php" class="btn btn-primary" style="background: var(--sana-red); border: none; border-radius: 20px; padding: 0.75rem 2rem; font-weight: 600;">
                            Sign In to Post
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Feed Posts -->
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1rem; border-radius: 12px; background: var(--sana-pink-bg); color: var(--sana-red-dark); border: 1px solid var(--sana-pink);">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1rem; border-radius: 12px; background: #FFF0F5; color: #D62E3E; border: 1px solid #FFD1DC;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                    <div class="panel" style="border-radius: 16px; overflow: hidden;">
                        <div class="panel-body" style="text-align: center; padding: 3rem;">
                            <i class="fas fa-users" style="font-size: 3rem; color: var(--sana-pink); margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; color: var(--text-dark);">Welcome to RaketKo!</h3>
                        <p style="margin: 1rem 0; color: var(--text-muted);">
                            Start building your professional network by creating your first post.
                        </p>
                        <button onclick="togglePostForm()" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Post
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="panel" style="margin-bottom: 1rem; border-radius: 16px; overflow: hidden; transition: all 0.2s ease;">
                        <div class="panel-body" style="padding: 1rem;">
                            <!-- Post Header -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                                    <?php if (!empty($post['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['profile_picture']); ?>" 
                                             alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--sana-red); color: white; 
                                                    display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: bold; flex-shrink: 0;">
                                            <?php echo mb_strtoupper(mb_substr($post['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="font-weight: 600; color: var(--text-dark); font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($post['full_name']); ?>
                                            </div>
                                            <?php if ($post['is_pinned']): ?>
                                                <i class="fas fa-thumbtack" style="color: var(--sana-red); font-size: 0.7rem;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            @<?php echo strtolower(str_replace(' ', '', $post['full_name'])); ?> • <?php echo timeAgo($post['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span class="tag tag-gray" style="font-size: 0.65rem;">
                                        <?php echo ucfirst($post['post_type']); ?>
                                    </span>
                                    <?php if ($post['visibility'] === 'connections'): ?>
                                        <i class="fas fa-lock" style="color: var(--text-muted); font-size: 0.7rem;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Post Content -->
                            <div style="margin-bottom: 1rem;">
                                <?php if (!empty($post['title'])): ?>
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 1.1rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h4>
                                <?php endif; ?>
                                <div style="color: var(--text-dark); line-height: 1.5; font-size: 0.95rem;">
                                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                </div>
                                
                                <?php if (!empty($post['media_urls'])): ?>
                                    <div style="margin-top: 0.75rem;">
                                        <?php 
                                        $media_urls = json_decode($post['media_urls'], true);
                                        foreach ($media_urls as $media_url): 
                                        ?>
                                            <?php if (in_array(pathinfo($media_url, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                                <img src="<?php echo htmlspecialchars($media_url); ?>" 
                                                     alt="Post media" style="max-width: 100%; border-radius: 12px; margin-bottom: 0.5rem;">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Post Actions -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0 0 0; border-top: 1px solid var(--border-light);">
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($is_logged_in): ?>
                                        <form method="POST" style="display: inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="<?php echo $post['user_liked'] ? 'unlike_post' : 'like_post'; ?>">
                                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                            <button type="submit" class="post-action-btn" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: none; border: none; color: <?php echo $post['user_liked'] ? 'var(--sana-red)' : 'var(--text-muted)'; ?>; cursor: pointer; border-radius: 20px; transition: all 0.2s ease;">
                                                <i class="fas fa-heart"></i>
                                                <span style="font-size: 0.85rem;"><?php echo $post['likes_count']; ?></span>
                                            </button>
                                        </form>
                                        
                                        <button class="post-action-btn" onclick="toggleComments(<?php echo $post['post_id']; ?>)" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: none; border: none; color: var(--text-muted); cursor: pointer; border-radius: 20px; transition: all 0.2s ease;">
                                            <i class="fas fa-comment"></i>
                                            <span style="font-size: 0.85rem;"><?php echo $post['comment_count']; ?></span>
                                        </button>
                                        
                                        <form method="POST" style="display: inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="share_post">
                                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                            <button type="submit" class="post-action-btn" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: none; border: none; color: var(--text-muted); cursor: pointer; border-radius: 20px; transition: all 0.2s ease;">
                                                <i class="fas fa-share"></i>
                                                <span style="font-size: 0.85rem;"><?php echo $post['shares_count']; ?></span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="post-action-btn" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: none; border: none; color: var(--text-muted); text-decoration: none; border-radius: 20px; transition: all 0.2s ease;">
                                            <i class="fas fa-heart"></i>
                                            <span style="font-size: 0.85rem;"><?php echo $post['likes_count']; ?></span>
                                        </a>
                                        
                                        <a href="login.php" class="post-action-btn" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: none; border: none; color: var(--text-muted); text-decoration: none; border-radius: 20px; transition: all 0.2s ease;">
                                            <i class="fas fa-comment"></i>
                                            <span style="font-size: 0.85rem;"><?php echo $post['comment_count']; ?></span>
                                        </a>
                                        
                                        <a href="login.php" class="post-action-btn" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: none; border: none; color: var(--text-muted); text-decoration: none; border-radius: 20px; transition: all 0.2s ease;">
                                            <i class="fas fa-share"></i>
                                            <span style="font-size: 0.85rem;"><?php echo $post['shares_count']; ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($is_logged_in && $post['user_id'] != $user_id): ?>
                                        <a href="messages.php?user=<?php echo $post['user_id']; ?>" class="feed-message-btn">
                                            <i class="fas fa-envelope"></i> Message
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php
                            $comments = fetchAll(
                                $conn,
                                "SELECT spc.*, u.full_name, u.profile_picture
                                 FROM social_post_comments spc
                                 JOIN users u ON spc.user_id = u.user_id
                                 WHERE spc.post_id = ?
                                 ORDER BY spc.created_at DESC
                                 LIMIT 5",
                                [$post['post_id']],
                                'i'
                            );
                            ?>
                            <div id="comments-<?php echo $post['post_id']; ?>" style="display: none; margin-top: 0.75rem; border-top: 1px solid var(--border-light); padding-top: 0.75rem;">
                                <?php if ($is_logged_in): ?>
                                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: flex-start; margin-bottom: 0.75rem;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="add_comment">
                                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                        <textarea name="comment_content" class="form-control" rows="2" placeholder="Write a comment..." required
                                                  style="flex: 1; border-radius: 10px; font-size: 0.85rem; padding: 0.5rem;"></textarea>
                                        <button type="submit" class="btn btn-primary btn-small" style="align-self: flex-end;">
                                            Comment
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline btn-small" style="text-decoration: none;">
                                        Sign in to comment
                                    </a>
                                <?php endif; ?>

                                <?php if (empty($comments)): ?>
                                    <div class="text-muted" style="font-size: 0.8rem;">No comments yet.</div>
                                <?php else: ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.6rem;">
                                            <?php if (!empty($comment['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--sana-red); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">
                                                    <?php echo mb_strtoupper(mb_substr($comment['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <strong style="font-size: 0.8rem; color: var(--text-dark);">
                                                        <?php echo htmlspecialchars($comment['full_name']); ?>
                                                    </strong>
                                                    <span style="font-size: 0.7rem; color: var(--text-muted);">
                                                        <?php echo timeAgo($comment['created_at']); ?>
                                                    </span>
                                                </div>
                                                <div style="font-size: 0.85rem; color: var(--text-dark);">
                                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>

            <!-- Right Sidebar -->
            <div class="raketko-right-sidebar">
                <!-- Search -->
                <div class="panel" style="margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 1rem;">
                        <div style="position: relative;">
                            <input type="text" placeholder="Search RaketKo..." class="form-control" style="padding-left: 2.5rem; border-radius: 20px; border: 1px solid var(--sana-pink);">
                            <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        </div>
                    </div>
                </div>

                <!-- Trending Topics -->
                <?php if (!empty($trendingTopics)): ?>
                <div class="panel" style="margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 1rem;">
                        <h3 style="margin: 0 0 1rem 0; color: var(--text-dark); font-size: 1.1rem; font-weight: 600;">
                            <i class="fas fa-fire" style="color: var(--sana-red); margin-right: 0.5rem;"></i>
                            Trending Topics
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <?php foreach ($trendingTopics as $topic): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border-light);">
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-dark); font-size: 0.9rem;">
                                            #<?php echo htmlspecialchars($topic['topic_name'] ?? $topic['hashtag'] ?? 'Topic'); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            <?php echo ($topic['post_count'] ?? $topic['usage_count'] ?? '0'); ?> posts
                                        </div>
                                    </div>
                                    <i class="fas fa-arrow-right" style="color: var(--sana-red); font-size: 0.7rem;"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Who to Follow -->
                <div class="panel" style="border-radius: 16px; overflow: hidden;">
                    <div class="panel-body" style="padding: 1rem;">
                        <h3 style="margin: 0 0 1rem 0; color: var(--text-dark); font-size: 1.1rem; font-weight: 600;">
                            <i class="fas fa-user-plus" style="color: var(--sana-red); margin-right: 0.5rem;"></i>
                            Who to Follow
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php if (!empty($suggestedConnections)): ?>
                                <?php foreach (array_slice($suggestedConnections, 0, 3) as $user): ?>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0;">
                                        <?php if (!empty($user['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                                 alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--sana-red); color: white; 
                                                        display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;">
                                                <?php echo mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-weight: 600; color: var(--text-dark); font-size: 0.85rem;">
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </div>
                                            <div style="font-size: 0.7rem; color: var(--text-muted);">
                                                <?php echo htmlspecialchars($user['user_type']); ?>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-small" style="background: var(--sana-red); border: none; border-radius: 16px; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                            Follow
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 1rem;">
                                    <i class="fas fa-users" style="font-size: 2rem; color: var(--sana-pink); margin-bottom: 0.5rem;"></i>
                                    <p style="color: var(--text-muted); font-size: 0.8rem;">No suggestions yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePostForm() {
    const form = document.getElementById('postForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    if (!commentsSection) {
        return;
    }
    commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const mediaInput = document.getElementById('postMedia');
    const preview = document.getElementById('postMediaPreview');

    if (!mediaInput || !preview) {
        return;
    }

    mediaInput.addEventListener('change', function() {
        preview.innerHTML = '';

        if (!mediaInput.files || mediaInput.files.length === 0) {
            preview.style.display = 'none';
            return;
        }

        preview.style.display = 'flex';

        Array.from(mediaInput.files).forEach(function(file) {
            if (file.type && file.type.indexOf('image/') === 0) {
                const img = document.createElement('img');
                img.alt = 'Selected media';
                img.style.width = '88px';
                img.style.height = '88px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '10px';
                img.style.border = '1px solid var(--border-light)';

                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                preview.appendChild(img);
            } else {
                const badge = document.createElement('div');
                badge.textContent = file.name;
                badge.style.fontSize = '0.75rem';
                badge.style.padding = '0.4rem 0.5rem';
                badge.style.border = '1px solid var(--border-light)';
                badge.style.borderRadius = '10px';
                preview.appendChild(badge);
            }
        });
    });
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
