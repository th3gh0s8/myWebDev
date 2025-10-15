<?php
// Include the database connection
include_once 'db.php';

// Check connection
if (!$conn) {
    die("Connection failed: Database connection not established.");
}

// Function to fetch FPS data with sorting and filtering
function fetchFPSData($conn, $limit = 10, $page_url = null, $session_id = null, $sort_by = 'timestamp', $sort_order = 'DESC') {
    $allowed_sort_columns = ['timestamp', 'fps_value', 'page_url', 'session_id'];

    // Validate sort_by parameter
    if (!in_array($sort_by, $allowed_sort_columns)) {
        $sort_by = 'timestamp';
    }

    // Validate sort_order parameter
    $sort_order = strtoupper($sort_order);
    if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
        $sort_order = 'DESC';
    }

    $sql = "SELECT id, timestamp, fps_value, page_url, session_id FROM fps_performance WHERE 1=1";

    $params = [];
    if ($page_url) {
        $sql .= " AND page_url LIKE ?";
        $params[] = '%' . $conn->real_escape_string($page_url) . '%';
    }
    if ($session_id) {
        $sql .= " AND session_id LIKE ?";
        $params[] = '%' . $conn->real_escape_string($session_id) . '%';
    }

    $sql .= " ORDER BY " . $sort_by . " " . $sort_order . " LIMIT ?";
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

        return $data;
    }

    return [];
}

// Handle GET parameters for filtering and sorting
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page_url = isset($_GET['page_url']) ? $_GET['page_url'] : null;
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'timestamp';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Fetch FPS data
$fps_data = fetchFPSData($conn, $limit, $page_url, $session_id, $sort_by, $sort_order);

