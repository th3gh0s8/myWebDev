<?php
// Include the database connection
include_once 'db.php';

// Check connection
if (!$conn) {
    die("Connection failed: Database connection not established.");
}

// Function to generate realistic FPS data
function generateFPSData($count = 100) {
    $data = [];
    $baseTime = time() - ($count * 10); // Start from 1000 seconds ago, 10 seconds apart
    
    for ($i = 0; $i < $count; $i++) {
        $timestamp = date('Y-m-d H:i:s', $baseTime + ($i * 10)); // Each sample is 10 seconds apart
        
        // Generate realistic FPS values
        // Simulate different performance levels over time
        $hour = date('H', $baseTime + ($i * 10));
        
        // Different FPS ranges based on time of day or simulated scenarios
        if ($i % 30 < 5) {
            // Periods of lower performance (e.g., when heavy operations happen)
            $fps = rand(15, 30);
        } else {
            // Normal performance
            $fps = rand(45, 60);
        }
        
        // Add some random variation
        $fps += (rand(-500, 500) / 100); // Add some decimal precision
        
        // Ensure FPS is not negative
        $fps = max(0, $fps);
        
        // Define different page URLs and sessions
        $pages = [
            '/home',
            '/dashboard',
            '/profile',
            '/settings',
            '/products',
            '/cart',
            '/checkout'
        ];
        
        $page_url = $pages[array_rand($pages)];
        $session_id = 'session_' . rand(100, 999);
        $user_id = rand(1, 50);
        
        $data[] = [
            'timestamp' => $timestamp,
            'fps_value' => round($fps, 2),
            'session_id' => $session_id,
            'page_url' => $page_url,
            'user_id' => $user_id
        ];
    }
    
    return $data;
}

// Insert sample data into the table
function insertSampleData($conn, $data) {
    $sql = "INSERT INTO fps_performance (timestamp, fps_value, session_id, page_url, user_id) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($data as $row) {
        $timestamp = $row['timestamp'];
        $fps_value = $row['fps_value'];
        $session_id = $row['session_id'];
        $page_url = $row['page_url'];
        $user_id = $row['user_id'];
        
        $stmt->bind_param("sdssi", $timestamp, $fps_value, $session_id, $page_url, $user_id);
        
        if ($stmt->execute()) {
            $successCount++;
        } else {
            echo "Error inserting record: " . $stmt->error . "<br>";
            $errorCount++;
        }
    }
    
    $stmt->close();
    
    return ['success' => $successCount, 'errors' => $errorCount];
}

echo "<h2>Inserting Sample FPS Data</h2>\n";

// Generate sample data
$sampleData = generateFPSData(200); // Generate 200 sample records

echo "<p>Generated " . count($sampleData) . " sample FPS records.</p>\n";

// Insert the data
$result = insertSampleData($conn, $sampleData);

echo "<p>Insertion completed: " . $result['success'] . " successful, " . $result['errors'] . " errors.</p>\n";

// Summary of what was inserted
echo "<h3>Sample Data Summary:</h3>\n";
echo "<ul>\n";
echo "<li>Time Range: " . $sampleData[0]['timestamp'] . " to " . $sampleData[count($sampleData) - 1]['timestamp'] . "</li>\n";
echo "<li>FPS Range: " . min(array_column($sampleData, 'fps_value')) . " to " . max(array_column($sampleData, 'fps_value')) . "</li>\n";
echo "<li>Different Sessions: " . count(array_unique(array_column($sampleData, 'session_id'))) . "</li>\n";
echo "<li>Different Pages: " . count(array_unique(array_column($sampleData, 'page_url'))) . "</li>\n";
echo "</ul>\n";

echo "<p><a href='fps_chart.php'>View FPS Chart</a></p>\n";

// Close the database connection
$conn->close();
?>