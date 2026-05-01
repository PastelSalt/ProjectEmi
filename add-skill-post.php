<?php
/**
 * Add Skill Post Page (Admin)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Add Skill Post';
require_once 'config/config.php';
requireUserType('admin');
require_once 'includes/header.php';

$conn = getDBConnection();
$admin_id = getCurrentUserId();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    }

    $skill_name = sanitizeInput($_POST['skill_name'] ?? '');
    $skill_description = sanitizeMultilineInput($_POST['skill_description'] ?? '');
    $skill_category = sanitizeInput($_POST['skill_category'] ?? '');
    $difficulty_level = sanitizeInput($_POST['difficulty_level'] ?? 'beginner');
    $learning_resources = sanitizeMultilineInput($_POST['learning_resources'] ?? '');
    $estimated_duration_hours = (int)($_POST['estimated_duration_hours'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $allowedDifficulties = ['beginner', 'intermediate', 'advanced', 'expert'];

    if (!empty($error)) {
        // Preserve CSRF error state
    } elseif (empty($skill_name) || empty($skill_description) || empty($skill_category)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($difficulty_level, $allowedDifficulties, true)) {
        $error = 'Please select a valid difficulty level.';
    } elseif (strlen($skill_name) > 100 || strlen($skill_category) > 100) {
        $error = 'Skill name and category must not exceed 100 characters.';
    } elseif ($estimated_duration_hours < 0 || $estimated_duration_hours > 10000) {
        $error = 'Estimated duration must be between 0 and 10000 hours.';
    } else {
        // Build tags from difficulty and duration to preserve all form data
        $tags = 'Difficulty: ' . $difficulty_level;
        if ($estimated_duration_hours > 0) {
            $tags .= ', Duration: ' . $estimated_duration_hours . ' hours';
        }

        $insertSql = "INSERT INTO skill_posts (
            admin_id, post_title, post_content,
            post_type, link_url, category, tags, is_featured, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $params = [
            $admin_id, $skill_name, $skill_description,
            'course', $learning_resources, $skill_category, $tags, $is_featured
        ];
        $types = 'issssssi';

        if (executeQuery($conn, $insertSql, $params, $types)) {
            $_SESSION['flash_success'] = 'Skill post created successfully!';
            redirect('analytics.php?tab=skills');
        } else {
            $error = 'Failed to create skill post. Please try again.';
        }
    }
}

$skillCategories = ['Programming', 'Design', 'Marketing', 'Business', 'Data Science', 'Finance', 'Communication', 'Other'];
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <div class="panel">
                <div class="section-header">
                    <span class="header-square"></span>
                    ADD NEW SKILL POST
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

                    <form method="POST" style="max-width: 600px;">
                        <?php echo csrfField(); ?>

                        <div class="form-group">
                            <label for="skill_name">Skill Name <span class="text-pink">*</span></label>
                            <input type="text" id="skill_name" name="skill_name" class="form-control" 
                                   placeholder="e.g., React.js Development" 
                                   value="<?php echo htmlspecialchars($_POST['skill_name'] ?? ''); ?>" required>
                            <small class="text-muted">The name of the skill being taught</small>
                        </div>

                        <div class="form-group">
                            <label for="skill_category">Category <span class="text-pink">*</span></label>
                            <select id="skill_category" name="skill_category" class="form-control" required>
                                <option value="">Select a category...</option>
                                <?php foreach ($skillCategories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"
                                        <?php echo ($_POST['skill_category'] ?? '') === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="skill_description">Description <span class="text-pink">*</span></label>
                            <textarea id="skill_description" name="skill_description" class="form-control" 
                                      placeholder="Detailed description of the skill..." 
                                      rows="4" required><?php echo htmlspecialchars($_POST['skill_description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="difficulty_level">Difficulty Level <span class="text-pink">*</span></label>
                            <select id="difficulty_level" name="difficulty_level" class="form-control" required>
                                <option value="beginner" <?php echo ($_POST['difficulty_level'] ?? 'beginner') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="intermediate" <?php echo ($_POST['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo ($_POST['difficulty_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                <option value="expert" <?php echo ($_POST['difficulty_level'] ?? '') === 'expert' ? 'selected' : ''; ?>>Expert</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="estimated_duration_hours">Estimated Duration (hours) <span class="text-pink">*</span></label>
                            <input type="number" id="estimated_duration_hours" name="estimated_duration_hours" class="form-control" 
                                   placeholder="e.g., 40" min="0" max="10000"
                                   value="<?php echo htmlspecialchars($_POST['estimated_duration_hours'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="learning_resources">Learning Resources</label>
                            <textarea id="learning_resources" name="learning_resources" class="form-control" 
                                      placeholder="Links or references to learning materials (one per line)" 
                                      rows="3"><?php echo htmlspecialchars($_POST['learning_resources'] ?? ''); ?></textarea>
                            <small class="text-muted">Optional: Add resource links or references</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                                <span>Featured Skill (display prominently)</span>
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Skill Post
                            </button>
                            <a href="dashboard-admin.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    QUICK INFO
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        <strong>Skill Posts</strong> are educational resources that guide users in learning valuable skills.
                        <br><br>
                        Keep descriptions clear and difficulty levels accurate for better user experience.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
