<?php
session_start();

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection with error handling
try {
    $conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Check authentication
if (!isset($_SESSION['cso_id'])) {
    header("Location: cso_login.php");
    exit();
}

// Function to fetch statistics
function fetchStatistics($conn) {
    $stats = [
        'total_today' => 0,
        'approved_today' => 0,
        'denied_today' => 0,
        'pending_count' => 0,
        'hourly_data' => [],
        'repeat_visitors' => 0,
        'top_hosts' => [],
        'weekly_data' => [],
        'approval_rate' => 0,
        'total_visitors' => 0
    ];

    $today = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime('sunday this week'));
    
    // Total visitors today (all statuses)
    $result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE DATE(check_in_time) = '$today'");
    if ($result) {
        $stats['total_today'] = $result->fetch_assoc()['count'] ?? 0;
    }
    
    // Approved today
    $result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE DATE(time_of_visit) = '$today' AND status='approved'");
    if ($result) {
        $stats['approved_today'] = $result->fetch_assoc()['count'] ?? 0;
    }
    
    // Denied today
    $result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE DATE(time_of_visit) = '$today' AND status='denied'");
    if ($result) {
        $stats['denied_today'] = $result->fetch_assoc()['count'] ?? 0;
    }

    // Pending count
    $result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status='pending'");
    if ($result) {
        $stats['pending_count'] = $result->fetch_assoc()['count'] ?? 0;
    }

    // Hourly distribution
    $hourly_result = $conn->query("SELECT HOUR(time_of_visit) as hour, COUNT(*) as count FROM visitors WHERE DATE(time_of_visit) = '$today' GROUP BY HOUR(time_of_visit)");
    if ($hourly_result) {
        while ($row = $hourly_result->fetch_assoc()) {
            $stats['hourly_data'][$row['hour']] = $row['count'];
        }
    }

    // Repeat visitors
    $result = $conn->query("SELECT COUNT(*) as count FROM (SELECT phone, COUNT(*) as visits FROM visitors WHERE status='approved' GROUP BY phone HAVING visits > 1) as repeat_visitors");
    if ($result) {
        $stats['repeat_visitors'] = $result->fetch_assoc()['count'] ?? 0;
    }

    // Top hosts
    $top_hosts_result = $conn->query("SELECT host_name, COUNT(*) as count FROM visitors WHERE status='approved' GROUP BY host_name ORDER BY count DESC LIMIT 5");
    if ($top_hosts_result) {
        while ($row = $top_hosts_result->fetch_assoc()) {
            $stats['top_hosts'][] = $row;
        }
    }
    
    // Weekly data
    $weekly_result = $conn->query("
        SELECT DAYNAME(time_of_visit) as day, COUNT(*) as count 
        FROM visitors 
        WHERE DATE(time_of_visit) BETWEEN '$week_start' AND '$week_end'
        GROUP BY DAYOFWEEK(time_of_visit), DAYNAME(time_of_visit)
        ORDER BY DAYOFWEEK(time_of_visit)
    ");
    
    $days = ['Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0];
    if ($weekly_result) {
        while ($row = $weekly_result->fetch_assoc()) {
            $days[$row['day']] = $row['count'];
        }
    }
    $stats['weekly_data'] = array_values($days);
    
    // Last week's data for comparison
    $last_week_start = date('Y-m-d', strtotime('monday last week'));
    $last_week_end = date('Y-m-d', strtotime('sunday last week'));
    
    $last_weekly_result = $conn->query("
        SELECT DAYNAME(time_of_visit) as day, COUNT(*) as count 
        FROM visitors 
        WHERE DATE(time_of_visit) BETWEEN '$last_week_start' AND '$last_week_end'
        GROUP BY DAYOFWEEK(time_of_visit), DAYNAME(time_of_visit)
        ORDER BY DAYOFWEEK(time_of_visit)
    ");
    
    $last_days = ['Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0];
    if ($last_weekly_result) {
        while ($row = $last_weekly_result->fetch_assoc()) {
            $last_days[$row['day']] = $row['count'];
        }
    }
    $stats['last_weekly_data'] = array_values($last_days);
    
    // Approval rate
    $total_processed = $stats['approved_today'] + $stats['denied_today'];
    $stats['approval_rate'] = $total_processed > 0 ? round(($stats['approved_today'] / $total_processed) * 100, 2) : 0;
    
    // Total visitors count
    $result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status='approved'");
    if ($result) {
        $stats['total_visitors'] = $result->fetch_assoc()['count'] ?? 0;
    }

    return $stats;
}

// Fetch initial statistics
$stats = fetchStatistics($conn);

// Check active tab
$active_tab = isset($_GET['tab']) && $_GET['tab'] == 'analytics' ? 'analytics' : 'approvals';

// Check if this is an AJAX request for visitor list
if (isset($_GET['ajax']) && $_GET['ajax'] == 'visitors') {
    $pending_result = $conn->query("SELECT * FROM visitors WHERE status='pending' ORDER BY time_of_visit DESC");
    
    if ($pending_result->num_rows > 0) {
        while ($row = $pending_result->fetch_assoc()) {
            echo '<div class="visitor-item">';
            echo '<div class="visitor-info">';
            echo '<h5>' . htmlspecialchars($row['name']) . '</h5>';
            echo '<span class="badge bg-warning text-dark mb-1">Pending</span>';
            echo '<small><i class="fas fa-phone me-1"></i> ' . htmlspecialchars($row['phone']) . '</small>';
            echo '<small><i class="fas fa-envelope me-1"></i> ' . htmlspecialchars($row['email']) . '</small>';
            echo '<small><i class="fas fa-user-shield me-1"></i> Visiting: ' . htmlspecialchars($row['host_name']) . '</small>';
            if (!empty($row['time_of_visit'])) {
                echo '<small><i class="fas fa-calendar-day me-1"></i> ' . date('M j, Y g:i A', strtotime($row['time_of_visit'])) . '</small>';
            }
            if (!empty($row['organization'])) {
                echo '<small><i class="fas fa-building me-1"></i> Organization: ' . htmlspecialchars($row['organization']) . '</small>';
            }
            if (!empty($row['reason'])) {
                echo '<small><i class="fas fa-info-circle me-1"></i> Reason: ' . htmlspecialchars($row['reason']) . '</small>';
            }
            echo '</div>';
            echo '<div class="action-buttons">';
            echo '<a href="approve.php?id=' . $row['id'] . '" class="btn btn-success btn-sm">';
            echo '<i class="fas fa-check me-1"></i> Approve';
            echo '</a>';
            echo '<a href="deny.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm">';
            echo '<i class="fas fa-times me-1"></i> Deny';
            echo '</a>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="empty-state">';
        echo '<i class="fas fa-user-check fa-4x mb-3"></i>';
        echo '<h4>No pending approvals</h4>';
        echo '<p>All visitor requests have been processed</p>';
        echo '</div>';
    }
    $conn->close();
    exit();
}

// Check if this is an AJAX request for stats
if (isset($_GET['ajax']) && $_GET['ajax'] == 'stats') {
    header('Content-Type: application/json');
    echo json_encode(fetchStatistics($conn));
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management Dashboard</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #07AF8B;
            --accent-color: #FFCA00;
            --dark-green: #007570;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1200px;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            height: 100%;
        }

        .stat-card {
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-green);
        }

        .stat-card .label {
            font-size: 0.9rem;
            color: #666;
        }

        .visitor-list {
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
            padding: 2rem;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .header-bar h1 {
            font-size: 1.6rem;
            color: var(--dark-green);
            font-weight: 700;
        }

        .nav-tabs .nav-link {
            color: var(--dark-green);
            font-weight: 500;
            border: none;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background-color: transparent;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }

        .concentric-chart {
            max-width: 300px;
            margin: 0 auto;
        }

        .visitor-item {
            display: flex;
            flex-direction: column;
            padding: 1rem 0;
            border-bottom: 1px solid #eaeaea;
            transition: background-color 0.2s;
        }

        .visitor-item:hover {
            background-color: #f9f9f9;
        }

        @media (min-width: 576px) {
            .visitor-item {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .visitor-info {
            flex-grow: 1;
        }

        .visitor-info h5 {
            margin-bottom: 0.5rem;
            color: var(--dark-green);
            font-weight: 600;
        }

        .visitor-info small {
            display: block;
            color: #555;
            margin-bottom: 0.2rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        @media (min-width: 576px) {
            .action-buttons {
                margin-top: 0;
            }
        }

        .btn-success {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            color: #777;
        }

        .empty-state i {
            color: #ccc;
        }

        .badge.bg-primary {
            background-color: var(--accent-color) !important;
            color: #000;
        }

        .badge.bg-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .logout-btn {
            color: var(--dark-green);
            border: 1px solid var(--dark-green);
        }
        
        .logout-btn:hover {
            background-color: var(--dark-green);
            color: white;
        }
        
        .tab-content {
            padding-top: 1.5rem;
        }
        
        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .stat-card-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'approvals' ? 'active' : ''; ?>" href="?tab=approvals">Approvals</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'analytics' ? 'active' : ''; ?>" href="?tab=analytics">Analytics</a>
            </li>
        </ul>
        <a href="cso_logout.php" class="btn logout-btn btn-sm align-self-start">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>

    <div class="tab-content">
        <?php if ($active_tab == 'approvals'): ?>
            <div class="row mb-4" id="stats-container">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="value" id="total-today"><?php echo $stats['total_today']; ?></div>
                        <div class="label">Visitors Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="value" id="pending-count"><?php echo $stats['pending_count']; ?></div>
                        <div class="label">Pending Approvals</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="value" id="approved-today"><?php echo $stats['approved_today']; ?></div>
                        <div class="label">Approved Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="value" id="denied-today"><?php echo $stats['denied_today']; ?></div>
                        <div class="label">Denied Today</div>
                    </div>
                </div>
            </div>

            <div class="visitor-list mt-4">
                <div class="header-bar">
                    <h1>
                        Visitor Approvals
                        <span class="badge bg-primary ms-2" id="pending-badge"><?php echo $stats['pending_count']; ?> Pending</span>
                    </h1>
                    <button onclick="refreshAll()" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>

                <div id="visitor-container">
                    <?php 
                    $pending_result = $conn->query("SELECT * FROM visitors WHERE status='pending' ORDER BY time_of_visit DESC");
                    if ($pending_result->num_rows > 0): 
                        while ($row = $pending_result->fetch_assoc()): ?>
                            <div class="visitor-item">
                                <div class="visitor-info">
                                    <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                                    <span class="badge bg-warning text-dark mb-1">Pending</span>
                                    <small><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($row['phone']); ?></small>
                                    <small><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></small>
                                    <small><i class="fas fa-user-shield me-1"></i> Visiting: <?php echo htmlspecialchars($row['host_name']); ?></small>
                                    <?php if (!empty($row['time_of_visit'])): ?>
                                        <small><i class="fas fa-calendar-day me-1"></i> <?php echo date('M j, Y g:i A', strtotime($row['time_of_visit'])); ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($row['organization'])): ?>
                                        <small><i class="fas fa-building me-1"></i> Organization: <?php echo htmlspecialchars($row['organization']); ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($row['reason'])): ?>
                                        <small><i class="fas fa-info-circle me-1"></i> Reason: <?php echo htmlspecialchars($row['reason']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="action-buttons">
                                    <a href="approve.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </a>
                                    <a href="deny.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times me-1"></i> Deny
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; 
                    else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-check fa-4x mb-3"></i>
                            <h4>No pending approvals</h4>
                            <p>All visitor requests have been processed</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Analytics Tab Content -->
            <div class="dashboard-card">
                <h3 class="mb-4">Security Analytics</h3>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-title">Weekly Visitor Trend</div>
                        <div class="chart-container">
                            <canvas id="weeklyTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-title">Top Hosts by Visitors</div>
                        <div class="chart-container">
                            <canvas id="topHostsChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="chart-title">Visitor Status Today</div>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-title">Repeat Visitors</div>
                        <div class="concentric-chart">
                            <canvas id="repeatVisitorsChart"></canvas>
                        </div>
                        <p class="text-center mt-2"><?php echo $stats['repeat_visitors']; ?> repeat visitors identified</p>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-title">Approval Rate</div>
                        <div class="concentric-chart">
                            <canvas id="approvalRateChart"></canvas>
                        </div>
                        <p class="text-center mt-2"><?php echo $stats['approval_rate']; ?>% approval rate</p>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="chart-title">Hourly Visitor Distribution Today</div>
                        <div class="chart-container">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Real-time refresh functionality
let refreshInterval = 5000; // 5 seconds
let visitorRefreshTimeout;
let statsRefreshTimeout;

function fetchVisitorList() {
    fetch('?ajax=visitors&tab=approvals')
        .then(response => response.text())
        .then(data => {
            document.getElementById('visitor-container').innerHTML = data;
            // Schedule next refresh
            visitorRefreshTimeout = setTimeout(fetchVisitorList, refreshInterval);
        })
        .catch(error => {
            console.error('Error fetching visitors:', error);
            // Retry after delay even if error occurs
            visitorRefreshTimeout = setTimeout(fetchVisitorList, refreshInterval);
        });
}

function fetchStats() {
    fetch('?ajax=stats&tab=approvals')
        .then(response => response.json())
        .then(data => {
            // Update all counters
            document.getElementById('total-today').textContent = data.total_today;
            document.getElementById('pending-count').textContent = data.pending_count;
            document.getElementById('approved-today').textContent = data.approved_today;
            document.getElementById('denied-today').textContent = data.denied_today;
            document.getElementById('pending-badge').textContent = data.pending_count + ' Pending';
            
            // Update hourly chart data if needed
            if (hourlyChart) {
                hourlyChart.data.datasets[0].data = Array.from({length: 24}, (_, i) => data.hourly_data[i] || 0);
                hourlyChart.update();
            }
            
            // Schedule next refresh
            statsRefreshTimeout = setTimeout(fetchStats, refreshInterval);
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            // Retry after delay even if error occurs
            statsRefreshTimeout = setTimeout(fetchStats, refreshInterval);
        });
}

function refreshAll() {
    // Clear any pending timeouts
    clearTimeout(visitorRefreshTimeout);
    clearTimeout(statsRefreshTimeout);
    
    // Fetch immediately
    fetchVisitorList();
    fetchStats();
}

// Start auto-refresh only on Approvals tab
if (document.querySelector('.nav-link.active').textContent === 'Approvals') {
    // Fetch immediately on page load
    fetchVisitorList();
    fetchStats();
}

// Tab switching behavior
document.querySelectorAll('.nav-link').forEach(tab => {
    tab.addEventListener('click', function() {
        // Clear any pending refreshes when switching tabs
        clearTimeout(visitorRefreshTimeout);
        clearTimeout(statsRefreshTimeout);
        
        // Only start auto-refresh if switching to Approvals tab
        if (this.textContent === 'Approvals') {
            fetchVisitorList();
            fetchStats();
        }
    });
});

<?php if ($active_tab == 'analytics'): ?>
    // Analytics tab charts initialization
    const weeklyCtx = document.getElementById('weeklyTrendChart').getContext('2d');
    const hostsCtx = document.getElementById('topHostsChart').getContext('2d');
    const repeatCtx = document.getElementById('repeatVisitorsChart').getContext('2d');
    const approvalCtx = document.getElementById('approvalRateChart').getContext('2d');
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    const statusCtx = document.getElementById('statusChart').getContext('2d');

    // Weekly Trend Chart
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            datasets: [{
                label: 'This Week',
                data: <?php echo json_encode($stats['weekly_data']); ?>,
                borderColor: 'rgba(7, 175, 139, 1)',
                backgroundColor: 'rgba(7, 175, 139, 0.1)',
                tension: 0.3,
                fill: true
            }, {
                label: 'Last Week',
                data: <?php echo json_encode($stats['last_weekly_data']); ?>,
                borderColor: 'rgba(0, 117, 112, 1)',
                backgroundColor: 'rgba(0, 117, 112, 0.1)',
                tension: 0.3,
                borderDash: [5, 5],
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Visitors'
                    }
                }
            }
        }
    });

    // Top Hosts Chart
    new Chart(hostsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($stats['top_hosts'], 'host_name')); ?>,
            datasets: [{
                label: 'Visitors',
                data: <?php echo json_encode(array_column($stats['top_hosts'], 'count')); ?>,
                backgroundColor: 'rgba(255, 202, 0, 0.7)',
                borderColor: 'rgba(255, 202, 0, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Visitors'
                    }
                }
            }
        }
    });

    // Repeat Visitors Chart
    new Chart(repeatCtx, {
        type: 'doughnut',
        data: {
            labels: ['First-time Visitors', 'Repeat Visitors'],
            datasets: [{
                data: [
                    <?php echo max(0, $stats['total_visitors'] - $stats['repeat_visitors']); ?>,
                    <?php echo $stats['repeat_visitors']; ?>
                ],
                backgroundColor: [
                    'rgba(7, 175, 139, 0.7)',
                    'rgba(255, 202, 0, 0.7)'
                ],
                borderColor: [
                    'rgba(7, 175, 139, 1)',
                    'rgba(255, 202, 0, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Approval Rate Chart
    new Chart(approvalCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Denied'],
            datasets: [{
                data: [
                    <?php echo $stats['approved_today']; ?>,
                    <?php echo $stats['denied_today']; ?>
                ],
                backgroundColor: [
                    'rgba(7, 175, 139, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(7, 175, 139, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Hourly Distribution Chart
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: 'Visitors',
                data: Array.from({length: 24}, (_, i) => <?php echo $stats['hourly_data'][i] ?? 0; ?>),
                backgroundColor: 'rgba(7, 175, 139, 0.7)',
                borderColor: 'rgba(7, 175, 139, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Visitors'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Hour of Day'
                    }
                }
            }
        }
    });

    // Status Chart
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Denied'],
            datasets: [{
                data: [
                    <?php echo $stats['pending_count']; ?>,
                    <?php echo $stats['approved_today']; ?>,
                    <?php echo $stats['denied_today']; ?>
                ],
                backgroundColor: [
                    'rgba(255, 202, 0, 0.7)',
                    'rgba(7, 175, 139, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 202, 0, 1)',
                    'rgba(7, 175, 139, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
<?php endif; ?>
</script>
</body>
</html>
<?php
$conn->close();
?>