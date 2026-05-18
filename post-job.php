<?php
/**
 * Post Job Page (Employer)
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Post a New Job';
require_once 'config/config.php';
requireUserType('employer');
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    }

    $job_title = sanitizeInput($_POST['job_title'] ?? '');
    $job_description = sanitizeMultilineInput($_POST['job_description'] ?? '');
    $location_region = sanitizeInput($_POST['location_region'] ?? '');
    $location_province = sanitizeInput($_POST['location_province'] ?? '');
    $location_city = sanitizeInput($_POST['location_city'] ?? '');
    $specific_address = sanitizeMultilineInput($_POST['specific_address'] ?? '');
    $pay_amount = (float)($_POST['pay_amount'] ?? 0);
    $pay_type = sanitizeInput($_POST['pay_type'] ?? 'fixed');
    $required_skills = sanitizeInput($_POST['required_skills'] ?? '');
    $preferred_skills = sanitizeInput($_POST['preferred_skills'] ?? '');
    $job_category = sanitizeInput($_POST['job_category'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $slots_available = (int)($_POST['slots_available'] ?? 1);
    $advance_payment_amount = (float)($_POST['advance_payment_amount'] ?? 0);
    $job_type = sanitizeInput($_POST['job_type'] ?? 'one_time');
    $remote_policy = sanitizeInput($_POST['remote_policy'] ?? 'on_site');

    // Handle optional job image upload
    $job_image = null;
    if (isset($_FILES['job_image']) && $_FILES['job_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['job_image'];
        $fileSize = $file['size'];
        $fileTmpPath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileType = mime_content_type($fileTmpPath);

        // Validate file type (images only)
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($fileType, $allowedMimeTypes, true)) {
            $error = 'Invalid image type. Please upload JPEG, PNG, or WebP.';
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $error = 'Image size exceeds 5MB limit.';
        } else {
            // Generate unique filename
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $uniqueFileName = uniqid('job_', true) . '_' . $user_id . '.' . $fileExtension;
            $destination = JOB_IMAGES_DIR . $uniqueFileName;

            if (saveUploadedImage($fileTmpPath, $destination, 1600, 1200)) {
                $job_image = 'uploads/jobs/' . $uniqueFileName;
            } else {
                $error = 'Failed to upload job image. Please try again.';
            }
        }
    }

    global $ALLOWED_PAY_TYPES, $ALLOWED_JOB_TYPES;
    $allowedRemotePolicies = ['on_site', 'hybrid', 'fully_remote'];

    if (!empty($error)) {
        // Preserve CSRF error state.
    } elseif (empty($job_title) || empty($job_description) || empty($location_city) || $pay_amount <= 0) {
        $error = 'Please fill in all required fields.';
    } elseif (!isValidRegionCode($location_region)) {
        $error = 'Please select a valid region.';
    } elseif (!in_array($pay_type, $ALLOWED_PAY_TYPES, true)) {
        $error = 'Invalid pay type selected.';
    } elseif (!in_array($job_type, $ALLOWED_JOB_TYPES, true)) {
        $error = 'Invalid job type selected.';
    } elseif (!isValidJobPayCombination($job_type, $pay_type)) {
        $jobTypeInfo = getJobTypeInfo($job_type);
        $suggested = array_map(function($pt) {
            $info = getPayTypeInfo($pt);
            return $info['label'];
        }, $jobTypeInfo['suggested_pay_types']);
        $error = sprintf(
            'For %s positions, we suggest using: %s',
            $jobTypeInfo['label'],
            implode(', ', $suggested)
        );
    } elseif ($slots_available < 1 || $slots_available > 200) {
        $error = 'Workers needed must be between 1 and 200.';
    } elseif (strlen($job_title) > 200 || strlen($job_category) > 100 || strlen($location_province) > 100 || strlen($location_city) > 100) {
        $error = 'One or more fields exceed the allowed length.';
    } elseif (!empty($start_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        $error = 'Invalid start date format.';
    } elseif (!empty($end_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        $error = 'Invalid end date format.';
    } elseif (!empty($start_date) && !empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
        $error = 'End date cannot be earlier than start date.';
    } elseif (!in_array($remote_policy, $allowedRemotePolicies, true)) {
        $error = 'Invalid remote policy selected.';
    } else {
        // Validate duration based on job type
        $durationValidation = validateJobDuration($job_type, $start_date, $end_date);
        if (!$durationValidation['valid']) {
            $error = $durationValidation['error'];
        } else {
        $insertSql = "INSERT INTO job_posts (
            employer_id, job_title, job_description,
            location_region, location_province, location_city, specific_address,
            pay_amount, pay_type, required_skills, preferred_skills, job_category, job_type,
            start_date, end_date, slots_available, advance_payment_amount, job_image, remote_policy
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $user_id, $job_title, $job_description,
            $location_region, $location_province, $location_city, $specific_address,
            $pay_amount, $pay_type, $required_skills, $preferred_skills, $job_category, $job_type,
            $start_date ?: null, $end_date ?: null, $slots_available, $advance_payment_amount, $job_image, $remote_policy
        ];
        $types = 'issssssdsssssssids'; // 19 params
        
            if (executeQuery($conn, $insertSql, $params, $types)) {
                $_SESSION['flash_success'] = 'Job posted successfully!';
                redirect('dashboard-employer.php');
            } else {
                $error = 'Failed to post job. Please try again.';
            }
        }
    }
}
?>

<div class="container">
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="layout-two-col">
        <!-- Main Form -->
        <div>
            <form method="POST" enctype="multipart/form-data" data-validate>
                <?php echo csrfField(); ?>
                <!-- Job Information -->
                <div class="panel">
                    <div class="section-header">
                        <span class="header-square"></span>
                        JOB INFORMATION
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="job_title">Job Title *</label>
                            <input type="text" id="job_title" name="job_title" class="form-control" 
                                   placeholder="E.g., Carpenter Needed for Home Renovation" required>
                        </div>
                        <div class="form-group">
                            <label for="job_description">Job Description *</label>
                            <textarea id="job_description" name="job_description" class="form-control" 
                                      rows="5" placeholder="Describe the job in detail..." required></textarea>
                        </div>
                        <div class="grid grid-3">
                            <div class="form-group">
                                <label for="job_category">Job Category</label>
                                <input type="text" id="job_category" name="job_category" class="form-control" 
                                       placeholder="E.g., Construction, IT">
                            </div>
                            <div class="form-group">
                                <label for="job_type">Job Type *</label>
                                <select id="job_type" name="job_type" class="form-control" required onchange="updatePayTypeOptions()">
                                    <?php foreach ($JOB_TYPES_CONFIG as $key => $config): ?>
                                        <option value="<?php echo $key; ?>" data-suggested='<?php echo json_encode($config['suggested_pay_types']); ?>' data-default="<?php echo $config['default_pay_type']; ?>">
                                            <?php echo $config['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small id="job_type_help" class="text-muted" style="display: block; margin-top: 4px;"></small>
                            </div>
                            <div class="form-group">
                                <label for="remote_policy">Work Arrangement *</label>
                                <select id="remote_policy" name="remote_policy" class="form-control" required>
                                    <option value="on_site" selected>On-site</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="fully_remote">Fully Remote</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="slots_available">Workers Needed *</label>
                                <input type="number" id="slots_available" name="slots_available" class="form-control" 
                                       value="1" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="panel">
                    <div class="section-header section-header-pink">
                        <span class="header-square"></span>
                        LOCATION DETAILS
                    </div>
                    <div class="panel-body">
                        <div class="grid grid-3">
                            <div class="form-group">
                                <label for="location_region">Region *</label>
                                <select id="location_region" name="location_region" class="form-control" required>
                                    <option value="">Select region</option>
                                    <?php foreach ($PHILIPPINES_REGIONS as $code => $name): ?>
                                        <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location_province">Province *</label>
                                <input type="text" id="location_province" name="location_province" class="form-control" 
                                       placeholder="Province" required>
                            </div>
                            <div class="form-group">
                                <label for="location_city">City *</label>
                                <input type="text" id="location_city" name="location_city" class="form-control" 
                                       placeholder="City" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="specific_address">Specific Address (Optional)</label>
                            <textarea id="specific_address" name="specific_address" class="form-control" 
                                      rows="2" placeholder="Street address or landmark"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Compensation -->
                <div class="panel">
                    <div class="section-header section-header-gray">
                        <span class="header-square"></span>
                        COMPENSATION
                    </div>
                    <div class="panel-body">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label for="pay_amount">Pay Amount (₱) *</label>
                                <input type="number" id="pay_amount" name="pay_amount" class="form-control" 
                                       placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="pay_type">Pay Type * <span id="pay_type_hint" class="text-muted" style="font-size: 0.75rem; font-weight: 400;"></span></label>
                                <select id="pay_type" name="pay_type" class="form-control" required>
                                    <?php foreach ($PAY_TYPES_CONFIG as $key => $config): ?>
                                        <option value="<?php echo $key; ?>" data-job-types='<?php echo json_encode($JOB_TYPES_CONFIG); ?>'>
                                            <?php echo $config['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="advance_payment_amount">Advance Payment (Optional)</label>
                            <input type="number" id="advance_payment_amount" name="advance_payment_amount" class="form-control" 
                                   placeholder="0.00" step="0.01" min="0">
                            <small class="text-muted">Offering advance payment can attract more applicants</small>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="panel">
                    <div class="section-header section-header-pink">
                        <span class="header-square"></span>
                        TIMELINE (OPTIONAL)
                    </div>
                    <div class="panel-body">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skills -->
                <div class="panel">
                    <div class="section-header">
                        <span class="header-square"></span>
                        SKILLS REQUIRED
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="required_skills">Required Skills (comma-separated)</label>
                            <input type="text" id="required_skills" name="required_skills" class="form-control"
                                   placeholder="E.g., Carpentry, Welding, Plumbing">
                            <small class="text-muted">Separate multiple skills with commas</small>
                        </div>
                        <div class="form-group">
                            <label for="preferred_skills">Preferred Skills (comma-separated)</label>
                            <input type="text" id="preferred_skills" name="preferred_skills" class="form-control"
                                   placeholder="E.g., Blueprint Reading, Power Tools">
                            <small class="text-muted">Nice-to-have skills that are not mandatory</small>
                        </div>
                    </div>
                </div>

                <!-- Job Image -->
                <div class="panel">
                    <div class="section-header section-header-green">
                        <span class="header-square"></span>
                        JOB AREA IMAGE (OPTIONAL)
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="job_image">Upload Image</label>
                            <input type="file" id="job_image" name="job_image" class="form-control"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Upload a photo of the job site or work area. Max 5MB. JPEG, PNG, or WebP.</small>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="panel">
                    <div class="panel-body" style="display: flex; gap: 0.8rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-check"></i> Post Job
                        </button>
                        <a href="dashboard-employer.php" class="btn btn-outline" style="flex: 1; text-align: center;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="widget">
                <div class="section-header">
                    <span class="header-square"></span>
                    POSTING GUIDE
                </div>
                <div class="panel-body">
                    <div class="notice-text">
                        Fill in all required fields marked with *. Be as detailed as possible in the job description 
                        to attract the right candidates.
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-pink">
                    <span class="header-square"></span>
                    TIPS
                </div>
                <div class="panel-body" style="font-size: 0.8rem;">
                    <div class="headline-item">
                        <span class="headline-badge">01</span>
                        <div>
                            <strong>Clear Title</strong>
                            <div class="text-muted">Use a specific, descriptive job title</div>
                        </div>
                    </div>
                    <div class="headline-item">
                        <span class="headline-badge">02</span>
                        <div>
                            <strong>Detailed Description</strong>
                            <div class="text-muted">Include scope, requirements, and expectations</div>
                        </div>
                    </div>
                    <div class="headline-item">
                        <span class="headline-badge">03</span>
                        <div>
                            <strong>Fair Compensation</strong>
                            <div class="text-muted">Offer competitive pay to attract skilled workers</div>
                        </div>
                    </div>
                    <div class="headline-item">
                        <span class="headline-badge">04</span>
                        <div>
                            <strong>List Skills</strong>
                            <div class="text-muted">Specify required skills for better matching</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="widget">
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    QUICK LINKS
                </div>
                <div class="panel-body">
                    <a href="dashboard-employer.php" style="display: block; padding: 0.4rem 0; font-size: 0.82rem; color: var(--primary-blue-dark); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="index.php" style="display: block; padding: 0.4rem 0; font-size: 0.82rem; color: var(--primary-blue-dark); text-decoration: none;">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Job type configuration for JavaScript
const jobTypesConfig = <?php echo json_encode($JOB_TYPES_CONFIG); ?>;
const payTypesConfig = <?php echo json_encode($PAY_TYPES_CONFIG); ?>;

function updatePayTypeOptions() {
    const jobTypeSelect = document.getElementById('job_type');
    const payTypeSelect = document.getElementById('pay_type');
    const jobTypeHelp = document.getElementById('job_type_help');
    const payTypeHint = document.getElementById('pay_type_hint');

    const selectedJobType = jobTypeSelect.value;
    const jobConfig = jobTypesConfig[selectedJobType];

    if (!jobConfig) return;

    // Update job type help text
    jobTypeHelp.textContent = jobConfig.description;

    // Get current pay type value
    const currentPayType = payTypeSelect.value;

    // Rebuild pay type options
    payTypeSelect.innerHTML = '';

    // Add suggested pay types first
    jobConfig.suggested_pay_types.forEach(function(payType) {
        const option = document.createElement('option');
        option.value = payType;
        option.textContent = payTypesConfig[payType].label + ' (Recommended)';
        payTypeSelect.appendChild(option);
    });

    // Add other pay types (disabled but visible to show all options)
    Object.keys(payTypesConfig).forEach(function(payType) {
        if (!jobConfig.suggested_pay_types.includes(payType)) {
            const option = document.createElement('option');
            option.value = payType;
            option.textContent = payTypesConfig[payType].label;
            // option.disabled = true; // Optional: disable non-suggested options
            payTypeSelect.appendChild(option);
        }
    });

    // Select default pay type for this job type
    payTypeSelect.value = jobConfig.default_pay_type;

    // Update hint
    const suggestedLabels = jobConfig.suggested_pay_types.map(function(pt) {
        return payTypesConfig[pt].label;
    });
    payTypeHint.textContent = 'Suggested: ' + suggestedLabels.join(', ');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePayTypeOptions();

    // Add date validation based on job type
    const jobTypeSelect = document.getElementById('job_type');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    function validateDuration() {
        const jobConfig = jobTypesConfig[jobTypeSelect.value];
        const start = startDateInput.value ? new Date(startDateInput.value) : null;
        const end = endDateInput.value ? new Date(endDateInput.value) : null;

        if (start && end) {
            const durationDays = (end - start) / (1000 * 60 * 60 * 24);

            if (jobConfig.min_duration_days && durationDays < jobConfig.min_duration_days) {
                endDateInput.setCustomValidity(jobConfig.label + ' positions typically require at least ' + jobConfig.min_duration_days + ' days');
            } else if (jobConfig.max_duration_days && durationDays > jobConfig.max_duration_days) {
                endDateInput.setCustomValidity(jobConfig.label + ' positions typically should not exceed ' + jobConfig.max_duration_days + ' days');
            } else {
                endDateInput.setCustomValidity('');
            }
        }
    }

    jobTypeSelect.addEventListener('change', validateDuration);
    startDateInput.addEventListener('change', validateDuration);
    endDateInput.addEventListener('change', validateDuration);
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
