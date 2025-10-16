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

<div class="refresh-btn-container" style="margin-bottom: 15px; text-align: center;">
    <button id="refresh-chart" style="background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer;">Refresh Chart</button>
</div>

<div class="chart-container" style="height: 400px; position: relative;">
    <canvas id="chartCanvas" style="width: 100%; height: 100%;"></canvas>
    <div id="chart-loading" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255,255,255,0.8); padding: 20px; border-radius: 5px; border: 1px solid #ddd;">
        Loading chart...
    </div>
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

<script>
    // Prepare data for the charts
    const fpsData = <?php echo $fps_json; ?>;
    
    // Get the chart type from URL parameter, default to 'line'
    const urlParams = new URLSearchParams(window.location.search);
    const chartType = urlParams.get('chart_type') || 'line';

    // Extract labels and data points
    const labels = fpsData.map(item => new Date(item.timestamp).toLocaleTimeString());
    const data = fpsData.map(item => parseFloat(item.fps_value));

    // For pie chart, group FPS values into ranges
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
    
    // For scatter plot, prepare coordinates
    const scatterData = data.map((fps, index) => ({
        x: index,
        y: fps
    }));
    
    // For histogram, create bins
    const binCount = 10;
    const minFPS = Math.min(...data);
    const maxFPS = Math.max(...data);
    const binSize = (maxFPS - minFPS) / binCount;
    
    const bins = Array(binCount).fill(0);
    data.forEach(fps => {
        const binIndex = Math.min(Math.floor((fps - minFPS) / binSize), binCount - 1);
        bins[binIndex]++;
    });
    
    const binLabels = bins.map((_, i) => {
        const start = (minFPS + i * binSize).toFixed(1);
        const end = (minFPS + (i + 1) * binSize).toFixed(1);
        return `${start}-${end}`;
    });

    // Chart configuration based on chart type
    let chartConfig = {};
    
    switch(chartType) {
        case 'line':
            chartConfig = {
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
            };
            break;
            
        case 'bar':
            chartConfig = {
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
            };
            break;
            
        case 'pie':
            chartConfig = {
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
            };
            break;
            
        case 'scatter':
            chartConfig = {
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
            };
            break;
            
        case 'histogram':
            chartConfig = {
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
            };
            break;
    }

    // Initialize chart immediately if DOM is already loaded, or wait for it
    // Also ensure initialization when loaded via AJAX
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        // Check if we're in a modal context (loaded via AJAX)
        // If the chart canvas is already in the DOM, initialize immediately
        if (document.getElementById('chartCanvas')) {
            initChart();
        } else {
            // If not ready, wait for DOM
            document.addEventListener('DOMContentLoaded', initChart);
        }
    }
    
    // Also provide a function to manually trigger initialization
    // This can be called from parent page when content is loaded via AJAX
    if (typeof window.initializeModalChart === 'undefined') {
        window.initializeModalChart = function() {
            setTimeout(initChart, 100); // Slight delay to ensure DOM is ready
        };
    }

    function initChart() {
        // First, check if a chart instance already exists and destroy it to prevent conflicts
        if (window.currentChart && typeof window.currentChart.destroy === 'function') {
            window.currentChart.destroy();
        }
        
        // Show loading indicator
        document.getElementById('chart-loading').style.display = 'block';
        
        // Wait a bit to ensure canvas is rendered
        setTimeout(function() {
            const chartCanvas = document.getElementById('chartCanvas');
            if (chartCanvas) {
                const ctx = chartCanvas.getContext('2d');
                if (ctx) {
                    window.currentChart = new Chart(ctx, chartConfig);
                    // Hide loading indicator after chart is created
                    document.getElementById('chart-loading').style.display = 'none';
                } else {
                    console.error('Could not get canvas context');
                    document.getElementById('chart-loading').style.display = 'none';
                }
            } else {
                console.error('Chart canvas element not found');
                document.getElementById('chart-loading').style.display = 'none';
            }
        }, 100);
    }

    // Function to update the chart with new data
    function updateChart(newData) {
        if (!window.currentChart) {
            console.error('Chart instance not available');
            return;
        }
        
        // Convert new data to the required format
        const newLabels = newData.map(item => new Date(item.timestamp).toLocaleTimeString());
        const newDataPoints = newData.map(item => parseFloat(item.fps_value));

        // Update chart data based on chart type
        switch(chartType) {
            case 'line':
            case 'bar':
                window.currentChart.data.labels = newLabels;
                window.currentChart.data.datasets[0].data = newDataPoints;
                break;
                
            case 'pie':
                // For pie chart, group FPS values into ranges
                const newFpsRanges = {
                    '0-15': 0,
                    '16-30': 0,
                    '31-45': 0,
                    '46-60': 0
                };
                
                newDataPoints.forEach(fps => {
                    if (fps <= 15) newFpsRanges['0-15']++;
                    else if (fps <= 30) newFpsRanges['16-30']++;
                    else if (fps <= 45) newFpsRanges['31-45']++;
                    else newFpsRanges['46-60']++;
                });
                
                window.currentChart.data.datasets[0].data = [
                    newFpsRanges['0-15'],
                    newFpsRanges['16-30'],
                    newFpsRanges['31-45'],
                    newFpsRanges['46-60']
                ];
                
                if (chartType === 'pie') {
                    window.currentChart.data.labels = ['0-15 FPS', '16-30 FPS', '31-45 FPS', '46-60 FPS'];
                }
                break;
                
            case 'scatter':
                // For scatter plot, prepare coordinates
                const newScatterData = newDataPoints.map((fps, index) => ({
                    x: index,
                    y: fps
                }));
                
                window.currentChart.data.datasets[0].data = newScatterData;
                break;
                
            case 'histogram':
                // For histogram, create bins
                const binCount = 10;
                const minFPS = Math.min(...newDataPoints);
                const maxFPS = Math.max(...newDataPoints);
                const binSize = (maxFPS - minFPS) / binCount;
                
                const bins = Array(binCount).fill(0);
                newDataPoints.forEach(fps => {
                    const binIndex = Math.min(Math.floor((fps - minFPS) / binSize), binCount - 1);
                    bins[binIndex]++;
                });
                
                window.currentChart.data.labels = bins.map((_, i) => {
                    const start = (minFPS + i * binSize).toFixed(1);
                    const end = (minFPS + (i + 1) * binSize).toFixed(1);
                    return `${start}-${end}`;
                });
                
                window.currentChart.data.datasets[0].data = bins;
                break;
        }
        
        // Update chart
        window.currentChart.update();
    }

    // Function to update statistics
    function updateStats(avg, min, max) {
        // Ensure the values are numbers before calling toFixed
        const avgNum = typeof avg === 'number' ? avg : parseFloat(avg) || 0;
        const minNum = typeof min === 'number' ? min : parseFloat(min) || 0;
        const maxNum = typeof max === 'number' ? max : parseFloat(max) || 0;
        
        document.getElementById('avg-fps').textContent = avgNum.toFixed(2);
        document.getElementById('min-fps').textContent = minNum.toFixed(2);
        document.getElementById('max-fps').textContent = maxNum.toFixed(2);
    }

    // Function to refresh chart data
    function refreshChart() {
        // Get current parameters
        const urlParams = new URLSearchParams(window.location.search);
        const limit = urlParams.get('limit') || '100';
        const pageUrl = urlParams.get('page_url');
        const sessionId = urlParams.get('session_id');
        
        // Build query string
        let queryString = `?limit=${limit}`;
        if (pageUrl) queryString += `&page_url=${encodeURIComponent(pageUrl)}`;
        if (sessionId) queryString += `&session_id=${encodeURIComponent(sessionId)}`;
        queryString += `&chart_type=${chartType}`;
        
        // Show loading indicator
        const refreshButton = document.getElementById('refresh-chart');
        const originalText = refreshButton.textContent;
        refreshButton.textContent = 'Refreshing...';
        refreshButton.disabled = true;
        
        // Fetch new data from the dedicated endpoint
        fetch(`get_fps_data.php${queryString}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(newData => {
                // Check if there was an error returned by the server
                if (newData.error) {
                    console.error('Server error:', newData.error);
                    alert(`Server error: ${newData.error}`);
                    return;
                }
                
                // Update chart with new data
                updateChart(newData.data);
                
                // Update statistics
                updateStats(newData.avg_fps, newData.min_fps, newData.max_fps);
                
                console.log('Chart refreshed successfully');
            })
            .catch(error => {
                console.error('Error fetching new data:', error);
                alert(`Error refreshing chart data: ${error.message}`);
            })
            .finally(() => {
                // Restore button state
                refreshButton.textContent = originalText;
                refreshButton.disabled = false;
            });
    }

    // Refresh button functionality
    document.getElementById('refresh-chart')?.addEventListener('click', refreshChart);
    
    // Modal functionality
    if (typeof $ !== 'undefined') {
        $('.close').click(function() {
            $('#chartModal').hide();
            
            // Destroy the chart instance when modal is closed
            if (window.currentChart && typeof window.currentChart.destroy === 'function') {
                window.currentChart.destroy();
                window.currentChart = null;
            }
        });
        
        $(window).click(function(event) {
            if (event.target.id === 'chartModal') {
                $('#chartModal').hide();
                
                // Destroy the chart instance when modal is closed
                if (window.currentChart && typeof window.currentChart.destroy === 'function') {
                    window.currentChart.destroy();
                    window.currentChart = null;
                }
            }
        });
    }
</script>