// Get total count for information
$count_sql = "SELECT COUNT(*) as total FROM fps_performance";
$count_result = $conn->query($count_sql);
$total_count = $count_result->fetch_assoc()['total'];

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPS Performance Data Table</title>

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

        .controls {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .control-group {
            margin: 10px 0;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }

        input, select {
            padding: 8px;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            min-width: 150px;
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

        .view-charts-btn {
            background: #28a745;
            margin-left: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }

        .view-charts-btn:hover {
            background: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            cursor: pointer;
            user-select: none;
        }

        th:hover {
            background-color: #e0e0e0;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .sort-indicator {
            margin-left: 5px;
            font-size: 0.8em;
        }

        .info-panel {
            margin: 20px 0;
            padding: 15px;
            background: #e9f7ef;
            border-left: 4px solid #28a745;
            border-radius: 3px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            padding: 0;
            border: 1px solid #888;
            border-radius: 8px;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: auto;
            position: relative;
        }

        .modal-header {
            padding: 15px 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #e0e0e0;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        .modal-body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>FPS Performance Data</h1>

        <div class="controls">
            <div class="form-row">
                <div class="control-group">
                    <label for="limit">Show Records:</label>
                    <select id="limit">
                        <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
               <!--
                <div class="control-group">
                    <label for="page_url_filter">Filter Page URL:</label>
                    <input type="text" id="page_url_filter" value="<?php echo htmlspecialchars($page_url ?? ''); ?>" placeholder="Filter by page URL">
                </div>

                <div class="control-group">
                    <label for="session_id_filter">Filter Session ID:</label>
                    <input type="text" id="session_id_filter" value="<?php echo htmlspecialchars($session_id ?? ''); ?>" placeholder="Filter by session ID">
                </div>
               -->
                <div class="control-group">
                    <button id="apply-filters">Apply Filters</button>
                    <button id="reset-filters">Reset</button>
                    <button id="view-charts" class="view-charts-btn">View Charts</button>
                </div>
            </div>
        </div>
        
        <div class="info-panel">
            <p>Showing <?php echo count($fps_data); ?> of <?php echo $total_count; ?> records</p>
        </div>

        <table id="dataTable">
            <thead>
                <tr>
                    <th data-sort="id">ID <?php echo $sort_by === 'id' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="timestamp">Timestamp <?php echo $sort_by === 'timestamp' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="fps_value">FPS <?php echo $sort_by === 'fps_value' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="page_url">Page URL <?php echo $sort_by === 'page_url' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="session_id">Session ID <?php echo $sort_by === 'session_id' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fps_data)): ?>
                    <?php foreach ($fps_data as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($row['fps_value']); ?></td>
                            <td><?php echo htmlspecialchars($row['page_url']); ?></td>
                            <td><?php echo htmlspecialchars($row['session_id']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Include FPS Chart Modal -->
        <div id="chartModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>FPS Performance Charts</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <!-- Chart content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

        <div class="info-panel">
            <p>Showing <?php echo count($fps_data); ?> of <?php echo $total_count; ?> records</p>
        </div>

        <table id="dataTable">
            <thead>
                <tr>
                    <th data-sort="id">ID <?php echo $sort_by === 'id' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="timestamp">Timestamp <?php echo $sort_by === 'timestamp' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="fps_value">FPS <?php echo $sort_by === 'fps_value' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="page_url">Page URL <?php echo $sort_by === 'page_url' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                    <th data-sort="session_id">Session ID <?php echo $sort_by === 'session_id' ? '<span class="sort-indicator">' . ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fps_data)): ?>
                    <?php foreach ($fps_data as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($row['fps_value']); ?></td>
                            <td><?php echo htmlspecialchars($row['page_url']); ?></td>
                            <td><?php echo htmlspecialchars($row['session_id']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            // Apply filters button event
            $('#apply-filters').click(function() {
                const limit = $('#limit').val();
                const pageUrl = $('#page_url_filter').val();
                const sessionId = $('#session_id_filter').val();

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

            // Reset filters button event
            $('#reset-filters').click(function() {
                window.location.href = window.location.pathname;
            });

            // View charts button event
            $('#view-charts').click(function() {
                const limit = $('#limit').val();
                const pageUrl = $('#page_url_filter').val();
                const sessionId = $('#session_id_filter').val();

                // Build URL for chart page with current filters
                let chartUrl = 'chart_modal.php';
                let params = [];

                if (limit) params.push('limit=' + limit);
                if (pageUrl) params.push('page_url=' + encodeURIComponent(pageUrl));
                if (sessionId) params.push('session_id=' + encodeURIComponent(sessionId));

                if (params.length > 0) {
                    chartUrl += '?' + params.join('&');
                }

                // Load the modal content via AJAX
                $.get(chartUrl, function(data) {
                    $('#chartModal .modal-body').html(data);
                    $('#chartModal').show();
                    
                    // Reinitialize Chart.js after content is loaded
                    setTimeout(function() {
                        if (typeof Chart !== 'undefined') {
                            // Charts will be initialized by the loaded content
                        }
                    }, 100);
                }).fail(function() {
                    alert('Error loading chart data');
                });
            });
            
            // Modal close functionality
            $('.close, #chartModal').click(function(e) {
                if (e.target === this || $(e.target).hasClass('close')) {
                    $('#chartModal').hide();
                }
            });
            
            // Prevent modal content from closing when clicked
            $('.modal-content').click(function(e) {
                e.stopPropagation();
            });

            // Sorting functionality
            $('th[data-sort]').click(function() {
                const sortBy = $(this).data('sort');
                const currentSortBy = '<?php echo $sort_by; ?>';
                const currentSortOrder = '<?php echo $sort_order; ?>';

                let newSortOrder = 'ASC';

                // If clicking the same column, toggle the sort order
                if (sortBy === currentSortBy) {
                    newSortOrder = (currentSortOrder === 'ASC') ? 'DESC' : 'ASC';
                }

                // Update the URL with new sort parameters
                const limit = $('#limit').val();
                const pageUrl = $('#page_url_filter').val();
                const sessionId = $('#session_id_filter').val();

                let url = window.location.pathname;
                let params = [];

                params.push('sort_by=' + sortBy);
                params.push('sort_order=' + newSortOrder);

                if (limit) params.push('limit=' + limit);
                if (pageUrl) params.push('page_url=' + encodeURIComponent(pageUrl));
                if (sessionId) params.push('session_id=' + encodeURIComponent(sessionId));

                url += '?' + params.join('&');

                window.location.href = url;
            });
        });
    </script>
</body>
</html>
