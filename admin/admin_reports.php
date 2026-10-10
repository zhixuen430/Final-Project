
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

// Escape output
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Get filter values
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$status = trim($_GET['status'] ?? '');

// Validate date format
if (
    ($dateFrom !== '' &&
        (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) ||
         !checkdate((int)substr($dateFrom, 5, 2),
                    (int)substr($dateFrom, 8, 2),
                    (int)substr($dateFrom, 0, 4)))) ||
    ($dateTo !== '' &&
        (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) ||
         !checkdate((int)substr($dateTo, 5, 2),
                    (int)substr($dateTo, 8, 2),
                    (int)substr($dateTo, 0, 4))))
) {
    exit('Invalid date format.');
}

if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    exit('Start date cannot be later than end date.');
}

// Get booking statuses from database
$statusOptions = $pdo->query(
    "SELECT DISTINCT booking_status
     FROM bookings
     ORDER BY booking_status"
)->fetchAll(PDO::FETCH_COLUMN);

// Check selected status
if ($status !== '' && !in_array($status, $statusOptions, true)) {
    exit('Invalid booking status.');
}

// Build SQL conditions
$where = [];
$params = [];

if ($dateFrom !== '') {
    $where[] = 'b.check_in_date >= ?';
    $params[] = $dateFrom;
}

if ($dateTo !== '') {
    $where[] = 'b.check_in_date <= ?';
    $params[] = $dateTo;
}

if ($status !== '') {
    $where[] = 'b.booking_status = ?';
    $params[] = $status;
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = ' WHERE ' . implode(' AND ', $where);
}

// Get booking report
$sql = "
    SELECT
        b.booking_id,
        b.check_in_date,
        b.check_out_date,
        b.total_price,
        b.booking_status,
        u.fullname,
        h.hotel_name,
        r.room_number
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN hotels_page h ON b.hotel_id = h.hotel_id
    JOIN rooms r ON b.room_id = r.room_id
    $whereSql
    ORDER BY b.booking_id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate report totals
$totalRecords = count($bookings);
$bookingValue = 0;

foreach ($bookings as $booking) {
    if (strtolower($booking['booking_status']) !== 'cancelled') {
        $bookingValue += (float)($booking['total_price'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking Reports</title>

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
            text-decoration: none;
        }

        .main-content {
            padding: 35px;
        }

        .report-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .summary-icon {
            color: #2563eb;
            font-size: 27px;
            margin-bottom: 10px;
        }

        .summary-label {
            color: #6b7280;
            font-size: 14px;
        }

        .summary-value {
            font-size: 25px;
            font-weight: bold;
            margin-top: 8px;
            overflow-wrap: anywhere;
        }

        .btn-primary-custom {
            background: #0f2747;
            color: white;
            border: none;
        }

        .btn-primary-custom:hover {
            background: #1e3a5f;
            color: white;
        }

        .table thead th {
            background: #0f2747;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

    </style>
</head>

<body>

<nav class="navbar no-print">
    <a class="navbar-brand" href="admin-dashboard.php">
        <i class="bi bi-building"></i>
        HOTEL BOOKING
    </a>
</nav>

<div class="container-fluid main-content">

    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 mb-4">

        <div>
            <h2 class="fw-bold">Booking Reports</h2>
            <p class="text-secondary mb-0">
                Review booking records and booking values.
            </p>
        </div>

        <div class="d-flex gap-2 no-print">

            <a href="admin_dashboard.php"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="report-card filter-card">
        <h5 class="fw-bold mb-3">
            <i class="bi bi-funnel"></i> Filter Bookings
        </h5>

        <form method="GET">
            <div class="row g-3">

                <div class="col-12 col-md-4">
                    <label for="date_from" class="form-label">
                        Check-in Date From
                    </label>
                    <input type="date"
                           name="date_from"
                           id="date_from"
                           class="form-control"
                           value="<?= e($dateFrom) ?>">
                </div>

                <div class="col-12 col-md-4">
                    <label for="date_to" class="form-label">
                        Check-in Date To
                    </label>
                    <input type="date"
                           name="date_to"
                           id="date_to"
                           class="form-control"
                           value="<?= e($dateTo) ?>">
                </div>

                <div class="col-12 col-md-4">
                    <label for="status" class="form-label">
                        Booking Status
                    </label>

                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>

                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?= e($option) ?>"
                                <?= $status === $option ? 'selected' : '' ?>>
                                <?= e(ucwords(str_replace('_', ' ', $option))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-search"></i> Apply Filters
                    </button>

                    <a href="admin_reports.php"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>

    <!-- Report Summary -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-6">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="summary-label">Total Matching Bookings</div>
                <div class="summary-value">
                    <?= number_format($totalRecords) ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="summary-label">
                    Booking Value Excluding Cancelled
                </div>
                <div class="summary-value">
                    RM <?= number_format($bookingValue, 2) ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Booking Table -->
    <div class="report-card">
        <h5 class="fw-bold mb-3">
            <i class="bi bi-file-earmark-text"></i>
            Booking Details
        </h5>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Hotel</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total Price</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                No booking records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    #<?= (int)$booking['booking_id'] ?>
                                </td>

                                <td><?= e($booking['fullname']) ?></td>

                                <td><?= e($booking['hotel_name']) ?></td>

                                <td><?= e($booking['room_number']) ?></td>

                                <td><?= e($booking['check_in_date']) ?></td>

                                <td><?= e($booking['check_out_date']) ?></td>

                                <td>
                                    RM <?= number_format(
                                        (float)$booking['total_price'], 2
                                    ) ?>
                                </td>

                                <td>
                                    <?php
                                    $currentStatus = strtolower(
                                        $booking['booking_status']
                                    );

                                    $badgeClass = 'text-bg-secondary';

                                    if ($currentStatus === 'pending') {
                                        $badgeClass = 'text-bg-warning';
                                    } elseif (
                                        in_array(
                                            $currentStatus,
                                            ['confirmed', 'checked_in', 'checked_out'],
                                            true
                                        )
                                    ) {
                                        $badgeClass = 'text-bg-success';
                                    } elseif ($currentStatus === 'cancelled') {
                                        $badgeClass = 'text-bg-danger';
                                    }
                                    ?>

                                    <span class="badge <?= e($badgeClass) ?>">
                                        <?= e(ucwords(str_replace(
                                            '_', ' ', $currentStatus
                                        ))) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="text-secondary small mb-0">
            Report records are filtered by check-in date.
        </p>
    </div>

</div>

</body>
</html>