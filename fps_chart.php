<?php
// Include the database connection
include_once 'db.php';

// Check connection
if (!$conn) {
    die("Connection failed: Database connection not established.");
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
    if ($stmt) {
        // Bind parameters dynamically
        if (!empty($params)) {
            $types = str_repeat('s', count($params) - 1) . 'i'; // Last param is always int (limit)
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('i', $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        
        // Reverse the data to get chronological order (oldest first)
        return array_reverse($data);
    }
    
    return [];
}

// Handle GET parameters for filtering
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$page_url = isset($_GET['page_url']) ? $_GET['page_url'] : null;
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

// Fetch FPS data
$fps_data = fetchFPSData($conn, $limit, $page_url, $session_id);

// Convert data for JavaScript
$fps_json = json_encode($fps_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPS Performance Chart</title>
    
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        
        .controls {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .control-group {
            margin: 10px 0;
        }
        
        label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        
        input, select {
            padding: 5px;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        button {
            background: #007cba;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        button:hover {
            background: #005a87;
        }
        
        .info-panel {
            margin-top: 20px;
            padding: 15px;
            background: #e9f7ef;
            border-left: 4px solid #28a745;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>FPS Performance Chart</h1>
        
        <div class="controls">
            <div class="control-group">
                <label for="limit">Data Points:</label>
                <select id="limit">
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?php echo $limit == 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="page_url">Page URL:</label>
                <input type="text" id="page_url" value="<?php echo htmlspecialchars($page_url ?? ''); ?>" placeholder="Filter by page URL">
            </div>
            
            <div class="control-group">
                <label for="session_id">Session ID:</label>
                <input type="text" id="session_id" value="<?php echo htmlspecialchars($session_id ?? ''); ?>" placeholder="Filter by session ID">
            </div>
            
            <div class="control-group">
                <button id="update-chart">Update Chart</button>
                <button id="refresh-data">Refresh Data</button>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="fpsChart"></canvas>
        </div>
        
        <div class="info-panel">
            <p><strong>Current Average FPS:</strong> 
                <span id="avg-fps"><?php 
                    if (!empty($fps_data)) {
                        $avg = array_sum(array_column($fps_data, 'fps_value')) / count($fps_data);
                        echo number_format($avg, 2);
                    } else {
                        echo "0.00";
                    }
                ?></span>
            </p>
            <p><strong>Min FPS:</strong> 
                <span id="min-fps"><?php 
                    if (!empty($fps_data)) {
                        echo number_format(min(array_column($fps_data, 'fps_value')), 2);
                    } else {
                        echo "0.00";
                    }
                ?></span>
            </p>
            <p><strong>Max FPS:</strong> 
                <span id="max-fps"><?php 
                    if (!empty($fps_data)) {
                        echo number_format(max(array_column($fps_data, 'fps_value')), 2);
                    } else {
                        echo "0.00";
                    }
                ?></span>
            </p>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Prepare data for the chart
            const fpsData = <?php echo $fps_json; ?>;
            
            // Extract labels and data points
            const labels = fpsData.map(item => new Date(item.timestamp).toLocaleTimeString());
            const data = fpsData.map(item => parseFloat(item.fps_value));
            
            // Create the chart
            const ctx = document.getElementById('fpsChart').getContext('2d');
            const fpsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'FPS',
                        data: data,
                        borderColor: '#007cba',
                        backgroundColor: 'rgba(0, 124, 186, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#007cba',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Frames Per Second (FPS) Over Time'
                        },
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Time'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'FPS'
                            },
                            min: 0,
                            suggestedMax: 60,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
            
            // Handle update chart button
            $('#update-chart').click(function() {
                const limit = $('#limit').val();
                const pageUrl = $('#page_url').val();
                const sessionId = $('#session_id').val();
                
                // Update the URL with parameters and reload the page
                let url = window.location.pathname;
                let params = [];
                
                if (limit) params.push('limit=' + limit);
                if (pageUrl) params.push('page_url=' + encodeURIComponent(pageUrl));
                if (sessionId) params.push('session_id=' + encodeURIComponent(sessionId));
                
                if (params.length > 0) {
                    url += '?' + params.join('&');
                }
                
                window.location.href = url;
            });
            
            // Handle refresh data button
            $('#refresh-data').click(function() {
                location.reload();
            });
        });
    </script>
</body>
</html>