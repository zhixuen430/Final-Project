
<?php
session_start();

require_once __DIR__ . '/config/database.php';

// STEP 1: Check whether the user is logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// STEP 2: Get the current user's ID from the session
$userId = $_SESSION['user']['id'];

// STEP 3: Get the user's information from the database
$sql = "SELECT fullname, email, role
        FROM users
        WHERE user_id = :user_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// STEP 4: If the account no longer exists, log out
if (!$user) {
    session_unset();
    session_destroy();

    header('Location: login.php');
    exit;
}

// Safely display text from the database
function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | Hotel Booking</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 650px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .profile-card {
            background-color: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .profile-icon {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background-color: #e8eef6;
            color: #0f2747;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }

        .profile-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .profile-subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .profile-info {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .profile-label {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .profile-value {
            font-size: 16px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .back-btn {
            background-color: #0f2747;
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #1d416e;
            color: white;
        }

        @media (max-width: 576px) {
            .profile-card {
                padding: 25px 20px;
            }
        }
        
    </style>
</head>

<body>
<header>
        <nav class="fixed-header">
            <div class="header-container">
                <div class="title">
                    <h1 class="home-title">Hotel Booking</h1>
                </div>
                <div class="logsign">
                    <a href="register.php">Register</a>
                    <a href="login.php">Log In</a>
                    <a href="logout.php">Log Out</a>
                </div>
            </div>
        </nav>
        <nav class="second">
            <div class="navigation-link">
                <a href="home.php"><i class="bi bi-house-door me-2"></i>Home</a>
                <a href="hotel.php"><i class="bi bi-building me-2"></i>Hotels</a>
                <a href="room.php"><i class="bi bi-door-open me-2"></i>Rooms</a>
                <a href="mybooking.php"><i class="bi bi-calendar-check me-2"></i>My Bookings</a>
                <a href="contact.php"><i class="bi bi-telephone me-2"></i>Contact Us</a>
                <a href="profile.php"><i class="bi bi-person-circle me-2"></i>My Profile</a>
            </div>
        </nav>
        <nav class="third">
            <h1>Find Your Perfect Stay</h1>
            <p>Search,compare,and book your ideal stay with ease.</p>
        </nav>
    </header>

<div class="profile-container">
    <div class="profile-card">

        <div class="profile-icon">
            <i class="bi bi-person"></i>
        </div>

        <h2 class="profile-title">My Profile</h2>

        <p class="profile-subtitle">
            View your personal information
        </p>

        <div class="profile-info">
            <span class="profile-label">Full Name</span>
            <div class="profile-value">
                <?= escape($user['fullname']) ?>
            </div>
        </div>

        <div class="profile-info">
            <span class="profile-label">Email Address</span>
            <div class="profile-value">
                <?= escape($user['email']) ?>
            </div>
        </div>

        <div class="profile-info">
            <span class="profile-label">Role</span>
            <div class="profile-value">
                <?= escape(ucfirst($user['role'])) ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="home.php" class="back-btn">
                <i class="bi bi-arrow-left"></i>
                Back to Home
            </a>
        </div>

    </div>
</div>

</body>
</html>