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
    <title>FPS Performance Charts</title>
    
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
        
        .chart-controls {
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
            margin: 0 5px;
        }
        
        button:hover {
            background: #005a87;
        }
        
        .chart-type-selector {
            margin: 20px 0;
            text-align: center;
        }
        
        .chart-type-selector button {
            margin: 0 5px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
            display: block;
        }
        
        .chart-container.hidden {
            display: none;
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
        <h1>FPS Performance Charts</h1>
        
        <div class="chart-controls">
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
                <button id="update-chart">Update Data</button>
                <button id="refresh-data">Refresh Data</button>
            </div>
        </div>
        
        <div class="chart-type-selector">
            <button id="line-chart-btn" class="active-chart-btn">Line Chart</button>
            <button id="bar-chart-btn">Bar Chart</button>
            <button id="pie-chart-btn">Pie Chart</button>
            <button id="scatter-chart-btn">Scatter Plot</button>
            <button id="histogram-chart-btn">Histogram</button>
        </div>
        
        <div class="chart-container" id="line-chart-container">
            <canvas id="lineChart"></canvas>
        </div>
        
        <div class="chart-container hidden" id="bar-chart-container">
            <canvas id="barChart"></canvas>
        </div>
        
        <div class="chart-container hidden" id="pie-chart-container">
            <canvas id="pieChart"></canvas>
        </div>
        
        <div class="chart-container hidden" id="scatter-chart-container">
            <canvas id="scatterChart"></canvas>
        </div>
        
        <div class="chart-container hidden" id="histogram-chart-container">
            <canvas id="histogramChart"></canvas>
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
            // Prepare data for the charts
            const fpsData = <?php echo $fps_json; ?>;
            
            // Extract labels and data points
            const labels = fpsData.map(item => new Date(item.timestamp).toLocaleTimeString());
            const data = fpsData.map(item => parseFloat(item.fps_value));
            
            // Create the line chart
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            const lineChart = new Chart(lineCtx, {
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
                            text: 'Frames Per Second (FPS) Over Time - Line Chart'
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
            
            // Create the bar chart
            const barCtx = document.getElementById('barChart').getContext('2d');
            const barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'FPS',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Frames Per Second (FPS) Over Time - Bar Chart'
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
                    }
                }
            });
            
            // Create the pie chart
            // For the pie chart, let's group FPS values into ranges
            const fpsRanges = {
                '0-15': 0,
                '16-30': 0,
                '31-45': 0,
                '46-60': 0
            };
            
            data.forEach(fps => {
                if (fps <= 15) fpsRanges['0-15']++;
                else if (fps <= 30) fpsRanges['16-30']++;
                else if (fps <= 45) fpsRanges['31-45']++;
                else fpsRanges['46-60']++;
            });
            
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['0-15 FPS', '16-30 FPS', '31-45 FPS', '46-60 FPS'],
                    datasets: [{
                        label: 'FPS Distribution',
                        data: [
                            fpsRanges['0-15'],
                            fpsRanges['16-30'],
                            fpsRanges['31-45'],
                            fpsRanges['46-60']
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 205, 86, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'FPS Distribution - Pie Chart'
                        },
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    }
                }
            });
            
            // Create the scatter plot
            const scatterCtx = document.getElementById('scatterChart').getContext('2d');
            const scatterData = data.map((fps, index) => ({
                x: index,
                y: fps
            }));
            
            const scatterChart = new Chart(scatterCtx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'FPS Scatter Plot',
                        data: scatterData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'FPS Scatter Plot'
                        },
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Time Index'
                            },
                            type: 'linear',
                            position: 'bottom',
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
                    }
                }
            });
            
            // Create the histogram
            // Define bins for the histogram
            const binCount = 10;
            const minFPS = Math.min(...data);
            const maxFPS = Math.max(...data);
            const binSize = (maxFPS - minFPS) / binCount;
            
            // Initialize bins
            const bins = Array(binCount).fill(0);
            
            // Populate bins
            data.forEach(fps => {
                const binIndex = Math.min(Math.floor((fps - minFPS) / binSize), binCount - 1);
                bins[binIndex]++;
            });
            
            // Define bin labels
            const binLabels = bins.map((_, i) => {
                const start = (minFPS + i * binSize).toFixed(1);
                const end = (minFPS + (i + 1) * binSize).toFixed(1);
                return `${start}-${end}`;
            });
            
            const histogramCtx = document.getElementById('histogramChart').getContext('2d');
            const histogramChart = new Chart(histogramCtx, {
                type: 'bar',
                data: {
                    labels: binLabels,
                    datasets: [{
                        label: 'FPS Distribution',
                        data: bins,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'FPS Histogram'
                        },
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'FPS Range'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Frequency'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    }
                }
            });
            
            // Function to show selected chart and hide others
            function showChart(chartType) {
                // Hide all containers
                $('#line-chart-container, #bar-chart-container, #pie-chart-container, #scatter-chart-container, #histogram-chart-container').addClass('hidden');
                
                // Remove active class from all buttons
                $('.chart-type-selector button').removeClass('active-chart-btn');
                
                // Show selected container and set active button
                switch(chartType) {
                    case 'line':
                        $('#line-chart-container').removeClass('hidden');
                        $('#line-chart-btn').addClass('active-chart-btn');
                        break;
                    case 'bar':
                        $('#bar-chart-container').removeClass('hidden');
                        $('#bar-chart-btn').addClass('active-chart-btn');
                        break;
                    case 'pie':
                        $('#pie-chart-container').removeClass('hidden');
                        $('#pie-chart-btn').addClass('active-chart-btn');
                        break;
                    case 'scatter':
                        $('#scatter-chart-container').removeClass('hidden');
                        $('#scatter-chart-btn').addClass('active-chart-btn');
                        break;
                    case 'histogram':
                        $('#histogram-chart-container').removeClass('hidden');
                        $('#histogram-chart-btn').addClass('active-chart-btn');
                        break;
                }
            }
            
            // Initially show line chart
            showChart('line');
            
            // Event handlers for chart type buttons
            $('#line-chart-btn').click(function() {
                showChart('line');
            });
            
            $('#bar-chart-btn').click(function() {
                showChart('bar');
            });
            
            $('#pie-chart-btn').click(function() {
                showChart('pie');
            });
            
            $('#scatter-chart-btn').click(function() {
                showChart('scatter');
            });
            
            $('#histogram-chart-btn').click(function() {
                showChart('histogram');
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
    
    <style>
        .active-chart-btn {
            background: #28a745 !important;
        }
    </style>
</body>
</html>