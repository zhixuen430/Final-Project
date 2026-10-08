<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Check admin role
if ($_SESSION['user']['role'] !== 'admin') {
    header("Location:../home.php");
    exit;
}

// Count hotels
$hotelCount = $pdo->query("SELECT COUNT(*) FROM hotels_page")->fetchColumn();

// Count rooms
$roomCount = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();

// Count users
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Count bookings
$bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>
</head>

<body>

    <h1>Admin Dashboard</h1>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?>
    </p>

    <nav>
        <a href="admin.php">Dashboard</a>
        <a href="admin_hotels.php">Manage Hotels</a>
        <a href="admin_rooms.php">Manage Rooms</a>
        <a href="admin_bookings.php">Manage Bookings</a>
        <a href="admin_users.php">Manage Users</a>
        <a href="logout.php">Log Out</a>
    </nav>

    <hr>

    <h2>Overview</h2>

    <div>
        <h3>Total Hotels</h3>
        <p><?php echo $hotelCount; ?></p>
    </div>

    <div>
        <h3>Total Rooms</h3>
        <p><?php echo $roomCount; ?></p>
    </div>

    <div>
        <h3>Total Users</h3>
        <p><?php echo $userCount; ?></p>
    </div>

    <div>
        <h3>Total Bookings</h3>
        <p><?php echo $bookingCount; ?></p>
    </div>

</body>

</html>