<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if (!isset($_SESSION['receptionist_id'])) {
    header("Location: vmc_login.php");
    exit();
}

// Handle check-out
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_out_id'])) {
    $check_out_id = $_POST['check_out_id'];
    
    // Update status to 'checked_out' and set check_out_time
    $stmt = $conn->prepare("UPDATE visitors SET status = 'checked_out', check_out_time = NOW() WHERE id = ?");
    $stmt->bind_param("i", $check_out_id);
    
    if ($stmt->execute()) {
        // Success - reload the page to show updated status
        header("Location: vmc_dashboard.php?success=checked_out");
        exit();
    } else {
        // Error - show message
        $error_message = "Error checking out guest: " . $conn->error;
    }
    $stmt->close();
}

// Fetch logged-in receptionist name
$receptionist_name = "Receptionist";
if (isset($_SESSION['receptionist_id'])) {
    $rec_id = $_SESSION['receptionist_id'];
    $stmt = $conn->prepare("SELECT name FROM receptionists WHERE id = ?");
    $stmt->bind_param("i", $rec_id);
    $stmt->execute();
    $stmt->bind_result($receptionist_name);
    $stmt->fetch();
    $stmt->close();
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Search functionality
$search_term = isset($_GET['search']) ? trim($_GET['search']) : "";
$search_mode = !empty($search_term);

// Fetch approved guests with pagination
// Fetch visitors with pagination and search
if ($search_mode) {
    $like_term = '%' . $conn->real_escape_string($search_term) . '%';
    
    // Approved visitors
    $stmt = $conn->prepare("SELECT id, name, phone, email, host_name, visit_date, status, organization, floor_of_visit 
                          FROM visitors 
                          WHERE status = 'approved' 
                          AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR host_name LIKE ?)
                          LIMIT ?, ?");
    $stmt->bind_param("ssssii", $like_term, $like_term, $like_term, $like_term, $start, $per_page);
    $stmt->execute();
    $approved_guests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Checked-in visitors
    $stmt = $conn->prepare("SELECT id, name, phone, email, host_name, visit_date, check_in_time, status, organization 
                          FROM visitors 
                          WHERE status = 'checked_in' 
                          AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR host_name LIKE ?)
                          LIMIT ?, ?");
    $stmt->bind_param("ssssii", $like_term, $like_term, $like_term, $like_term, $start, $per_page);
    $stmt->execute();
    $authenticated_guests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Checked-out visitors (FIXED)
    $stmt = $conn->prepare("SELECT id, name, phone, email, host_name, visit_date, check_in_time, check_out_time, organization, floor_of_visit, reason 
                          FROM visitors 
                          WHERE status = 'checked_out' 
                          AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR host_name LIKE ?)
                          LIMIT ?, ?");
    $stmt->bind_param("ssssii", $like_term, $like_term, $like_term, $like_term, $start, $per_page);
    $stmt->execute();
    $checked_out_guests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get counts for search results
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visitors WHERE status = 'approved' AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR host_name LIKE ?)");
    $stmt->bind_param("ssss", $like_term, $like_term, $like_term, $like_term);
    $stmt->execute();
    $total_approved = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visitors WHERE status = 'checked_in' AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR host_name LIKE ?)");
    $stmt->bind_param("ssss", $like_term, $like_term, $like_term, $like_term);
    $stmt->execute();
    $total_authenticated = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visitors WHERE status = 'checked_out' AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR host_name LIKE ?)");
    $stmt->bind_param("ssss", $like_term, $like_term, $like_term, $like_term);
    $stmt->execute();
    $total_checked_out = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
} else {
    // Default non-search queries
    $approved_guests = $conn->query("SELECT id, name, phone, email, host_name, visit_date, status, organization, floor_of_visit 
                                   FROM visitors 
                                   WHERE status = 'approved' 
                                   LIMIT $start, $per_page")->fetch_all(MYSQLI_ASSOC);
    
    $authenticated_guests = $conn->query("SELECT id, name, phone, email, host_name, visit_date, check_in_time, status, organization 
                                        FROM visitors 
                                        WHERE status = 'checked_in' 
                                        LIMIT $start, $per_page")->fetch_all(MYSQLI_ASSOC);
    
    $checked_out_guests = $conn->query("SELECT id, name, phone, email, host_name, visit_date, check_in_time, check_out_time, organization, floor_of_visit, reason 
                                      FROM visitors 
                                      WHERE status = 'checked_out' 
                                      LIMIT $start, $per_page")->fetch_all(MYSQLI_ASSOC);

    // Get total counts
    $total_approved = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status = 'approved'")->fetch_assoc()['count'];
    $total_authenticated = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status = 'checked_in'")->fetch_assoc()['count'];
    $total_checked_out = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status = 'checked_out'")->fetch_assoc()['count'];
}

// Calculate total pages
$total_pages = max(
    ceil($total_approved / $per_page),
    ceil($total_authenticated / $per_page),
    ceil($total_checked_out / $per_page)
);
$total_pages = max(ceil($total_approved / $per_page), ceil($total_authenticated / $per_page));



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #07AF8B;
            --accent: #FFCA00;
            --dark: #007570;
            --light: #F8F9FA;
            --white: #FFFFFF;
        }

        body {
            background: #F5F7FA;
            font-family: 'Inter', sans-serif;
        }
        
        
.table-hover tbody tr {
    transition: all 0.3s ease;
}

.animate__animated {
    animation-duration: 0.5s;
}
        
        .header-bar {
    background: var(--white);
    border-bottom: 1px solid #e0e0e0;
        }
        
        .header-bar span {
    color: #333333;
        }
        
        header-bar a {
    background: var(--primary); /* Use green color instead of yellow */
    color: white;
    border: 1px solid var(--primary); /* Optional: adds border */
}
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header-bar sticky-top shadow-sm d-flex justify-content-between align-items-center px-4 py-3" style="background: white; border-bottom: 1px solid #e0e0e0;">
    <div class="d-flex align-items-center">
        <img src="assets/logo-green-yellow.png" alt="Logo" style="height: 40px; margin-right: 15px;">
        <span class="text-dark fs-5 fw-medium">Visitor Management Center</span>
    </div>
    <div class="d-flex align-items-center">
        <span class="text-dark me-3">Welcome, <?= htmlspecialchars($receptionist_name) ?></span>
        <a href="vmc_logout.php" class="btn btn-sm text-white" style="background: var(--primary);">Logout</a>
    </div>
</div>

    <!-- Main Container -->
    <div class="d-flex align-items-center ms-3">
   <!--  <button id="refreshBtn" class="btn btn-sm btn-icon" 
            style="background: var(--light); color: var(--dark); border: 1px solid rgba(0,0,0,0.1);"
            title="Refresh Data" aria-label="Refresh dashboard data">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
        </svg>
    </button> -->
</div>

    <div class="container-fluid py-4 px-4">
        <!-- Search Bar -->
<form method="GET" class="row justify-content-center mb-4">
    <div class="col-md-6">
        <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email, or host"
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-custom">Search</button>
        <?php if (!empty($_GET['search'])): ?>
            <a href="vmc_dashboard.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($search_mode): ?>
    <div class="alert alert-info alert-dismissible fade show text-center m-0 rounded-0" role="alert" style="background-color: #FFCA00; color: #212529;">
        <strong>Search Result:</strong> Showing results for "<strong><?= htmlspecialchars($search_term) ?></strong>"
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

        <!-- Search Bar -->
       <!-- <div class="row justify-content-center mb-4 animate__animated animate__fadeIn">
            <div class="col-lg-8">
                <form method="GET" class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control border-2 border-end-0 py-2" placeholder="Search visitors..." value="<?= htmlspecialchars($search_term) ?>">
                    <button class="btn border-2 border-start-0" type="submit" style="background: var(--accent);">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if ($search_mode): ?>
                    <a href="vmc_dashboard.php" class="btn ms-2" style="background: var(--light);">
                        Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div> -->

        <!-- Register Button -->
        <div class="text-center mb-4">
            <a href="register_walkin.php" class="btn btn-lg shadow-sm px-4 py-2 animate__animated animate__pulse" style="background: var(--primary); color: white;">
                <i class="bi bi-person-plus-fill me-2"></i>Walk-in Visitor
            </a>

            <a href="register_for_staff.php" class="btn btn-lg shadow-sm px-4 py-2 animate__animated animate__pulse" style="background: var(--dark); color: white;">
                <i class="bi bi-person-plus-fill me-2"></i>Request Visit for Staff
            </a>
            
            <!--<a href="export_visitors.php" class="btn btn-lg shadow-sm px-4 py-2" style="background: #6c757d; color: white;">
                <i class="bi bi-download me-2"></i>Export Today's Visitors
            </a> -->
</div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-medium" id="checkedin-tab" data-bs-toggle="tab" data-bs-target="#checkedin" type="button" role="tab" style="color: var(--dark);">
                    <i class="bi bi-people-fill me-2"></i>Checked-In Visitors
                    <span class="badge bg-primary ms-2"><?= $total_authenticated ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-medium" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" style="color: var(--dark);">
                    <i class="bi bi-check-circle-fill me-2"></i>Approved Visitors
                    <span class="badge bg-success ms-2"><?= $total_approved ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
    <button class="nav-link fw-medium" id="checkedout-tab" data-bs-toggle="tab" data-bs-target="#checkedout" type="button" role="tab" style="color: var(--dark);">
        <i class="bi bi-box-arrow-left me-2"></i>Checked-Out Visitors
        <span class="badge bg-secondary ms-2"><?= $total_checked_out ?? 0 ?></span>
    </button>
</li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabsContent">
            <!-- Checked-In Visitors Tab -->
            <div class="tab-pane fade show active" id="checkedin" role="tabpanel">
                <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
                    <div class="card-body p-0">
                        <?php if (empty($authenticated_guests)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No visitors currently checked-in</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: var(--primary); color: white;">
                                    <tr>
                                        <th width="18%">Visitor</th>
                                        <th width="15%">Contact</th>
                                        <th width="18%">Host</th>
                                        <th width="12%">Visit Date</th>
                                        <th width="15%">Check-In Time</th>
                                        <th width="22%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($authenticated_guests as $guest): ?>
                                    <tr class="animate__animated animate__fadeIn">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label" style="
                                                        background: #E6F7F4; 
                                                        color: var(--dark);
                                                        width: 40px;
                                                        height: 40px;
                                                        display: flex;
                                                        align-items: center;
                                                        justify-content: center;
                                                        border-radius: 50%;
                                                        font-weight: 600;
                                                    ">
                                                        <?= strtoupper(substr($guest['name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($guest['name']) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($guest['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($guest['phone']) ?></td>
                                        <td><?= htmlspecialchars($guest['host_name']) ?></td>
                                        <td>
                                            <?php 
    if (!empty($guest['visit_date']) && $guest['visit_date'] != '0000-00-00') {
        echo htmlspecialchars(date('Y-m-d', strtotime($guest['visit_date'])));
    } else {
        echo 'N/A';
    }
    ?>
                                        </td>
                                        <td><?= date('g:i A', strtotime($guest['check_in_time'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm me-2" style="background: var(--accent);" onclick="openCameraModal(<?= $guest['id'] ?>, '<?= htmlspecialchars($guest['name']) ?>', '<?= htmlspecialchars($guest['host_name']) ?>')">
                                                <i class="bi bi-camera-fill me-1"></i>Card
                                            </button>
                                            <form method="POST" action="vmc_dashboard.php" class="d-inline">
                                                <input type="hidden" name="check_out_id" value="<?= $guest['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Check Out</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Approved Visitors Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
                    <div class="card-body p-0">
                        <?php if (empty($approved_guests)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No approved visitors awaiting check-in</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: var(--primary); color: white;">
                                    <tr>
                                        <th width="20%">Visitor</th>
                                        <th width="15%">Contact</th>
                                        <th width="20%">Host</th>
                                        <th width="15%">Visit Date</th>
                                        <th width="30%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approved_guests as $guest): ?>
                                    <tr class="animate__animated animate__fadeIn">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label" style="
                                                        background: #E6F7F4;
                                                        color: var(--dark);
                                                        width: 40px;
                                                        height: 40px;
                                                        display: flex;
                                                        align-items: center;
                                                        justify-content: center;
                                                        border-radius: 50%;
                                                        font-weight: 600;
                                                    ">
                                                        <?= strtoupper(substr($guest['name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($guest['name']) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($guest['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($guest['phone']) ?></td>
                                        <td><?= htmlspecialchars($guest['host_name']) ?></td>
                                        <td>
                                            <?php 
    if (!empty($guest['visit_date']) && $guest['visit_date'] != '0000-00-00') {
        echo htmlspecialchars(date('Y-m-d', strtotime($guest['visit_date'])));
    } else {
        echo 'N/A';
    }
    ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm me-2" style="background: var(--accent);" onclick="openCameraModal(
        <?= $guest['id'] ?>, 
        '<?= htmlspecialchars($guest['name'], ENT_QUOTES) ?>', 
        '<?= htmlspecialchars($guest['host_name'], ENT_QUOTES) ?>',
        '<?= htmlspecialchars($guest['organization'] ?? 'N/A', ENT_QUOTES) ?>',
        '<?= htmlspecialchars(date('jS F, Y', strtotime($guest['visit_date'])), ENT_QUOTES) ?>',
        '<?= htmlspecialchars($guest['floor_of_visit'] ?? 'N/A', ENT_QUOTES) ?>'
    )">
                                                <i class="bi bi-camera-fill me-1"></i>Card
                                            </button>

                                            <!--<button class="btn btn-sm me-2" 
                                                    style="background: var(--accent);"
                                                    onclick="openCameraModal(<?= $guest['id'] ?>, '<?= htmlspecialchars($guest['name']) ?>', '<?= htmlspecialchars($guest['host_name']) ?>')">
                                                <i class="bi bi-camera-fill me-1"></i>Card
                                            </button> -->
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#checkoutModal<?= $guest['id'] ?>">
                                                <i class="bi bi-box-arrow-right me-1"></i>Cancel
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Checked-out Visitors Tab -->
<div class="tab-pane fade" id="checkedout" role="tabpanel">
    <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
        <div class="card-body p-0">
            <?php if (empty($checked_out_guests)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-arrow-left text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No checked-out visitors available</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: var(--primary); color: white;">
                        <tr>
                            <th width="18%">Visitor</th>
                            <th width="12%">Contact</th>
                            <th width="15%">Host</th>
                            <th width="10%">Visit Date</th>
                            <th width="12%">Check-out Time</th>
                            <th width="15%">Floor/Venue</th>
                            <th width="18%">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checked_out_guests as $guest): ?>
                        <tr class="animate__animated animate__fadeIn">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label" style="
                                            background: #E6F7F4;
                                            color: var(--dark);
                                            width: 40px;
                                            height: 40px;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            border-radius: 50%;
                                            font-weight: 600;
                                        ">
                                            <?= strtoupper(substr($guest['name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($guest['name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($guest['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($guest['phone']) ?></td>
                            <td><?= htmlspecialchars($guest['host_name']) ?></td>
                            <td>
                                <?php 
                                    if (!empty($guest['visit_date']) && $guest['visit_date'] != '0000-00-00') {
                                        echo htmlspecialchars(date('Y-m-d', strtotime($guest['visit_date'])));
                                    } else {
                                        echo 'N/A';
                                    }
                                ?>
                            </td>
                            <td>
                                <?= !empty($guest['check_out_time']) ? date('g:i A', strtotime($guest['check_out_time'])) : 'N/A' ?>
                            </td>
                            <td>
                                    <?= htmlspecialchars($guest['floor_of_visit'] ?? 'N/A') ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($guest['reason'] ?? 'N/A') ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
            
        </div> <!-- End of tab-content -->
    </div> <!-- End of container -->

    <!-- Checkout Modals -->
    <?php foreach (array_merge($authenticated_guests, $approved_guests) as $guest): ?>
    <div class="modal fade" id="checkoutModal<?= $guest['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background: var(--primary); color: white;">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Are you sure you want to <strong><?= $guest['status'] === 'checked_in' ? 'check out' : 'cancel' ?></strong> the visit for:</p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="symbol symbol-50px me-3">
                            <span class="symbol-label" style="
                            background: #E6F7F4;
                            color: var(--dark);
                            width: 50px;
                            height: 50px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 50%;
                            font-weight: 600;
                            font-size: 1.2rem;
                        ">
                                <?= strtoupper(substr($guest['name'], 0, 1)) ?>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($guest['name']) ?></h6>
                            <small class="text-muted">Host: <?= htmlspecialchars($guest['host_name']) ?></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form method="POST">
                        <input type="hidden" name="check_out_id" value="<?= $guest['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            <?= $guest['status'] === 'checked_in' ? 'Check Out' : 'Cancel Visit' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <!-- Camera & ID Card Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background: var(--primary); color: white;">
                    <h5 class="modal-title">Visitor Card</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Camera Feed Column 
                    <div class="col-md-6 border-end pe-3">
                        <div class="text-center mb-3">
                            <video id="videoFeed" width="100%" autoplay class="rounded" style="background: #000;"></video>
                        </div>
                        <div class="text-center">
                            <button id="captureBtn" class="btn" style="background: var(--accent);">
                                <i class="bi bi-camera-fill me-2"></i>Capture Photo
                            </button>
                        </div>
                    </div>-->

                        <!-- ID Card Preview Column -->
                        <div id="cardPreview">
                            <div class="card mx-auto shadow-sm" style="width: 2.358in; height: 3.735in; border: 1px solid #07AF8B; display: flex; flex-direction: row; font-family: 'Inter', sans-serif; font-size: 13px; overflow: hidden;">

                                <!-- Vertical Strip -->
                                <div style="width: 0.5in; background-color: #007570; color: white; writing-mode: vertical-lr; text-orientation: mixed; text-align: center; font-weight: bold; font-size: 28px; display: flex; align-items: center; justify-content: center; letter-spacing: 1px;">
                                    VISITOR PASS
                                </div>

                                <!-- Main Content -->
                                <div style="padding: 10px 12px; display: flex; flex-direction: column; justify-content: space-between; width: 2in;">

                                    <!-- Top Logos -->
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 6px;">
                                        <img src="assets/logo-green-yellow.png" alt="Afreximbank" style="height: 28px;">
                                        <!-- <img src="assets/logo-trade-center.png" alt="African Trade Centre" style="height: 28px;"> -->
                                    </div>
                                    


                                    <!-- Visitor Details -->
                                    <div style="line-height: 1.6;">
                                      <!--  <p><strong>Name:</strong> <span id="visitorName">Sunday Chiejina</span></p> -->
                                      <!--  <p><strong>Organisation:</strong> <span id="visitorOrg">The Nation</span></p>-->
                                        <p><strong>Venue:</strong> <span id="visitorFloor">Conference Hall</span></p>
                                      <!--  <p><strong>Date:</strong> <span id="visitorDate">8th April, 2025</span></p>-->
                                     <!--   <p><strong>Host:</strong> <span id="visitorHost">Afreximbank</span></p>-->
                                    </div>

                                    <!-- Bottom Logo -->
                                    <div style="text-align: center; margin-top: 6px;">
                                        <img src="assets/logo-green-yellow.png" alt="Afreximbank Logo" style="height: 35px;">
                                    </div>
                                </div>
                            </div>
                           <!-- <div class="text-center mt-3">
                                <button class="btn btn-outline-primary" onclick="printVisitorCard()">
                                    <i class="bi bi-printer me-1"></i> Print Card
                                </button>
                            </div> -->

                        </div>



                    </div>
                </div>
                
                <!-- Print Section -->
                <div class="text-center mt-3">
                                <button class="btn btn-outline-primary" onclick="printVisitorCard()">
                                    <i class="bi bi-printer me-1"></i> Print Card
                                </button>
                            </div>
                
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global variables
        let currentVisitor = {};
        let videoStream = null;

        // Open camera modal with visitor data
        //function openCameraModal(id, name, host) {
        //  currentVisitor = { id, name, host };
        //const modal = new bootstrap.Modal('#cameraModal');
        //modal.show();

        // Reset UI
        //document.getElementById('cardPreview').classList.add('d-none');
        //document.getElementById('cameraInstructions').classList.remove('d-none');
        //document.getElementById('captureBtn').disabled = false;

        // Start camera
        //startCamera();
        //} 


        function openCameraModal(id, name, host, org, date, floor) {
            const modal = new bootstrap.Modal('#cameraModal');
            modal.show();

            document.getElementById('visitorName').textContent = name;
            document.getElementById('visitorHost').textContent = host;
            document.getElementById('visitorOrg').textContent = org;
            document.getElementById('visitorDate').textContent = date;
            document.getElementById('visitorFloor').textContent = floor;
        }



        // Initialize camera
        function startCamera() {
            const video = document.getElementById('videoFeed');

            // Stop any existing stream
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }

            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(stream => {
                    video.srcObject = stream;
                    videoStream = stream;
                })
                .catch(err => {
                    console.error("Camera error:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Camera Access Denied',
                        text: 'Please enable camera permissions to generate ID cards',
                        confirmButtonColor: 'var(--primary)'
                    });
                });
        }

        // Capture photo from video feed
        document.getElementById('captureBtn').addEventListener('click', function() {
            const video = document.getElementById('videoFeed');
            const canvas = document.getElementById('visitorPhoto');
            const ctx = canvas.getContext('2d');

            // Draw image to canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Update card details
            document.getElementById('visitorName').textContent = currentVisitor.name;
            document.getElementById('visitorHost').textContent = currentVisitor.host;

            // Switch UI to preview mode
            document.getElementById('cardPreview').classList.remove('d-none');
            document.getElementById('cameraInstructions').classList.add('d-none');
            this.disabled = true;

            // Stop camera
            videoStream.getTracks().forEach(track => track.stop());
        });

        // Print ID card
        document.getElementById('printCardBtn').addEventListener('click', function() {
            const printContent = document.getElementById('cardPreview').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;

            // Restart camera
            startCamera();
            document.getElementById('cardPreview').classList.add('d-none');
            document.getElementById('cameraInstructions').classList.remove('d-none');
            document.getElementById('captureBtn').disabled = false;
        });

        // Live search functionality
        document.querySelector('input[name="search"]').addEventListener('input', function() {
            const term = this.value.trim().toLowerCase();
            const tables = document.querySelectorAll('.table tbody');

            tables.forEach(tbody => {
                tbody.querySelectorAll('tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        });
        // Initialize tabs properly
        document.addEventListener('DOMContentLoaded', function() {
            const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabEls.forEach(tabEl => {
                tabEl.addEventListener('shown.bs.tab', function() {
                    // Force redraw of tab content
                    const target = document.querySelector(tabEl.dataset.bsTarget);
                    target.classList.add('animate__animated', 'animate__fadeIn');
                });
            });
        });


        // Show success message if present in URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');

            if (message) {
                const [type, msg] = message.split(':');
                Swal.fire({
                    icon: type === 'success' ? 'success' : 'error',
                    title: msg,
                    confirmButtonColor: 'var(--primary)'
                });

                // Clean URL
                history.replaceState(null, null, window.location.pathname);
            }
        });

        function printVisitorCard() {
            const printContent = document.getElementById('cardPreview').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload(); // Reload to restore event handlers and page state
        }
    </script>
    <script>
// Auto-refresh function
function refreshDashboard() {
    fetch('vmc_dashboard.php?ajax=1&' + new URLSearchParams(window.location.search))
        .then(response => response.json())
        .then(data => {
            // Update Approved Visitors tab
            updateTable('#approved tbody', data.approved_guests);
            // Update Checked-In Visitors tab
            updateTable('#checkedin tbody', data.authenticated_guests);
            
            // Update badge counts
            document.querySelector('#approved-tab .badge').textContent = data.total_approved;
            document.querySelector('#checkedin-tab .badge').textContent = data.total_authenticated;
        })
        .catch(error => console.error('Error refreshing data:', error));
}

// Helper function to update table rows
function updateTable(selector, guests) {
    const tbody = document.querySelector(selector);
    if (!tbody) return;
    
    tbody.innerHTML = guests.map(guest => `
        <tr class="animate__animated animate__fadeIn">
            <td>
                <div class="d-flex align-items-center">
                    ${guest.is_walkin ? '<span class="badge bg-warning me-2"><i class="fas fa-walking me-1"></i>Walk-in</span>' : ''}
                    <div>
                        <div class="fw-medium">${escapeHtml(guest.name)}</div>
                        <div class="text-muted small">${escapeHtml(guest.email)}</div>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(guest.phone)}</td>
            <td>${escapeHtml(guest.host_name)}</td>
            <td>${guest.visit_date && guest.visit_date != '0000-00-00' ? escapeHtml(guest.visit_date) : 'N/A'}</td>
            ${selector === '#checkedin tbody' ? `<td>${formatTime(guest.check_in_time)}</td>` : ''}
            <td>
                <button class="btn btn-sm me-2" style="background: var(--accent);" 
                    onclick="openCameraModal(${guest.id}, '${escapeHtml(guest.name)}', '${escapeHtml(guest.host_name)}')">
                    <i class="bi bi-camera-fill me-1"></i>Card
                </button>
                ${selector === '#checkedin tbody' ? 
                    `<form method="POST" class="d-inline">
                        <input type="hidden" name="check_out_id" value="${guest.id}">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Check Out</button>
                    </form>` : 
                    `<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                        data-bs-target="#checkoutModal${guest.id}">
                        <i class="bi bi-box-arrow-right me-1"></i>Cancel
                    </button>`}
            </td>
        </tr>
    `).join('');
}

// Helper functions
function escapeHtml(unsafe) {
    return unsafe ? unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;") : '';
}

function formatTime(timeString) {
    if (!timeString) return 'N/A';
    const time = new Date(timeString);
    return time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Start auto-refresh (every 7 seconds)
setInterval(refreshDashboard, 7000);

// Initial load after 1 second (optional)
setTimeout(refreshDashboard, 1000);
</script>

<script>
    // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        // Add visual feedback
        this.classList.add('animate__animated', 'animate__rotateIn');
        
        // Trigger refresh
        refreshDashboard();
        
        // Remove animation class after it completes
        setTimeout(() => {
            this.classList.remove('animate__animated', 'animate__rotateIn');
        }, 1000);
        
        // Show toast notification
        const toast = new bootstrap.Toast(document.createElement('div'));
        toast._element.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
        toast._element.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i> Dashboard refreshed
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast._element);
        toast.show();
        
        // Remove toast after it hides
        toast._element.addEventListener('hidden.bs.toast', () => {
            toast._element.remove();
        });
    });
</script>

</body>

</html>