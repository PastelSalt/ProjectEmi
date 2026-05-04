<?php
/**
 * RaketKo + RaketGo Integration Functions
 * Cross-platform functionality and unified user experience
 */

/**
 * Sync user profile between RaketGo and RaketKo
 */
function syncUserProfile($user_id, $conn) {
    // Get user data from RaketGo
    $user = fetchOne($conn, "SELECT * FROM users WHERE user_id = ?", [$user_id], 'i');
    if (!$user) return false;
    
    // Get or create social profile
    $socialProfile = fetchOne($conn, "SELECT * FROM social_profiles WHERE user_id = ?", [$user_id], 'i');
    
    if (!$socialProfile) {
        // Create social profile from RaketGo data
        executeQuery($conn, 
            "INSERT INTO social_profiles (user_id, bio, headline, location, website, linkedin_url) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $user_id,
                $user['bio'] ?? '',
                generateDefaultHeadline($user['user_type']),
                generateLocationString($user['city'], $user['province']),
                $user['website'] ?? '',
                extractLinkedInFromSocialLinks($user['social_links'] ?? '')
            ],
            'isssss'
        );
        
        $socialProfile = fetchOne($conn, "SELECT * FROM social_profiles WHERE user_id = ?", [$user_id], 'i');
    } else {
        // Update social profile with RaketGo data if sync is enabled
        if ($socialProfile['raketgo_profile_sync']) {
            executeQuery($conn, 
                "UPDATE social_profiles SET bio = ?, headline = ?, location = ?, website = ?, linkedin_url = ? 
                 WHERE user_id = ?",
                [
                    $user['bio'] ?? $socialProfile['bio'],
                    $socialProfile['headline'] ?: generateDefaultHeadline($user['user_type']),
                    $socialProfile['location'] ?: generateLocationString($user['city'], $user['province']),
                    $user['website'] ?? $socialProfile['website'],
                    extractLinkedInFromSocialLinks($user['social_links'] ?? '') ?: $socialProfile['linkedin_url'],
                    $user_id
                ],
                'sssssi'
            );
        }
    }
    
    // Update users table with social profile reference
    executeQuery($conn, "UPDATE users SET social_profile_id = ? WHERE user_id = ?", [$socialProfile['profile_id'], $user_id], 'ii');
    
    return $socialProfile;
}

/**
 * Log cross-platform activity
 */
function logCrossPlatformActivity($user_id, $platform, $activity_type, $target_id = null, $target_type = null, $activity_data = [], $conn) {
    executeQuery($conn, 
        "INSERT INTO cross_platform_activities (user_id, platform, activity_type, target_id, target_type, activity_data) 
         VALUES (?, ?, ?, ?, ?, ?)",
        [$user_id, $platform, $activity_type, $target_id, $target_type, json_encode($activity_data)],
        'isssss'
    );
}

/**
 * Create unified notification
 */
function createUnifiedNotification($user_id, $platform, $type, $actor_id, $title, $message, $target_id = null, $target_type = null, $action_url = null, $priority = 'medium', $conn) {
    executeQuery($conn, 
        "INSERT INTO unified_notifications (user_id, platform, type, actor_id, title, message, target_id, target_type, action_url, priority) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$user_id, $platform, $type, $actor_id, $title, $message, $target_id, $target_type, $action_url, $priority],
        'isssssssss'
    );
}

/**
 * Get unified user profile data
 */
function getUnifiedUserProfile($user_id, $conn) {
    return fetchOne($conn, 
        "SELECT * FROM unified_user_profile WHERE user_id = ?", 
        [$user_id], 'i'
    );
}

/**
 * Get user's cross-platform activities
 */
function getCrossPlatformActivities($user_id, $limit = 10, $conn) {
    return fetchAll($conn, 
        "SELECT cpa.*, u.full_name as actor_name, u.profile_picture as actor_picture
         FROM cross_platform_activities cpa
         LEFT JOIN users u ON cpa.actor_id = u.user_id
         WHERE cpa.user_id = ?
         ORDER BY cpa.created_at DESC
         LIMIT ?", 
        [$user_id, $limit], 'ii'
    );
}

