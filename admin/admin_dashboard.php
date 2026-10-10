
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Check whether the user is logged in
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

// Get admin name
$adminName = $_SESSION['user']['fullname']
    ?? $_SESSION['user']['name']
    ?? 'Admin';

// Get dashboard statistics
$totalHotels = 0;
$totalRooms = 0;
$totalBookings = 0;
$totalUsers = 0;

try {
    $totalHotels = (int) $pdo
        ->query("SELECT COUNT(*) FROM hotels_page")
        ->fetchColumn();

    $totalRooms = (int) $pdo
        ->query("SELECT COUNT(*) FROM rooms")
        ->fetchColumn();

    $totalBookings = (int) $pdo
        ->query("SELECT COUNT(*) FROM bookings")
        ->fetchColumn();

    $totalUsers = (int) $pdo
        ->query("SELECT COUNT(*) FROM users")
        ->fetchColumn();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $errorMessage = 'Unable to load dashboard statistics.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Hotel Booking</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="brand">
        <i class="bi bi-building"></i>
        <span>HOTEL BOOKING</span>
    </div>

    <div class="admin-profile">
        <div class="profile-icon">
            <i class="bi bi-person-fill"></i>
        </div>

        <div>
            <h6><?= htmlspecialchars($adminName) ?></h6>
            <small>Administrator</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="admin_dashboard.php" class="nav-link active">
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>

        <a href="admin_hotel.php" class="nav-link">
            <i class="bi bi-buildings"></i>
            Manage Hotels
        </a>

        <a href="admin_room.php" class="nav-link">
            <i class="bi bi-door-open"></i>
            Manage Rooms
        </a>

        <a href="admin_user.php" class="nav-link">
            <i class="bi bi-person"></i>
            Manage Users
        </a>

        <a href="admin_statistics.php" class="nav-link">
            <i class="bi bi-bar-chart-line"></i>
            Statistics & Reports
        </a>

        <a href="admin_reports.php" class="nav-link">
            <i class="bi bi-file-earmark-bar-graph"></i>
            Booking Reports
        </a>

        <a href="../home.php" class="nav-link">
            <i class="bi bi-house"></i>
            View Website
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="../logout.php" class="nav-link logout-link">
            <i class="bi bi-box-arrow-left"></i>
            Logout
        </a>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content">

    <header class="topbar">
        <div>
            <h4>Admin Dashboard</h4>
            <p>Manage your hotel booking system.</p>
        </div>

        <div class="date-label">
            <i class="bi bi-shield-check"></i>
            Admin Panel
        </div>
    </header>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-warning">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div>
            <h2>Welcome back, <?= htmlspecialchars($adminName) ?>!</h2>
            <p>Here is an overview of your hotel booking system.</p>
        </div>

        <i class="bi bi-buildings welcome-icon"></i>
    </section>

    <!-- Statistics -->
    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon hotel-icon">
                <i class="bi bi-buildings"></i>
            </div>

            <div>
                <p>Total Hotels</p>
                <h3><?= $totalHotels ?></h3>
                <small>Hotels in the system</small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon room-icon">
                <i class="bi bi-door-open"></i>
            </div>

            <div>
                <p>Total Rooms</p>
                <h3><?= $totalRooms ?></h3>
                <small>Rooms in the system</small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon booking-icon">
                <i class="bi bi-calendar-check"></i>
            </div>

            <div>
                <p>Total Bookings</p>
                <h3><?= $totalBookings ?></h3>
                <small>All booking records</small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon user-icon">
                <i class="bi bi-people"></i>
            </div>

            <div>
                <p>Total Users</p>
                <h3><?= $totalUsers ?></h3>
                <small>Registered accounts</small>
            </div>
        </div>

    </section>

    <!-- Management Section -->
    <section class="management-section">

        <div class="section-heading">
            <div>
                <h3>Management</h3>
                <p>Manage the information in your system.</p>
            </div>
        </div>

        <div class="management-grid">

            <a href="admin_hotel.php" class="management-card">
                <div class="management-icon">
                    <i class="bi bi-buildings"></i>
                </div>

                <h5>Manage Hotels</h5>

                <p>
                    Add new hotels, update hotel information,
                    and delete hotel records.
                </p>

                <span>
                    Manage Hotels
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>

            <a href="admin_room.php" class="management-card">
                <div class="management-icon">
                    <i class="bi bi-door-open"></i>
                </div>

                <h5>Manage Rooms</h5>

                <p>
                    Manage room types, prices, room numbers,
                    and availability.
                </p>

                <span>
                    Manage Rooms
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>

            <a href="admin_user.php" class="management-card">
                <div class="management-icon">
                    <i class="bi bi-person"></i>
                </div>

                <h5>Manage Users</h5>

                <p>
                    Add new users, update user information, delete user accounts, and manage user roles.
                </p>

                <span>
                    Manage Users
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>

            <a href="admin_statistics.php" class="management-card">
                <div class="management-icon">
                    <i class="bi bi-bar-chart-line"></i>
                </div>

                <h5>Statistics & Reports</h5>

                <p>
                    View booking statistics, monitor room availability, track hotel revenue, and generate reports on users and bookings.
                </p>

                <span>
                    Statistics & Reports
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>

            <a href="admin_reports.php" class="management-card">
                <div class="management-icon">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                </div>

                <h5>Booking Reports</h5>

                <p>
                    View booking records, filter bookings by date and status, and generate booking and revenue reports.
                </p>

                <span>
                    Booking Reports
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>

        </div>
    </section>

    <footer class="footer">
        Hotel Booking System &copy; <?= date('Y') ?>
    </footer>

</main>

</body>
</html>