<?php
/**
 * Worker Portfolio Management Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'My Portfolio';
require_once 'config/config.php';
requireUserType('worker');
require_once 'includes/header.php';

$conn = getDBConnection();
$worker_id = getCurrentUserId();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // Add new portfolio item
        if ($action === 'add_portfolio') {
            $title = sanitizeInput($_POST['title'] ?? '');
            $description = sanitizeMultilineInput($_POST['description'] ?? '');
            $work_type = sanitizeInput($_POST['work_type'] ?? '');
            $project_url = sanitizeExternalUrl($_POST['project_url'] ?? '');
            $skills = sanitizeInput($_POST['skills'] ?? '');
            
            // Blue-collar specific fields
            $client_company = sanitizeInput($_POST['client_company'] ?? '');
            $job_location = sanitizeInput($_POST['job_location'] ?? '');
            $work_duration = sanitizeInput($_POST['work_duration'] ?? '');
            $team_size = (int)($_POST['team_size'] ?? 0);
            $completion_date = sanitizeInput($_POST['completion_date'] ?? '');
            $tools_equipment = sanitizeInput($_POST['tools_equipment'] ?? '');
            $certifications = sanitizeInput($_POST['certifications'] ?? '');
            $supervisor_name = sanitizeInput($_POST['supervisor_name'] ?? '');

            if (empty($title)) {
                $error = 'Work title is required.';
            } elseif (empty($work_type)) {
                $error = 'Work type is required.';
            } elseif (strlen($title) > 200) {
                $error = 'Title must not exceed 200 characters.';
            } elseif (strlen($description) > 2000) {
                $error = 'Description must not exceed 2000 characters.';
            } else {
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['project_image'];
                    $fileSize = $file['size'];
                    $fileTmpPath = $file['tmp_name'];
                    $fileName = $file['name'];
                    $fileType = mime_content_type($fileTmpPath);

                    // Validate file type (images only)
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!in_array($fileType, $allowedMimeTypes, true)) {
                        $error = 'Invalid image type. Please upload JPEG, PNG, WebP, or GIF.';
                    } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                        $error = 'Image size exceeds 5MB limit.';
                    } else {
                        // Generate unique filename
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $uniqueFileName = uniqid('portfolio_', true) . '_' . $worker_id . '.' . $fileExtension;
                        $destination = PORTFOLIO_IMAGES_DIR . $uniqueFileName;

                        if (saveUploadedImage($fileTmpPath, $destination, 1600, 1600)) {
                            $image_path = 'uploads/portfolio/' . $uniqueFileName;
                        } else {
                            $error = 'Failed to upload image. Please try again.';
                        }
                    }
                }

                if (empty($error)) {
                    // Prepare JSON arrays
                    $skillsArray = !empty($skills) ? array_map('trim', explode(',', $skills)) : [];
                    $toolsArray = !empty($tools_equipment) ? array_map('trim', explode(',', $tools_equipment)) : [];
                    $certsArray = !empty($certifications) ? array_map('trim', explode(',', $certifications)) : [];
                    
                    $insertSql = "INSERT INTO worker_portfolio (worker_id, title, description, work_type, project_url, image_path, client_company, job_location, work_duration, team_size, completion_date, tools_equipment, certifications, supervisor_name, skills_used, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $params = [
                        $worker_id, $title, $description, $work_type, $project_url, $image_path,
                        $client_company, $job_location, $work_duration, $team_size, $completion_date,
                        json_encode($toolsArray), json_encode($certsArray), $supervisor_name,
                        json_encode($skillsArray)
                    ];
                    
                    if (executeQuery($conn, $insertSql, $params, 'isssssssssssssss')) {
                        $success = 'Portfolio item added successfully!';
                    } else {
                        $error = 'Failed to add portfolio item. Please try again.';
                    }
                }
            }
        }

        // Delete portfolio item
        elseif ($action === 'delete_portfolio') {
            $portfolio_id = (int)($_POST['portfolio_id'] ?? 0);
            
            // Get image path to delete file
            $item = fetchOne($conn, "SELECT image_path FROM worker_portfolio WHERE portfolio_id = ? AND worker_id = ?", [$portfolio_id, $worker_id], 'ii');
            
            if ($item && !empty($item['image_path'])) {
                $filePath = BASE_PATH . $item['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            if (executeQuery($conn, "DELETE FROM worker_portfolio WHERE portfolio_id = ? AND worker_id = ?", [$portfolio_id, $worker_id], 'ii')) {
                $success = 'Portfolio item deleted successfully.';
            } else {
                $error = 'Failed to delete portfolio item.';
            }
        }

        // Edit portfolio item
        elseif ($action === 'edit_portfolio') {
            $portfolio_id = (int)($_POST['portfolio_id'] ?? 0);
            $title = sanitizeInput($_POST['title'] ?? '');
            $description = sanitizeMultilineInput($_POST['description'] ?? '');
            $project_url = sanitizeExternalUrl($_POST['project_url'] ?? '');
            $skills = sanitizeInput($_POST['skills'] ?? '');

            if (empty($title)) {
                $error = 'Project title is required.';
            } elseif (strlen($title) > 200) {
                $error = 'Title must not exceed 200 characters.';
            } else {
                // Handle image upload (optional for edit)
                $image_path = null;
                $updateImage = false;
                
                if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['project_image'];
                    $fileSize = $file['size'];
                    $fileTmpPath = $file['tmp_name'];
                    $fileName = $file['name'];
                    $fileType = mime_content_type($fileTmpPath);

                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!in_array($fileType, $allowedMimeTypes, true)) {
                        $error = 'Invalid image type. Please upload JPEG, PNG, WebP, or GIF.';
                    } elseif ($fileSize > 5 * 1024 * 1024) {
                        $error = 'Image size exceeds 5MB limit.';
                    } else {
                        // Delete old image if exists
                        $oldItem = fetchOne($conn, "SELECT image_path FROM worker_portfolio WHERE portfolio_id = ? AND worker_id = ?", [$portfolio_id, $worker_id], 'ii');
                        if ($oldItem && !empty($oldItem['image_path'])) {
                            $oldFilePath = BASE_PATH . $oldItem['image_path'];
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }

                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $uniqueFileName = uniqid('portfolio_', true) . '_' . $worker_id . '.' . $fileExtension;
                        $destination = PORTFOLIO_IMAGES_DIR . $uniqueFileName;

                        if (saveUploadedImage($fileTmpPath, $destination, 1600, 1600)) {
                            $image_path = 'uploads/portfolio/' . $uniqueFileName;
                            $updateImage = true;
                        } else {
                            $error = 'Failed to upload image. Please try again.';
                        }
                    }
                }

                if (empty($error)) {
                    if ($updateImage) {
                        $updateSql = "UPDATE worker_portfolio 
                                     SET title = ?, description = ?, image_path = ?, project_url = ?, skills_used = ?, updated_at = NOW()
                                     WHERE portfolio_id = ? AND worker_id = ?";
                        $result = executeQuery($conn, $updateSql, [$title, $description, $image_path, $project_url, $skills, $portfolio_id, $worker_id], 'sssssii');
                    } else {
                        $updateSql = "UPDATE worker_portfolio 
                                     SET title = ?, description = ?, project_url = ?, skills_used = ?, updated_at = NOW()
                                     WHERE portfolio_id = ? AND worker_id = ?";
                        $result = executeQuery($conn, $updateSql, [$title, $description, $project_url, $skills, $portfolio_id, $worker_id], 'ssssii');
                    }

                    if ($result && $result->affected_rows > 0) {
                        $success = 'Portfolio item updated successfully!';
                    } else {
                        $error = 'Failed to update portfolio item.';
                    }
                }
            }
        }
    }
}

// Fetch worker's portfolio
$portfolioItems = fetchAll($conn, 
    "SELECT * FROM worker_portfolio WHERE worker_id = ? ORDER BY created_at DESC", 
    [$worker_id], 
    'i'
);

// Fetch worker info
$worker = fetchOne($conn, "SELECT * FROM users WHERE user_id = ?", [$worker_id], 'i');
?>

<div class="container">
    <div class="layout-two-col">
        <!-- Main Content -->
        <div>
            <!-- Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Add Portfolio Form -->
            <div class="panel">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    ADD PORTFOLIO ITEM
                </div>
                <div class="panel-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="add_portfolio">

                        <div class="form-group">
                            <label for="title">Work Title <span class="text-pink">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   placeholder="e.g., E-Commerce Website, Residential Construction, Welding Project" required maxlength="200">
                        </div>

                        <div class="form-group">
                            <label for="work_type">Work Type <span class="text-pink">*</span></label>
                            <select id="work_type" name="work_type" class="form-control" required>
                                <option value="">Select Work Type</option>
                                <option value="project">Digital Project</option>
                                <option value="job_site">Job Site Work</option>
                                <option value="certification">Certification</option>
                                <option value="equipment_operation">Equipment Operation</option>
                                <option value="construction">Construction</option>
                                <option value="welding">Welding</option>
                                <option value="craft">Craft Work</option>
                                <option value="service">Service Work</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="installation">Installation</option>
                                <option value="repair">Repair Work</option>
                                <option value="assembly">Assembly Work</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="transportation">Transportation</option>
                                <option value="agriculture">Agriculture</option>
                                <option value="general_labor">General Labor</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4" 
                                      placeholder="Describe the work, your role, tools/equipment used, and outcomes achieved..."
                                      maxlength="2000"></textarea>
                            <small class="text-muted"><span id="desc-count">0</span>/2000 characters</small>
                        </div>

                        <!-- Blue-collar specific fields -->
                        <div id="blue-collar-fields" style="display: none;">
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="client_company">Client/Company <span class="text-muted">(optional)</span></label>
                                    <input type="text" id="client_company" name="client_company" class="form-control" 
                                           placeholder="e.g., ABC Construction, TechCorp">
                                </div>
                                <div class="form-group">
                                    <label for="job_location">Work Location <span class="text-muted">(optional)</span></label>
                                    <input type="text" id="job_location" name="job_location" class="form-control" 
                                           placeholder="e.g., Makati City, Quezon City">
                                </div>
                            </div>
                            
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="work_duration">Duration <span class="text-muted">(optional)</span></label>
                                    <input type="text" id="work_duration" name="work_duration" class="form-control" 
                                           placeholder="e.g., 3 months, 2 weeks">
                                </div>
                                <div class="form-group">
                                    <label for="team_size">Team Size <span class="text-muted">(optional)</span></label>
                                    <input type="number" id="team_size" name="team_size" class="form-control" 
                                           placeholder="e.g., 5" min="1">
                                </div>
                                <div class="form-group">
                                    <label for="completion_date">Completion Date <span class="text-muted">(optional)</span></label>
                                    <input type="date" id="completion_date" name="completion_date" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="tools_equipment">Tools/Equipment <span class="text-muted">(comma-separated)</span></label>
                                <input type="text" id="tools_equipment" name="tools_equipment" class="form-control" 
                                       placeholder="e.g., Welding Machine, Power Tools, Scaffolding">
                            </div>

                            <div class="form-group">
                                <label for="certifications">Certifications <span class="text-muted">(comma-separated)</span></label>
                                <input type="text" id="certifications" name="certifications" class="form-control" 
                                       placeholder="e.g., AWS Welding Certificate, Safety Training">
                            </div>

                            <div class="form-group">
                                <label for="supervisor_name">Supervisor/Reference <span class="text-muted">(optional)</span></label>
                                <input type="text" id="supervisor_name" name="supervisor_name" class="form-control" 
                                           placeholder="e.g., John Smith, Site Manager">
                            </div>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="project_url">Project URL <span class="text-muted">(optional)</span></label>
                                <input type="url" id="project_url" name="project_url" class="form-control" 
                                       placeholder="https://example.com">
                            </div>
                            <div class="form-group">
                                <label for="skills">Skills Used <span class="text-muted">(comma-separated)</span></label>
                                <input type="text" id="skills" name="skills" class="form-control" 
                                       placeholder="e.g., PHP, React, Photoshop, Welding, Carpentry">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="project_image">Project Image <span class="text-muted">(optional, max 5MB)</span></label>
                            <input type="file" id="project_image" name="project_image" class="form-control" 
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                            <small class="text-muted">Supported formats: JPEG, PNG, WebP, GIF. Max 5MB.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add to Portfolio
                        </button>
                    </form>
                </div>
            </div>

            <!-- Portfolio Items -->
            <?php if (!empty($portfolioItems)): ?>
                <div class="panel" style="margin-top: 1.5rem;">
                    <div class="section-header">
                        <span class="header-square"></span>
                        MY PORTFOLIO <span class="view-all"><?php echo count($portfolioItems); ?> items</span>
                    </div>
                    <div class="panel-body">
                        <div style="display: grid; gap: 1.5rem;">
                            <?php foreach ($portfolioItems as $item): ?>
                                <div class="portfolio-item" style="border: 1px solid var(--border-light); border-radius: 8px; padding: 1rem;">
                                    <div style="display: flex; gap: 1rem;">
                                        <?php if (!empty($item['image_path'])): ?>
                                            <div style="flex-shrink: 0;">
                                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                     style="width: 150px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            </div>
                                        <?php endif; ?>
                                        <div style="flex: 1;">
                                            <div style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem;">
                                                <h4 style="margin-bottom: 0;"><?php echo htmlspecialchars($item['title']); ?></h4>
                                                <span class="tag tag-blue" style="font-size: 0.6rem;">
                                                    <?php 
                                                    $workTypeLabels = [
                                                        'project' => 'Project',
                                                        'job_site' => 'Job Site',
                                                        'certification' => 'Certification',
                                                        'equipment_operation' => 'Equipment',
                                                        'construction' => 'Construction',
                                                        'welding' => 'Welding',
                                                        'craft' => 'Craft',
                                                        'service' => 'Service',
                                                        'maintenance' => 'Maintenance',
                                                        'installation' => 'Installation',
                                                        'repair' => 'Repair',
                                                        'assembly' => 'Assembly',
                                                        'manufacturing' => 'Manufacturing',
                                                        'transportation' => 'Transportation',
                                                        'agriculture' => 'Agriculture',
                                                        'general_labor' => 'General Labor',
                                                        'other' => 'Other'
                                                    ];
                                                    echo htmlspecialchars($workTypeLabels[$item['work_type']] ?? $item['work_type']);
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Blue-collar specific info -->
                                            <?php if (in_array($item['work_type'], ['construction', 'welding', 'installation', 'repair', 'maintenance', 'job_site'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                                    <?php if (!empty($item['client_company'])): ?>
                                                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($item['client_company']); ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['job_location'])): ?>
                                                        <?php if (!empty($item['client_company'])) echo ' • '; ?>
                                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['job_location']); ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['work_duration'])): ?>
                                                        <?php if (!empty($item['client_company']) || !empty($item['job_location'])) echo ' • '; ?>
                                                        <i class="fas fa-hourglass-half"></i> <?php echo htmlspecialchars($item['work_duration']); ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['team_size'])): ?>
                                                        <?php if (!empty($item['client_company']) || !empty($item['job_location']) || !empty($item['work_duration'])) echo ' • '; ?>
                                                        <i class="fas fa-users"></i> Team of <?php echo (int)$item['team_size']; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                                <?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 200))); ?>
                                                <?php if (strlen($item['description']) > 200): ?>
                                                    <span class="text-muted">...</span>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <!-- Skills/Tools/Certifications -->
                                            <?php if (!empty($item['skills_used']) || !empty($item['tools_equipment']) || !empty($item['certifications'])): ?>
                                                <div style="margin-bottom: 0.5rem;">
                                                    <?php if (!empty($item['skills_used'])): ?>
                                                        <?php 
                                                        $skills = is_string($item['skills_used']) ? explode(',', $item['skills_used']) : json_decode($item['skills_used'], true) ?? [];
                                                        foreach ($skills as $skill): 
                                                        ?>
                                                            <span class="tag tag-pink" style="font-size: 0.7rem;"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($item['tools_equipment'])): ?>
                                                        <?php 
                                                        $tools = json_decode($item['tools_equipment'], true) ?? [];
                                                        foreach ($tools as $tool): 
                                                        ?>
                                                            <span class="tag tag-orange" style="font-size: 0.7rem;">
                                                                <i class="fas fa-tools"></i> <?php echo htmlspecialchars(trim($tool)); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($item['certifications'])): ?>
                                                        <?php 
                                                        $certs = json_decode($item['certifications'], true) ?? [];
                                                        foreach ($certs as $cert): 
                                                        ?>
                                                            <span class="tag tag-green" style="font-size: 0.7rem;">
                                                                <i class="fas fa-certificate"></i> <?php echo htmlspecialchars(trim($cert)); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                <i class="fas fa-clock"></i> Added <?php echo timeAgo($item['created_at']); ?>
                                                <?php if (!empty($item['project_url'])): ?>
                                                    <a href="<?php echo htmlspecialchars($item['project_url']); ?>" target="_blank" class="btn btn-outline btn-small" style="margin-left: 0.5rem; font-size: 0.7rem;">
                                                        <i class="fas fa-external-link-alt"></i> View Project
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($item['site_photos'])): ?>
                                                    <span class="tag tag-gray" style="margin-left: 0.5rem; font-size: 0.7rem;">
                                                        <i class="fas fa-images"></i> Site Photos
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-light); display: flex; gap: 0.5rem;">
                                        <button type="button" class="btn btn-secondary btn-small" onclick="toggleEditForm(<?php echo $item['portfolio_id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this portfolio item?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete_portfolio">
                                            <input type="hidden" name="portfolio_id" value="<?php echo $item['portfolio_id']; ?>">
                                            <button type="submit" class="btn btn-outline btn-small" style="color: var(--red-badge);">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Form (hidden by default) -->
                                    <div id="edit-form-<?php echo $item['portfolio_id']; ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border-light);">
                                        <form method="POST" enctype="multipart/form-data">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="edit_portfolio">
                                            <input type="hidden" name="portfolio_id" value="<?php echo $item['portfolio_id']; ?>">

                                            <div class="form-group">
                                                <label>Project Title</label>
                                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
                                            </div>

                                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                <div class="form-group">
                                                    <label>Project URL</label>
                                                    <input type="url" name="project_url" class="form-control" value="<?php echo htmlspecialchars($item['project_url'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Skills Used</label>
                                                    <input type="text" name="skills" class="form-control" value="<?php echo htmlspecialchars($item['skills_used'] ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>New Image <span class="text-muted">(leave empty to keep current)</span></label>
                                                <input type="file" name="project_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                                            </div>

                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary btn-small">
                                                    <i class="fas fa-save"></i> Save Changes
                                                </button>
                                                <button type="button" class="btn btn-outline btn-small" onclick="toggleEditForm(<?php echo $item['portfolio_id']; ?>)">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="panel" style="margin-top: 1.5rem;">
                    <div class="section-header">
                        <span class="header-square"></span>
                        MY PORTFOLIO
                    </div>
                    <div class="panel-body text-center" style="padding: 2rem;">
                        <i class="fas fa-briefcase" style="font-size: 3rem; color: var(--gray-mid); margin-bottom: 1rem;"></i>
                        <h3>No portfolio items yet</h3>
                        <p class="text-muted">Add your projects to showcase your skills and experience to employers.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Profile Preview -->
            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    PROFILE PREVIEW
                </div>
                <div class="panel-body text-center">
                    <?php if (!empty($worker['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($worker['profile_picture']); ?>" alt="Profile" 
                             style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 0.5rem;">
                    <?php else: ?>
                        <div class="message-avatar" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 0.5rem;">
                            <?php echo mb_strtoupper(mb_substr($worker['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($worker['full_name']); ?></h4>
                    <p class="text-small text-muted"><?php echo htmlspecialchars($worker['city'] . ', ' . $worker['province']); ?></p>
                    
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            <i class="fas fa-briefcase"></i> <?php echo count($portfolioItems); ?> Portfolio Items
                        </div>
                        <?php if ($worker['trust_score'] > 0): ?>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.3rem;">
                                <i class="fas fa-star" style="color: #FFD700;"></i> Trust Score: <?php echo number_format($worker['trust_score'], 2); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <a href="dashboard-worker.php" class="btn btn-secondary btn-small btn-block" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Tips Widget -->
            <div class="widget">
                <div class="section-header section-header-green">
                    <span class="header-square"></span>
                    PORTFOLIO TIPS
                </div>
                <div class="panel-body">
                    <div class="notice-text" style="font-size: 0.85rem;">
                        <strong><i class="fas fa-lightbulb" style="color: var(--yellow-badge);"></i> Best Practices:</strong><br><br>
                        • Include high-quality images of your work<br><br>
                        • Describe your specific role and contributions<br><br>
                        • List technologies and skills you used<br><br>
                        • Provide links to live projects if available<br><br>
                        • Keep descriptions concise but informative
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleEditForm(portfolioId) {
    const form = document.getElementById('edit-form-' + portfolioId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Work type field visibility
document.getElementById('work_type').addEventListener('change', function() {
    const blueCollarTypes = ['construction', 'welding', 'installation', 'repair', 'maintenance', 'job_site', 'equipment_operation', 'manufacturing', 'transportation', 'agriculture', 'general_labor', 'craft'];
    const blueCollarFields = document.getElementById('blue-collar-fields');
    
    if (blueCollarTypes.includes(this.value)) {
        blueCollarFields.style.display = 'block';
    } else {
        blueCollarFields.style.display = 'none';
    }
});

// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    document.getElementById('desc-count').textContent = this.value.length;
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