/**
 * Get unified notifications for user
 */
function getUnifiedNotifications($user_id, $limit = 20, $unread_only = false, $conn) {
    $sql = "SELECT un.*, u.full_name as actor_name, u.profile_picture as actor_picture
            FROM unified_notifications un
            LEFT JOIN users u ON un.actor_id = u.user_id
            WHERE un.user_id = ?";
    $params = [$user_id];
    
    if ($unread_only) {
        $sql .= " AND un.is_read = FALSE";
    }
    
    $sql .= " ORDER BY un.priority DESC, un.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    return fetchAll($conn, $sql, $params);
}

/**
 * Share content across platforms
 */
function shareContentAcrossPlatforms($user_id, $source_platform, $source_type, $source_id, $target_platform, $share_context = '', $conn) {
    executeQuery($conn, 
        "INSERT INTO cross_platform_shares (source_platform, source_type, source_id, target_platform, shared_by_user_id, share_context) 
         VALUES (?, ?, ?, ?, ?, ?)",
        [$source_platform, $source_type, $source_id, $target_platform, $user_id, $share_context],
        'ssssis'
    );
}

/**
 * Get trending content across both platforms
 */
function getTrendingContent($period = 'daily', $limit = 10, $conn) {
    return fetchAll($conn, 
        "SELECT * FROM trending_combined_content 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
         ORDER BY engagement_count DESC 
         LIMIT ?", 
        [$limit], 'i'
    );
}

/**
 * Update unified analytics for user
 */
function updateUserAnalytics($user_id, $date = null, $metrics = [], $conn) {
    $date = $date ?? date('Y-m-d');
    
    // Build SET clause dynamically
    $set_parts = [];
    $params = [];
    $types = '';
    
    foreach ($metrics as $field => $value) {
        $set_parts[] = "$field = ?";
        $params[] = $value;
        $types .= 'i';
    }
    
    $params[] = $user_id;
    $params[] = $date;
    $types .= 'is';
    
    $sql = "INSERT INTO unified_user_analytics (user_id, date, " . implode(', ', array_keys($metrics)) . ")
            VALUES (?, ?, " . implode(', ', array_fill(0, count($metrics), '?')) . ")
            ON DUPLICATE KEY UPDATE " . implode(', ', $set_parts);
    
    executeQuery($conn, $sql, $params, $types);
}

/**
 * Create career milestone
 */
function createCareerMilestone($user_id, $milestone_type, $title, $description = '', $related_job_id = null, $related_social_post_id = null, $milestone_date = null, $is_public = false, $conn) {
    $milestone_date = $milestone_date ?? date('Y-m-d');
    
    executeQuery($conn, 
        "INSERT INTO career_milestones (user_id, milestone_type, title, description, related_job_id, related_social_post_id, milestone_date, is_public) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$user_id, $milestone_type, $title, $description, $related_job_id, $related_social_post_id, $milestone_date, $is_public],
        'isssisssi'
    );
    
    // Log activity
    logCrossPlatformActivity($user_id, 'both', $milestone_type, null, null, [
        'title' => $title,
        'milestone_date' => $milestone_date,
        'is_public' => $is_public
    ], $conn);
    
    // Create notification if public
    if ($is_public) {
        $followers = getFollowers($user_id, $conn);
        foreach ($followers as $follower) {
            createUnifiedNotification(
                $follower['follower_id'], 
                'both', 
                'social_achievement', 
                $user_id, 
                'New Career Milestone', 
                "$title - $description", 
                null, 
                'user_profile', 
                "raketko-profile.php?id=$user_id", 
                'medium', 
                $conn
            );
        }
    }
}

/**
 * Get user's career milestones
 */
function getCareerMilestones($user_id, $public_only = false, $limit = 10, $conn) {
    $sql = "SELECT * FROM career_milestones WHERE user_id = ?";
    $params = [$user_id];
    
    if ($public_only) {
        $sql .= " AND is_public = TRUE";
    }
    
    $sql .= " ORDER BY milestone_date DESC LIMIT ?";
    $params[] = $limit;
    
    return fetchAll($conn, $sql, $params);
}

