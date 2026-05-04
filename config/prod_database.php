<?php

// Database credentials
define('DB_HOST', 'sql307.infinityfree.com');
define('DB_USER', 'if0_41344639');
define('DB_PASS', 'q7zYQBmCGE');
define('DB_NAME', 'if0_41344639_raketgo');

// Simple connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    return $conn;
}

// Simple query
function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

// Fetch one
function fetchOne($conn, $sql, $params = [], $types = '') {
    $stmt = executeQuery($conn, $sql, $params, $types);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fetch all
function fetchAll($conn, $sql, $params = [], $types = '') {
    $stmt = executeQuery($conn, $sql, $params, $types);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

?>
