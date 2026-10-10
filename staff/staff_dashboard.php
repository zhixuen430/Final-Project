<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only logged-in staff can access this page
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

$staffName = $_SESSION['user']['fullname']
    ?? $_SESSION['user']['name']
    ?? 'Staff';

// Booking statistics
$totalBookings = 0;
$pendingBookings = 0;
$checkedInBookings = 0;
$checkedOutBookings = 0;

try {
    $totalBookings = (int) $pdo
        ->query("SELECT COUNT(*) FROM bookings")
        ->fetchColumn();

    $pendingBookings = (int) $pdo
        ->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")
        ->fetchColumn();

    $checkedInBookings = (int) $pdo
        ->query("SELECT COUNT(*) FROM bookings WHERE status = 'checked_in'")
        ->fetchColumn();

    $checkedOutBookings = (int) $pdo
        ->query("SELECT COUNT(*) FROM bookings WHERE status = 'checked_out'")
        ->fetchColumn();

} catch (PDOException $e) {
    $databaseError = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Staff Dashboard | Hotel Booking</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<nav class="navbar navbar-dark px-4" style="background-color: #0f2747;">
    <a class="navbar-brand fw-bold" href="../home.php">
        HOTEL BOOKING
    </a>

    <div class="d-flex align-items-center gap-3">
        <span class="text-white">
            Welcome, <?= htmlspecialchars($staffName) ?>
        </span>

        <a href="../logout.php" class="btn btn-outline-light btn-sm">
            Logout
        </a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100 p-3">
            <h6 class="text-secondary mb-3">STAFF MENU</h6>

            <a href="staff_dashboard.php"
               class="btn w-100 text-start mb-2"
               style="background-color:#0f2747;color:white;">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="staff_booking.php"
               class="btn btn-light w-100 text-start mb-2">
                <i class="bi bi-calendar-check"></i>
                Manage Bookings
            </a>

            <a href="../home.php"
               class="btn btn-light w-100 text-start">
                <i class="bi bi-house"></i>
                Back to Home
            </a>
        </aside>

        <!-- Main content -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2 class="fw-bold" style="color:#0f2747;">
                Staff Dashboard
            </h2>

            <p class="text-secondary">
                Manage hotel bookings, check-ins and check-outs.
            </p>

            <?php if (!empty($databaseError)): ?>
                <div class="alert alert-warning">
                    Unable to load booking statistics.
                    Please check your bookings table and status values.
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="row g-3 mt-2">

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-2">
                                Total Bookings
                            </p>
                            <h2 class="fw-bold">
                                <?= $totalBookings ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-2">
                                Pending Bookings
                            </p>
                            <h2 class="fw-bold text-warning">
                                <?= $pendingBookings ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-2">
                                Checked In
                            </p>
                            <h2 class="fw-bold text-success">
                                <?= $checkedInBookings ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-2">
                                Checked Out
                            </p>
                            <h2 class="fw-bold text-primary">
                                <?= $checkedOutBookings ?>
                            </h2>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Quick actions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">
                        Quick Actions
                    </h5>

                    <p class="text-secondary">
                        Manage guest reservations and update their
                        check-in or check-out status.
                    </p>

                    <a href="staff_booking.php"
                       class="btn text-white"
                       style="background-color:#0f2747;">
                        Manage Bookings
                    </a>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>

