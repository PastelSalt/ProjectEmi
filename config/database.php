<?php
/**
 * Database Configuration File
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */

// Database credentials
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('RAKETGO_DB_HOST') ?: 'localhost');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (int)(getenv('RAKETGO_DB_PORT') ?: 3306));
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('RAKETGO_DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('RAKETGO_DB_PASS') ?: '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('RAKETGO_DB_NAME') ?: 'raketgo');
}

function bindParamsByReference($stmt, $types, &$params) {
    if ($types === '' || empty($params)) {
        return true;
    }

    $bindValues = [$types];
    foreach ($params as $index => &$param) {
        $bindValues[] = &$param;
    }

    return call_user_func_array([$stmt, 'bind_param'], $bindValues);
}

// Create database connection
function getDBConnection() {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        
        // Initialize mysqli with driver options to handle authentication plugin
        $driver_options = [
            MYSQLI_OPT_CONNECT_TIMEOUT => 10,
            MYSQLI_INIT_COMMAND => "SET SESSION sql_mode='TRADITIONAL'"
        ];
        
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        
        // Connect with default authentication plugin handling
        $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Close database connection
function closeDBConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

// Transaction helper functions
function beginTransaction($conn) {
    return $conn->begin_transaction();
}

function commitTransaction($conn) {
    return $conn->commit();
}

function rollbackTransaction($conn) {
    return $conn->rollback();
}

// Prepared statement helper function
function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params) && !empty($types)) {
        if (strlen($types) !== count($params)) {
            error_log('Parameter binding mismatch: types and values length differ.');
            $stmt->close();
            return false;
        }

        if (!bindParamsByReference($stmt, $types, $params)) {
            error_log("Bind failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    return $stmt;
}

// Fetch single row
function fetchOne($conn, $sql, $params = [], $types = '') {
    $stmt = executeQuery($conn, $sql, $params, $types);
    if (!$stmt) return null;
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

// Fetch multiple rows
function fetchAll($conn, $sql, $params = [], $types = '') {
    $stmt = executeQuery($conn, $sql, $params, $types);
    if (!$stmt) return [];
    
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    
    return $rows;
}
?>
