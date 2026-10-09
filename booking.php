<?php

session_start();

require_once __DIR__ . '/config/database.php';

// check whether user have login

if(!isset($_SESSION['user']['id'])){
    header('Location:login.php');
    exit;
}

// get room id from url

$room_id = filter_input(INPUT_GET,'room_id',FILTER_VALIDATE_INT);

if(!$room_id){
    header('Location:room.php');
    exit;
}

// get room and hotel detail

$sql = "SELECT rooms.*,hotels_page.hotel_name,hotels_page.location FROM rooms JOIN hotels_page ON rooms.hotel_id = hotels_page.hotel_id WHERE rooms.room_id = ?";
$stmt = $pdo -> prepare($sql);
$stmt -> execute([$room_id]);
$room = $stmt -> fetch(PDO::FETCH_OBJ);

// check whether the room exists

if(!$room){
    header('Location:room.php');
    exit;
}

// check whether the room is available

$isAvailable = strtolower(trim($room -> status)) === 'available';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
    #booking-page{
        background-color: #f5f7fa;
        padding-bottom: 20px;
    }
    .booking-section{
        padding: 20px 70px;
    }
    .booking-section a{
        font-size: 1.5rem;
        color: #6b7280;
        text-decoration: none;
    }
    .booking-section a:hover{
        text-decoration: underline;
        color: black;
    }
    .booking-section h3{
        font-size: 2.5rem;
        color: #0F2747;
    }
    .booking-section p{
        font-size: 1.5rem;
        color: #6b7280;
    }
    .booking-container{
        display: flex;
        justify-content: space-around;
    }
    .booking-card{
        width: 600px;
        border-radius: 12px;
        border: 1px solid gray;
        background-color: #ffffff;
    }
    .booking-room-image{
        width: 600px;
        border-radius: 12px;
        padding: 20px;
    }
    .booking-room-image img{
        width: 100%;
        border-radius: 12px;
    }
    .booking-room-detail{
        padding: 20px;
    }
    .booking-room-detail h5{
        font-size: 1.5rem;
    }
    .booking-room-detail h6{
        font-size: 1.3rem;
    }
    .booking-detail-info{
        color: #0F2747;
        font-size: 1.3rem;
    }
    .booking-detail{
        color: #6b7280;
        font-size: 1.1rem;
    }
    .booking-price{
        display: flex;
        justify-content: space-between;
        line-height: 2.7;
    }
    .booking-price span{
        color: #0F2747;
        font-size: 1.5rem;
        font-weight: 500;
    }
    .confirm-info{
        width: 600px;
        border-radius: 12px;
        border: 1px solid gray;
        background-color: #ffffff;
        padding: 20px;
    }
    .confirm-info h4{
        color: #0F2747;
    }
    .confirm-date{
        display: flex;
        flex-direction: column;
    }
    .confirm-date input{
        width: 350px;
        padding: 5px;
        border-radius: 10px;
        border: 2px solid #d1d5d8;
    }
    .confirm-date label{
        font-size: 1.3rem;
        color: #0F2747;
        line-height: 3;
    }
    .confirm-price{
        font-size: 1.3rem;
        color: #0F2747;
    }
    .confirm-booking{
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
    .confirm-booking:hover{
        background-color: #c9a227;
        color: #0f2747;
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
    <section id="booking-page">
        <div class="booking-section">
        <a href="room.php">Back to Rooms</a>
        <h3>Complete Your Booking</h3>
        <p>Check your room details and select your dates.</p>
        </div>
        <div class="booking-container">
            <div class="booking-card">
                <div class="booking-room-image">
                    <img src="<?php echo $room -> image_url; ?>" alt="<?php echo $room -> room_type; ?>">
                </div>
                <div class="booking-room-detail">
                    <h5>Room Details</h5>
                    <p class="booking-detail">Hotel Name</p>
                    <h6><?php echo $room -> hotel_name; ?></h6>
                    <p class="booking-detail">Room Type</p>
                    <p class="booking-detail-info"><?php echo $room -> room_type; ?></p>
                    <p class="booking-detail">Bed Type</p>
                    <p class="booking-detail-info"><?php echo $room -> bed_type; ?></p>
                    <p class="booking-detail">Room Number</p>
                    <p class="booking-detail-info"><?php echo $room -> room_number; ?></p>
                    <hr>
                    <div class="booking-price">
                        <p class="booking-detail">Price per night</p>
                        <p><span>RM <?php echo $room -> price_per_night; ?></span></p>
                    </div>
                </div>
            </div>
            <div class="confirm-info">
                <h4>Your Stay</h4>
                    <?php if(!$isAvailable): ?>
                        <p class="text-danger">This room is currently unavailable.</p>
                    <?php else: ?>
                <form action="submit-booking.php" method="post">
                    <input type="hidden" name="room_id" value="<?php echo (int)$room -> room_id; ?>">
                    <div class="confirm-date">
                    <label for="check_in_date">Check-in Date</label>
                    <input type="date" name="check_in_date" id="check_in_date" required>
                    <label for="check_out_date">Check-out Date</label>
                    <input type="date" name="check_out_date" id="check_out_date" required>
                    </div>
                    <hr>
                    <h4>Price Summary</h4>
                    <div class="booking-price">
                        <p class="booking-detail">Price per night</p>
                        <p class="confirm-price">RM <?php echo $room -> price_per_night; ?></p>
                    </div>
                    <div class="booking-price">
                        <p class="booking-detail">Number of nights</p>
                        <p class="confirm-price" id="total-nights">0 nights</p>
                    </div>
                    <hr>
                    <div class="booking-price">
                        <h5>Total Price</h5>
                        <p><span id="total-price">RM 0.00</span></p>
                    </div>
                    <button type="submit" class="confirm-booking">Confirm Booking</button><br><br>
                    <p>Your booking will be pending confirmation</p>
                </form>
                <?php endif; ?>
            </div>
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

<script>
    const checkIn = document.getElementById('check_in_date');
    const checkOut = document.getElementById('check_out_date');
    const totalNights = document.getElementById('total-nights');
    const totalPrice = document.getElementById('total-price');

    const pricePerNight = <?php echo json_encode((float)$room -> price_per_night) ?>;

    // prevent selecting past date

    const today = new Date();
    const todayString =[
        today.getFullYear(),
        String(today.getMonth() + 1).padStart(2, '0'),
        String(today.getDate()).padStart(2, '0')
    ].join('-');

    checkIn.min = todayString;
    checkOut.min = todayString;

    function calculatePrice(){
        if(!checkIn.value || !checkOut.value){
            totalNights.textContent = '0 nights';
            totalPrice.textContent = 'RM0.00';
            return;
        }
        
        const start = new Date(checkIn.value + 'T00:00:00');
        const end = new Date(checkOut.value + 'T00:00:00');

        const nights = Math.round((end - start) / (1000 * 60 * 60 *24));

        if(nights <= 0){
            totalNights.textContent = 'Invalid Dates';
            totalPrice.textContent = 'RM0.00';
            return;
        }

        totalNights.textContent = nights + (nights === 1 ? ' night' : ' nights');
        totalPrice.textContent = 'RM ' + (pricePerNight * nights).toFixed(2);
    }

    checkIn.addEventListener('change',function(){
        checkOut.min = checkIn.value || todayString;

        if(checkOut.value && checkOut.value <= checkIn.value){
            checkOut.value = '';
        }
        calculatePrice();
    })

    checkOut.addEventListener('change',calculatePrice);
</script>

</body>
</html>