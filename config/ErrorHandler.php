<?php
/**
 * Error Handler and Validation Class
 * Standardizes error handling, validation, and response patterns
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */

class ErrorHandler {
    private $errors = [];
    private $successes = [];
    
    /**
     * Add error message
     */
    public function addError($message) {
        $this->errors[] = $message;
    }
    
    /**
     * Add success message
     */
    public function addSuccess($message) {
        $this->successes[] = $message;
    }
    
    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get all successes
     */
    public function getSuccesses() {
        return $this->successes;
    }
    
    /**
     * Check if has errors
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Check if has successes
     */
    public function hasSuccesses() {
        return !empty($this->successes);
    }
    
    /**
     * Get first error
     */
    public function getFirstError() {
        return $this->errors[0] ?? '';
    }
    
    /**
     * Get first success
     */
    public function getFirstSuccess() {
        return $this->successes[0] ?? '';
    }
    
    /**
     * Clear all messages
     */
    public function clear() {
        $this->errors = [];
        $this->successes = [];
    }
    
    /**
     * Validate required fields
     */
    public function validateRequired($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->addError(ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }
        return !$this->hasErrors();
    }
    
    /**
     * Validate email
     */
    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('Please enter a valid email address');
            return false;
        }
        return true;
    }
    
    /**
     * Validate mobile number (Philippines format)
     */
    public function validateMobile($mobile) {
        // Remove any non-digit characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Check if it's 10-11 digits and starts with 09
        if (!preg_match('/^09[0-9]{8,9}$/', $mobile)) {
            $this->addError('Please enter a valid Philippine mobile number (starting with 09)');
            return false;
        }
        
        return true;
    }
    
    /**
     * validate password strength
     */
    public function validatePassword($password) {
        if (strlen($password) < 8) {
            $this->addError('Password must be at least 8 characters long');
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $this->addError('Password must contain at least one uppercase letter');
            return false;
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $this->addError('Password must contain at least one lowercase letter');
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $this->addError('Password must contain at least one number');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate numeric value
     */
    public function validateNumeric($value, $fieldName, $min = null, $max = null) {
        if (!is_numeric($value)) {
            $this->addError(ucfirst($fieldName) . ' must be a number');
            return false;
        }
        
        $numValue = (float)$value;
        
        if ($min !== null && $numValue < $min) {
            $this->addError(ucfirst($fieldName) . ' must be at least ' . $min);
            return false;
        }
        
        if ($max !== null && $numValue > $max) {
            $this->addError(ucfirst($fieldName) . ' must be at most ' . $max);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate string length
     */
    public function validateLength($value, $fieldName, $min = null, $max = null) {
        $length = strlen($value);
        
        if ($min !== null && $length < $min) {
            $this->addError(ucfirst($fieldName) . ' must be at least ' . $min . ' characters long');
            return false;
        }
        
        if ($max !== null && $length > $max) {
            $this->addError(ucfirst($fieldName) . ' must be at most ' . $max . ' characters long');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate date
     */
    public function validateDate($date, $fieldName, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->addError(ucfirst($fieldName) . ' must be a valid date');
            return false;
        }
        return true;
    }
    
    /**
     * Validate file upload
     */
    public function validateFile($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->addError('Please select a file to upload');
            return false;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $this->addError('File size must be less than ' . round($maxSize / 1048576, 2) . 'MB');
            return false;
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $this->addError('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrf($token) {
        if (!verifyCsrfToken($token)) {
            $this->addError('Invalid request. Please refresh the page and try again.');
            return false;
        }
        return true;
    }
    
    /**
     * Validate user authentication
     */
    public function validateAuth() {
        if (!isLoggedIn()) {
            $this->addError('You must be logged in to perform this action');
            return false;
        }
        return true;
    }
    
    /**
     * Validate user type
     */
    public function validateUserType($requiredType) {
        if (getCurrentUserType() !== $requiredType) {
            $this->addError('You do not have permission to perform this action');
            return false;
        }
        return true;
    }
    
    /**
     * Render error messages
     */
    public function renderErrors() {
        if (!$this->hasErrors()) return '';
        
        $html = '<div class="alert alert-error" style="margin-bottom: 1rem; border-radius: 12px; background: #FFF0F5; color: #D62E3E; border: 1px solid #FFD1DC;">';
        foreach ($this->errors as $error) {
            $html .= '<div style="margin-bottom: 0.5rem;"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($error) . '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render success messages
     */
    public function renderSuccesses() {
        if (!$this->hasSuccesses()) return '';
        
        $html = '<div class="alert alert-success" style="margin-bottom: 1rem; border-radius: 12px; background: var(--sana-pink-bg); color: var(--sana-red-dark); border: 1px solid var(--sana-pink);">';
        foreach ($this->successes as $success) {
            $html .= '<div style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($success) . '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Handle JSON response for AJAX requests
     */
    public function jsonResponse() {
        header('Content-Type: application/json');
        
        $response = [
            'success' => !$this->hasErrors(),
            'errors' => $this->getErrors(),
            'successes' => $this->getSuccesses()
        ];
        
        echo json_encode($response);
        exit;
    }
}
