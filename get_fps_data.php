<?php
// Include the database connection
include_once 'db.php';

// Check connection
if (!$conn) {
    error_log("Connection failed: Database connection not established.");
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Connection failed: Database connection not established.']);
    exit;
}

// Function to fetch FPS data
function fetchFPSData($conn, $limit = 100, $page_url = null, $session_id = null) {
    $sql = "SELECT timestamp, fps_value, page_url, session_id FROM fps_performance WHERE 1=1";

    $params = [];
    if ($page_url) {
        $sql .= " AND page_url = ?";
        $params[] = $page_url;
    }
    if ($session_id) {
        $sql .= " AND session_id = ?";
        $params[] = $session_id;
    }

    $sql .= " ORDER BY timestamp DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    // Bind parameters dynamically
    if (!empty($params)) {
        $types = str_repeat('s', count($params) - 1) . 'i'; // Last param is always int (limit)
        $bind_result = $stmt->bind_param($types, ...$params);
        if (!$bind_result) {
            error_log("Bind failed: " . $stmt->error);
            $stmt->close();
            return [];
        }
    } else {
        $bind_result = $stmt->bind_param('i', $limit);
        if (!$bind_result) {
            error_log("Bind failed: " . $stmt->error);
            $stmt->close();
            return [];
        }
    }

    $execute_result = $stmt->execute();
    if (!$execute_result) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    if (!$result) {
        error_log("Get result failed: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    // Reverse the data to get chronological order (oldest first)
    return array_reverse($data);
}

// Handle GET parameters for filtering
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$page_url = isset($_GET['page_url']) ? $_GET['page_url'] : null;
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

// Validate limit parameter to prevent SQL injection
if ($limit <= 0 || $limit > 10000) {
    $limit = 100; // Set a reasonable default if the limit is invalid
}

try {
    // Fetch FPS data
    $fps_data = fetchFPSData($conn, $limit, $page_url, $session_id);

    // Calculate statistics
    $avg_fps = 0;
    $min_fps = 0;
    $max_fps = 0;

    if (!empty($fps_data)) {
        $fps_values = array_column($fps_data, 'fps_value');
        // Filter out non-numeric values to ensure proper calculation
        $fps_values = array_filter($fps_values, function($value) {
            return is_numeric($value);
        });
        
        if (!empty($fps_values)) {
            $avg_fps = array_sum($fps_values) / count($fps_values);
            $min_fps = min($fps_values);
            $max_fps = max($fps_values);
        }
    }

    // Ensure statistics are always numbers
    $avg_fps = is_numeric($avg_fps) ? (float)$avg_fps : 0.0;
    $min_fps = is_numeric($min_fps) ? (float)$min_fps : 0.0;
    $max_fps = is_numeric($max_fps) ? (float)$max_fps : 0.0;

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $fps_data,
        'avg_fps' => $avg_fps,
        'min_fps' => $min_fps,
        'max_fps' => $max_fps
    ]);
} catch (Exception $e) {
    error_log("Exception in get_fps_data.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

// Close the database connection
$conn->close();
?>