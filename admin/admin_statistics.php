
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

// Only admin can access this page
if (
    !isset($_SESSION['user']['role']) ||
    $_SESSION['user']['role'] !== 'admin'
) {
    header('Location: ../home.php');
    exit;
}

// Count total users
$totalUsers = $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

// Count total hotels
$totalHotels = $pdo->query(
    "SELECT COUNT(*) FROM hotels_page"
)->fetchColumn();

// Count total rooms
$totalRooms = $pdo->query(
    "SELECT COUNT(*) FROM rooms"
)->fetchColumn();

// Count total bookings
$totalBookings = $pdo->query(
    "SELECT COUNT(*) FROM bookings"
)->fetchColumn();

// Count pending bookings
$pendingBookings = $pdo->query(
    "SELECT COUNT(*) FROM bookings
     WHERE booking_status = 'pending'"
)->fetchColumn();

// Count cancelled bookings
$cancelledBookings = $pdo->query(
    "SELECT COUNT(*) FROM bookings
     WHERE booking_status = 'cancelled'"
)->fetchColumn();

// Calculate booking value excluding cancelled bookings
$bookingValue = $pdo->query(
    "SELECT COALESCE(SUM(total_price), 0)
     FROM bookings
     WHERE booking_status <> 'cancelled'"
)->fetchColumn();

// Get booking statistics by status
$statusSql = "
    SELECT booking_status, COUNT(*) AS total
    FROM bookings
    GROUP BY booking_status
    ORDER BY booking_status
";

$statusStats = $pdo->query($statusSql)->fetchAll(PDO::FETCH_ASSOC);

// Escape output
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Statistics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            color: #0f2747;
        }

        .navbar {
            background: #0f2747;
            padding: 16px 30px;
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
        }

        .navbar-brand:hover {
            color: #c9a227;
        }

        .main-content {
            padding: 35px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 12px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }

        .stat-number {
            font-size: 27px;
            font-weight: bold;
            margin-top: 8px;
            overflow-wrap: anywhere;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 25px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .table thead th {
            background: #0f2747;
            color: white;
            white-space: nowrap;
        }

        .btn-back {
            background: #0f2747;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
        }

        .btn-back:hover {
            background: #1e3a5f;
            color: white;
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a class="navbar-brand text-decoration-none" href="admin_dashboard.php">
        <i class="bi bi-building"></i>
        HOTEL BOOKING
    </a>
</nav>

<div class="container-fluid main-content">

    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 mb-4">

        <div>
            <h2 class="fw-bold">Statistics & Reports</h2>
            <p class="text-secondary mb-0">
                View hotel booking statistics and booking values.
            </p>
        </div>

        <a href="admin_dashboard.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-label">Total Users</div>
                <div class="stat-number">
                    <?= number_format((int)$totalUsers) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="stat-label">Total Hotels</div>
                <div class="stat-number">
                    <?= number_format((int)$totalHotels) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-door-open"></i>
                </div>
                <div class="stat-label">Total Rooms</div>
                <div class="stat-number">
                    <?= number_format((int)$totalRooms) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-label">Total Bookings</div>
                <div class="stat-number">
                    <?= number_format((int)$totalBookings) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-label">Pending Bookings</div>
                <div class="stat-number">
                    <?= number_format((int)$pendingBookings) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-label">Cancelled Bookings</div>
                <div class="stat-number">
                    <?= number_format((int)$cancelledBookings) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-label">
                    Booking Value Excluding Cancelled
                </div>
                <div class="stat-number">
                    RM <?= number_format((float)$bookingValue, 2) ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Booking Status Statistics -->
    <div class="section-card">
        <h5 class="fw-bold mb-3">
            <i class="bi bi-bar-chart"></i>
            Booking Status Statistics
        </h5>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Booking Status</th>
                        <th>Total Bookings</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($statusStats)): ?>
                        <tr>
                            <td colspan="2" class="text-center">
                                No booking records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($statusStats as $stat): ?>
                            <tr>
                                <td><?= e($stat['booking_status']) ?></td>
                                <td><?= number_format((int)$stat['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>