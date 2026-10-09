<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only staff can access this page
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

if (
    !isset($_SESSION['user']['role']) ||
    $_SESSION['user']['role'] !== 'staff'
) {
    header('Location: ../home.php');
    exit;
}

$message = '';
$error = '';

// Handle check-in and check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bookingId = filter_input(
        INPUT_POST,
        'booking_id',
        FILTER_VALIDATE_INT
    );

    $action = $_POST['action'] ?? '';

    if (!$bookingId) {
        $error = 'Invalid booking ID.';
    } else {
        try {
            if ($action === 'check_in') {

                // Only pending or confirmed bookings can check in
                $sql = "UPDATE bookings
                        SET booking_status = 'checked_in'
                        WHERE booking_id = ?
                        AND booking_status IN ('pending', 'confirmed')";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$bookingId]);

                if ($stmt->rowCount() > 0) {
                    $message = 'Guest checked in successfully.';
                } else {
                    $error = 'This booking cannot be checked in.';
                }

            } elseif ($action === 'check_out') {

                // Only checked-in bookings can check out
                $sql = "UPDATE bookings
                        SET booking_status = 'checked_out'
                        WHERE booking_id = ?
                        AND booking_status = 'checked_in'";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$bookingId]);

                if ($stmt->rowCount() > 0) {
                    $message = 'Guest checked out successfully.';
                } else {
                    $error = 'This booking cannot be checked out.';
                }

            } else {
                $error = 'Invalid action.';
            }

        } catch (PDOException $e) {
            $error = 'Unable to update booking status.';
        }
    }
}

// Search bookings
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT
        b.booking_id,
        b.check_in_date,
        b.check_out_date,
        b.total_price,
        b.booking_status,
        u.fullname,
        u.email,
        h.hotel_name,
        r.room_number,
        r.room_type
    FROM bookings b
    LEFT JOIN users u
        ON b.user_id = u.user_id
    LEFT JOIN hotels_page h
        ON b.hotel_id = h.hotel_id
    LEFT JOIN rooms r
        ON b.room_id = r.room_id
";

$params = [];

if ($search !== '') {
    $sql .= "
        WHERE u.fullname LIKE ?
        OR u.email LIKE ?
        OR CAST(b.booking_id AS CHAR) LIKE ?
        OR h.hotel_name LIKE ?
    ";

    $keyword = '%' . $search . '%';

    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];
}

$sql .= " ORDER BY b.booking_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Bookings | Staff</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        .navbar {
            background: #0f2747;
        }

        .page-title {
            color: #0f2747;
            font-weight: bold;
        }

        .booking-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .table thead th {
            background: #0f2747;
            color: white;
            white-space: nowrap;
            font-size: 13px;
            padding: 14px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 14px;
            font-size: 13px;
        }

        .status {
            display: inline-block;
            min-width: 100px;
            padding: 6px 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #cfe2ff;
            color: #084298;
        }

        .status-checked_in {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-checked_out {
            background: #e2e3e5;
            color: #41464b;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .btn-navy {
            background: #0f2747;
            color: white;
        }

        .btn-navy:hover {
            background: #1b416f;
            color: white;
        }

        .table-responsive {
            border-radius: 10px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a href="staff_dashboard.php"
       class="navbar-brand fw-bold">
        HOTEL BOOKING
    </a>

    <div class="d-flex gap-3 align-items-center">
        <a href="staff_dashboard.php"
           class="btn btn-outline-light btn-sm">
            Dashboard
        </a>

        <a href="../logout.php"
           class="btn btn-light btn-sm">
            Logout
        </a>
    </div>
</nav>

<main class="container-fluid px-4 py-4">

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">

        <div>
            <h2 class="page-title">Manage Bookings</h2>

            <p class="text-secondary mb-0">
                View reservations and manage guest check-in
                and check-out.
            </p>
        </div>

        <a href="staff_dashboard.php"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card booking-card mb-4">
        <div class="card-body p-4">

            <form method="GET" class="row g-3">

                <div class="col-md-9">
                    <label class="form-label">
                        Search Booking
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Guest name, email, booking ID or hotel"
                        value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit"
                            class="btn btn-navy flex-grow-1">
                        <i class="bi bi-search"></i>
                        Search
                    </button>

                    <a href="staff_booking.php"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="card booking-card">
        <div class="card-body p-0">

            <div class="d-flex justify-content-between
                        align-items-center p-4">

                <h5 class="fw-bold mb-0">
                    Booking Records
                </h5>

                <span class="badge text-bg-secondary">
                    <?= count($bookings) ?> bookings
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">

                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest</th>
                            <th>Hotel</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($bookings) > 0): ?>

                        <?php foreach ($bookings as $booking): ?>

                            <?php
                            $status = strtolower(
                                trim($booking->booking_status ?? '')
                            );

                            $statusClass = in_array(
                                $status,
                                [
                                    'pending',
                                    'confirmed',
                                    'checked_in',
                                    'checked_out',
                                    'cancelled'
                                ],
                                true
                            ) ? 'status-' . $status : '';

                            $canCheckIn = in_array(
                                $status,
                                ['pending', 'confirmed'],
                                true
                            );

                            $canCheckOut = $status === 'checked_in';
                            ?>

                            <tr>
                                <td>
                                    #<?= (int) $booking->booking_id ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $booking->fullname ?? 'N/A'
                                        ) ?>
                                    </strong>
                                    <br>

                                    <small class="text-secondary">
                                        <?= htmlspecialchars(
                                            $booking->email ?? ''
                                        ) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking->hotel_name ?? 'N/A'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking->room_type ?? 'N/A'
                                    ) ?>
                                    <br>

                                    <small class="text-secondary">
                                        Room:
                                        <?= htmlspecialchars(
                                            $booking->room_number ?? 'N/A'
                                        ) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking->check_in_date ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking->check_out_date ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    RM <?= number_format(
                                        (float) $booking->total_price,
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <span class="status <?= $statusClass ?>">
                                        <?= htmlspecialchars(
                                            ucwords(str_replace(
                                                '_',
                                                ' ',
                                                $status
                                            ))
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($canCheckIn): ?>

                                        <form method="POST"
                                              class="mb-2"
                                              onsubmit="return confirm('Confirm guest check-in?');">

                                            <input type="hidden"
                                                   name="booking_id"
                                                   value="<?= (int) $booking->booking_id ?>">

                                            <button type="submit"
                                                    name="action"
                                                    value="check_in"
                                                    class="btn btn-success btn-sm w-100">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                Check-in
                                            </button>
                                        </form>

                                    <?php elseif ($canCheckOut): ?>

                                        <form method="POST"
                                              onsubmit="return confirm('Confirm guest check-out?');">

                                            <input type="hidden"
                                                   name="booking_id"
                                                   value="<?= (int) $booking->booking_id ?>">

                                            <button type="submit"
                                                    name="action"
                                                    value="check_out"
                                                    class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-box-arrow-right"></i>
                                                Check-out
                                            </button>
                                        </form>

                                    <?php else: ?>

                                        <span class="text-secondary">
                                            No action
                                        </span>

                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9"
                                class="text-center py-5 text-secondary">
                                <i class="bi bi-calendar-x fs-2"></i>

                                <p class="mt-2 mb-0">
                                    No bookings found.
                                </p>
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</main>

</body>
</html>