/**
 * Get user's unified skills
 */
function getUnifiedSkills($user_id, $conn) {
    return fetchAll($conn, 
        "SELECT us.*, usk.skill_name, usk.skill_category, usk.demand_level
         FROM user_unified_skills us
         JOIN unified_skills usk ON us.skill_id = usk.skill_id
         WHERE us.user_id = ?
         ORDER BY us.proficiency_level DESC, us.years_experience DESC", 
        [$user_id], 'i'
    );
}

/**
 * Add/update unified skill for user
 */
function updateUnifiedSkill($user_id, $skill_name, $proficiency_level, $years_experience = 0, $source = 'raketgo', $conn) {
    // Get or create skill
    $skill = fetchOne($conn, "SELECT * FROM unified_skills WHERE skill_name = ?", [$skill_name], 's');
    if (!$skill) {
        executeQuery($conn, 
            "INSERT INTO unified_skills (skill_name, skill_category, description) 
             VALUES (?, 'industry_specific', ?)",
            [$skill_name, "Professional skill: $skill_name"], 'ss'
        );
        $skill = fetchOne($conn, "SELECT * FROM unified_skills WHERE skill_name = ?", [$skill_name], 's');
    }
    
    // Update user skill
    executeQuery($conn, 
        "INSERT INTO user_unified_skills (user_id, skill_id, proficiency_level, years_experience, source) 
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE 
         proficiency_level = VALUES(proficiency_level), 
         years_experience = VALUES(years_experience), 
         source = VALUES(source), 
         updated_at = CURRENT_TIMESTAMP",
        [$user_id, $skill['skill_id'], $proficiency_level, $years_experience, $source], 'iisds'
    );
}

/**
 * Get user's network statistics
 */
function getUserNetworkStats($user_id, $conn) {
    return fetchOne($conn, 
        "SELECT 
            (SELECT COUNT(*) FROM social_connections WHERE following_id = ? AND status = 'accepted') as followers,
            (SELECT COUNT(*) FROM social_connections WHERE follower_id = ? AND status = 'accepted') as following,
            (SELECT COUNT(*) FROM social_posts WHERE user_id = ?) as social_posts,
            (SELECT COUNT(*) FROM job_posts WHERE employer_id = ?) as job_posts,
            (SELECT COUNT(*) FROM job_applications WHERE worker_id = ?) as job_applications,
            (SELECT AVG(rating) FROM job_ratings WHERE ratee_id = ?) as avg_rating",
        [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id], 'iiiiii'
    );
}

/**
 * Get followers of a user
 */
function getFollowers($user_id, $limit = 50, $conn) {
    return fetchAll($conn, 
        "SELECT u.user_id, u.full_name, u.profile_picture, u.user_type
         FROM social_connections sc
         JOIN users u ON sc.follower_id = u.user_id
         WHERE sc.following_id = ? AND sc.status = 'accepted'
         ORDER BY sc.created_at DESC
         LIMIT ?", 
        [$user_id, $limit], 'ii'
    );
}

/**
 * Get users that a user follows
 */
function getFollowing($user_id, $limit = 50, $conn) {
    return fetchAll($conn, 
        "SELECT u.user_id, u.full_name, u.profile_picture, u.user_type
         FROM social_connections sc
         JOIN users u ON sc.following_id = u.user_id
         WHERE sc.follower_id = ? AND sc.status = 'accepted'
         ORDER BY sc.created_at DESC
         LIMIT ?", 
        [$user_id, $limit], 'ii'
    );
}

/**
 * Helper function to generate default headline
 */
function generateDefaultHeadline($user_type) {
    switch ($user_type) {
        case 'worker':
            return 'Professional Worker';
        case 'employer':
            return 'Professional Employer';
        case 'admin':
            return 'Platform Administrator';
        default:
            return 'Professional';
    }
}

/**
 * Helper function to generate location string
 */
function generateLocationString($city, $province) {
    return trim("$city, $province");
}

