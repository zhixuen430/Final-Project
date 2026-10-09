<?php

session_start();

require_once __DIR__ . '/config/database.php';

// check whether user login

if(!isset($_SESSION['user']['id'])){
    header('Location:login.php');
    exit;
}

$userId = $_SESSION['user']['id'];

// get booking belong to the current user

$sql = "SELECT b.booking_id,b.check_in_date,b.check_out_date,b.total_price,b.booking_status,h.hotel_name,h.location,h.image_url,r.room_type,r.price_per_night
FROM bookings b JOIN hotels_page h ON b.hotel_id = h.hotel_id JOIN rooms r ON b.room_id = r.room_id WHERE b.user_id = ? ORDER BY b.booking_id DESC";

$stmt = $pdo -> prepare($sql);
$stmt -> execute([$userId]);
$bookings = $stmt -> fetchAll(PDO::FETCH_OBJ);

if(!is_array($bookings)){
    $bookings = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        #mybooking-page{
            background-color: #f5f7fa;
        }
        .mybooking-section{
            padding: 20px 40px;
        }
        .mybooking-section h3{
            font-size: 2.5rem;
            color: #0F2747;
        }
        .mybooking-section p{
            font-size: 1.5rem;
            color: #6b7280;
        }
        .mybooking-container{
            display: flex;
            flex-direction: column;
            align-items: center;
            row-gap: 20px;
            padding-bottom: 20px;
        }
        .mybooking-card h3{
            color: #0F2747;
            font-size: 2rem;
        }
        .mybooking-card{
            background-color: #ffffff;
            width: 90%;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .mybooking-name{
            display: flex;
            justify-content: space-between;
        }
        .mybooking-name h5{
            color: #0F2747;
            font-size: 2rem;
        }
        .mybooking-name p{
            font-weight: bold;
            border-radius: 50px;
            width: fit-content;
            padding: 7px 10px;
            font-size: 1.2rem;
            text-align: center;
            display: inline-block;
            align-items: center;
            background-color: #fff0c2;
            color: #805b00;
        }
        .mybooking-detail{
            display: flex;
            gap: 40px;
        }
        .mybooking-image{
            width: 500px;
            border-radius: 16px;
        }
        .mybooking-image img{
            width: 100%;
            border-radius: 16px;
        }
        .mybooking-info{
            line-height: 1.6;
        }
        .booking-detail{
            color: #6b7280;
            font-size: 1.4rem;
        }
        .booking-room-type{
            color: #0F2747;
            font-size: 1.4rem;
            font-weight: bold;
        }
        .booking-detail-date{
            font-size: 1.2rem;
            color: #6b7280;
        }
        .detail-date{
            font-size: 1.4rem;
            color: #0F2747;
        }
        .mybooking-price{
            display: flex;
            justify-content: space-between;
        }
        .booking-price{
            color: #6b7280;
            font-size: 1.3rem;
        }
        .booking-totalprice{
            color: #0F2747;
            font-size: 1.7rem;
            font-weight: bold;
        }
        .view-btn{
            color: #ffffff;
            background-color:  #0F2747;
            padding: 15px;
            font-weight: bold;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            width: 100%;
            border: 0px solid;
            font-size: 1.3rem;
        }
        .view-btn:hover{
            background-color: #c9a227;
            color: #0f2747;
        }
        .explore{
            font-size: 1.5rem;
            color: #6b7280;
        }
        .browse{
            border: 1px solid #0F2747;
            color: #0F2747;
            border-radius: 12px;
            width: 100%;
            padding: 20px;
            text-decoration: none;
            display: flex;
            justify-content: center;
            font-size: 1.6rem;
            font-weight: bold;
        }
        .browse:hover{
            background-color: #f5f7fa;
            text-decoration: underline;
        }
        .cancel-btn{
            color: #ffffff;
            background-color:  #dc3545;
            padding: 15px;
            font-weight: bold;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            width: 100%;
            border: 0px solid #dc3545;
            font-size: 1.3rem;
            margin-top: 10px;
        }
        .cancel-btn:hover{
            cursor: pointer;
            background-color: #b32130;
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
            </div>
        </nav>
        <nav class="third">
            <h1>Find Your Perfect Stay</h1>
            <p>Search,compare,and book your ideal stay with ease.</p>
        </nav>
    </header>
    <section id="mybooking-page">
        <div class="mybooking-section">
            <h3>My Booking</h3>
            <p>Manage and view your hotel reservations.</p>
        </div>
        <div class="mybooking-container">

        <?php if(count($bookings) > 0): ?>
            <?php foreach($bookings as $booking): ?>
                <?php
                $checkIn = new DateTime($booking -> check_in_date);
                $checkOut = new DateTime($booking -> check_out_date);

                $nights = $checkIn -> diff($checkOut)-> days;

                $status = ucfirst(strtolower($booking -> booking_status));

                $statusClass = 'bg-warning-subtle text-warning-emphasis';

                if($status === 'Confirmed'){
                    $statusClass = 'bg-success-subtle text-success-emphasis';
                }elseif($status === 'Cancelled'){
                    $statusClass = 'bg-danger-subtle text-danger-emphasis';
                }
        ?>

            <div class="mybooking-card">
                <div class="mybooking-name">
                    <h5><?php echo $booking -> hotel_name; ?></h5>
                    <p class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></p>
                </div>
                <div class="mybooking-detail">
                    <div class="mybooking-image">
                        <img src="<?php echo $booking -> image_url; ?>" alt="<?php echo $booking -> hotel_name; ?>">
                    </div>
                    <div class="mybooking-info">
                        <p class="booking-detail"><i class="bi bi-geo-alt me-2"></i><?php echo $booking -> location; ?></p>
                        <p class="booking-room-type"><?php echo $booking -> room_type; ?></p>
                        <p class="booking-detail-date"><i class="bi bi-calendar-check me-2"></i>Check In</p>
                        <p class="detail-date"><?php echo $checkIn -> format('d M Y') ?></p>
                        <p class="booking-detail-date"><i class="bi bi-calendar-check me-2"></i>Check Out</p>
                        <p class="detail-date"><?php echo $checkOut -> format('d M Y') ?></p>
                    </div>
                </div>
                <hr>
                <div class="mybooking-price">
                    <div class="price-detail">
                        <p class="booking-price">
                            Total Price . <?php echo $nights ?>
                            <?php echo $nights === 1 ? 'night' : 'nights' ?>
                    </p>
                        <p class="booking-totalprice">RM <?php echo number_format((float)$booking -> total_price , 2); ?></p>
                    </div>
                    <div class="mybooking-viewbtn">
                        <a href="booking-detail.php?booking_id=<?= (int)$booking -> booking_id ?>" class="view-btn text-decoration-none">View Details</a>
                            <?php if(strtolower($booking -> booking_status) === 'pending'): ?>
                            <form action="cancel-booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking ?');">
                                <input type="hidden" name="booking_id" value="<?php echo (int)$booking -> booking_id ?>">
                                <button type="submit" class="cancel-btn">Cancel Booking</button>
                            </form>
                            <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="mybooking-card text-center">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #0F2747;"></i>
                    <h3>No Booking Yet</h3>
                    <p class="explore">You haven't made any reservations yet.</p>
                    <a href="hotel.php" class="browse">Browse Hotels</a>
                </div>
            <?php endif; ?>
            <?php if(!empty($bookings)): ?>
            <div class="mybooking-card">
                <h3>Need Another Stay ?</h3>
                <p class="explore">Explore our hotels and find your next destination.</p>
                <a href="hotel.php" class="browse">Browse Hotels</a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <footer>
        <h4 style="color: #ffffff;">HOTEL BOOKING</h4>
        <h5 style="color: #d1d5d8;">Find,compare,and book your perfect stay with ease.</h5><br>
        <div class="footer-container">
            <div class="quick-link">
            <h4 style="color: #c9a227;">Quick Links</h4>
            <a href="home.php">Home</a>
            <a href="hotel.php">Hotels</a>
            <a href="room.php">Rooms</a>
            <a href="mybooking.php">My Bookings</a>
        </div>
        <div class="contact-us-footer">
            <h4 style="color: #c9a227;">Contact Us</h4>
            <p><i class="bi bi-facebook me-2"></i> Facebook</p>
            <p><i class="bi-instagram me-2"></i> Instagram</p>
            <p><i class="bi bi-twitter-x me-2"></i> Twitter</p>
        </div>
        </div>
        <br>
        <h5 style="color: #9CA3AF;"> © 2026 Hotel Booking System. All Rights Reserved.</h5>
    </footer>
</body>
</html>