/**
 * Helper function to extract LinkedIn URL from social links
 */
function extractLinkedInFromSocialLinks($social_links) {
    if (empty($social_links)) return null;
    
    // Parse social links (assuming JSON format)
    $links = json_decode($social_links, true);
    if (is_array($links)) {
        foreach ($links as $platform => $url) {
            if (stripos($platform, 'linkedin') !== false) {
                return $url;
            }
        }
    }
    
    // Check if social_links is a string containing LinkedIn URL
    if (stripos($social_links, 'linkedin.com') !== false) {
        preg_match('/https?:\/\/(www\.)?linkedin\.com\/[^\\s]+/', $social_links, $matches);
        return $matches[0] ?? null;
    }
    
    return null;
}

/**
 * Get recommended content based on user's activity
 */
function getRecommendedContent($user_id, $limit = 10, $conn) {
    $userSkills = getUnifiedSkills($user_id, $conn);
    $skillNames = array_column($userSkills, 'skill_name');
    
    $recommendations = [];
    
    // Get relevant job posts
    if (!empty($skillNames)) {
        $skillPlaceholders = str_repeat('?,', count($skillNames) - 1) . '?';
        $jobs = fetchAll($conn, 
            "SELECT jp.*, 'job_post' as content_type
             FROM job_posts jp
             WHERE jp.job_status = 'active' 
               AND jp.description LIKE CONCAT('%', REPLACE(?, ',', '% OR jp.description LIKE %'), '%')
             ORDER BY jp.created_at DESC
             LIMIT 5",
            [implode(',', $skillNames)], 's'
        );
        $recommendations = array_merge($recommendations, $jobs);
    }
    
    // Get relevant social posts
    $socialPosts = fetchAll($conn, 
        "SELECT sp.*, 'social_post' as content_type, u.full_name, u.profile_picture
         FROM social_posts sp
         JOIN users u ON sp.user_id = u.user_id
         WHERE sp.visibility = 'public'
         ORDER BY sp.likes_count DESC, sp.created_at DESC
         LIMIT 5",
        [], ''
    );
    $recommendations = array_merge($recommendations, $socialPosts);
    
    return array_slice($recommendations, 0, $limit);
}

/**
 * Update trending content scores
 */
function updateTrendingContent($conn) {
    // Update job posts trending
    executeQuery($conn, 
        "INSERT INTO trending_content (content_type, content_id, trending_score, engagement_rate, view_count, share_count, like_count, comment_count, trending_period, period_start, period_end)
        SELECT 
            'job_post',
            job_id,
            (views_count * 0.3 + (SELECT COUNT(*) FROM job_applications WHERE job_id = job_posts.job_id) * 0.7) as trending_score,
            CASE WHEN views_count > 0 THEN ((SELECT COUNT(*) FROM job_applications WHERE job_id = job_posts.job_id) / views_count * 100) ELSE 0 END as engagement_rate,
            views_count,
            0 as share_count,
            0 as like_count,
            0 as comment_count,
            'daily',
            DATE_SUB(CURDATE(), INTERVAL 1 DAY),
            CURDATE()
        FROM job_posts 
        WHERE job_status = 'active'
        ON DUPLICATE KEY UPDATE
        trending_score = VALUES(trending_score),
        engagement_rate = VALUES(engagement_rate),
        view_count = VALUES(view_count)"
    );
    
    // Update social posts trending
    executeQuery($conn, 
        "INSERT INTO trending_content (content_type, content_id, trending_score, engagement_rate, view_count, share_count, like_count, comment_count, trending_period, period_start, period_end)
        SELECT 
            'social_post',
            post_id,
            (likes_count * 0.3 + comments_count * 0.4 + shares_count * 0.3) as trending_score,
            CASE WHEN views_count > 0 THEN ((likes_count + comments_count + shares_count) / views_count * 100) ELSE 0 END as engagement_rate,
            views_count,
            shares_count,
            likes_count,
            comments_count,
            'daily',
            DATE_SUB(CURDATE(), INTERVAL 1 DAY),
            CURDATE()
        FROM social_posts 
        WHERE visibility = 'public'
        ON DUPLICATE KEY UPDATE
        trending_score = VALUES(trending_score),
        engagement_rate = VALUES(engagement_rate),
        view_count = VALUES(view_count),
        share_count = VALUES(share_count),
        like_count = VALUES(like_count),
        comment_count = VALUES(comment_count)"
    );
}

/**
 * Get user's career progress score
 */
function getCareerProgressScore($user_id, $conn) {
    $stats = getUserNetworkStats($user_id, $conn);
    
    $score = 0;
    
    // Profile completeness (30%)
    $profile = getUnifiedUserProfile($user_id, $conn);
    $completeness = 0;
    if ($profile['combined_bio']) $completeness += 20;
    if ($profile['combined_headline']) $completeness += 20;
    if ($profile['profile_picture']) $completeness += 20;
    if ($profile['social_skills'] && count(json_decode($profile['social_skills']))) $completeness += 20;
    if ($profile['social_interests'] && count(json_decode($profile['social_interests']))) $completeness += 20;
    
    $score += ($completeness / 100) * 30;
    
    // Network size (25%)
    $networkSize = $stats['followers'] + $stats['following'];
    $networkScore = min($networkSize / 100, 1) * 25; // Cap at 100 connections
    $score += $networkScore;
    
    // Activity level (25%)
    $activityScore = min(($stats['social_posts'] + $stats['job_posts']) / 20, 1) * 25; // Cap at 20 activities
    $score += $activityScore;
    
    // Engagement quality (20%)
    $avgRating = $stats['avg_rating'] ?: 0;
    $engagementScore = ($avgRating / 5) * 20;
    $score += $engagementScore;
    
    return round($score, 2);
}

/**
 * Sync user skills between platforms
 */
function syncUserSkills($user_id, $conn) {
    // Get skills from both platforms
    $raketgoSkills = fetchAll($conn, "SELECT skill_name, proficiency_level FROM user_skills WHERE user_id = ?", [$user_id], 'i');
    $raketkoSkills = getUnifiedSkills($user_id, $conn);
    
    // Merge skills
    $allSkills = [];
    
    // Add RaketGo skills
    foreach ($raketgoSkills as $skill) {
        $allSkills[$skill['skill_name']] = [
            'proficiency' => $skill['proficiency_level'],
            'source' => 'raketgo'
        ];
    }
    
    // Add RaketKo skills (override if higher proficiency)
    foreach ($raketkoSkills as $skill) {
        $currentProficiency = $allSkills[$skill['skill_name']]['proficiency'] ?? 'beginner';
        if (compareProficiency($skill['proficiency_level'], $currentProficiency) > 0) {
            $allSkills[$skill['skill_name']] = [
                'proficiency' => $skill['proficiency_level'],
                'source' => 'raketko'
            ];
        }
    }
    
    // Update unified skills
    foreach ($allSkills as $skillName => $skillData) {
        updateUnifiedSkill($user_id, $skillName, $skillData['proficiency'], 0, $skillData['source'], $conn);
    }
}

/**
 * Compare proficiency levels
 */
function compareProficiency($level1, $level2) {
    $levels = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
    return ($levels[$level1] ?? 0) - ($levels[$level2] ?? 0);
}

/**
 * Get platform usage statistics
 */
function getPlatformUsageStats($conn) {
    return fetchOne($conn, 
        "SELECT 
            (SELECT COUNT(*) FROM users WHERE account_status = 'active') as total_users,
            (SELECT COUNT(*) FROM users WHERE user_type = 'worker') as workers,
            (SELECT COUNT(*) FROM users WHERE user_type = 'employer') as employers,
            (SELECT COUNT(*) FROM social_posts) as total_social_posts,
            (SELECT COUNT(*) FROM job_posts WHERE job_status = 'active') as active_jobs,
            (SELECT COUNT(*) FROM social_connections WHERE status = 'accepted') as total_connections,
            (SELECT AVG(trust_score) FROM users WHERE trust_score > 0) as avg_trust_score",
        [], ''
    );
}
?>
