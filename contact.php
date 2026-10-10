<?php

session_start();

$success = " ";
$error = " ";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $firstName = trim($_POST['first-name']);
    $lastName = trim($_POST['last-name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if(
        empty($firstName) ||
        empty($lastName) ||
        empty($email) ||
        empty($phone) ||
        empty($message)
    ){
        $error = "Please fill in all fields.";
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error = "Please enter a valid email address.";
    }else{
        $success = "Your message has been sent successfully!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
    <section id="contact-page">
        <h1 class="contact-title">Let's Get In Touch.</h1>
        <div class="get-in-touch">
            <div class="getintouch-card">
                <h4>Address</h4>
                <p>123,Jalan Seaview,<br>
                10250 George Town, Penang<br>
                Malaysia.</p>
            </div>
            <div class="getintouch-card">
                <h4>Contact</h4>
                <p>+60 12-345 6789</p>
            </div>
            <div class="getintouch-card">
                <h4>Email</h4>
                <p>hotelbooking@gamil.com</p>
            </div>
        </div>
        <?php if(!empty($success)): ?>
            <p class="success-message">
                <?php echo $success; ?>
            </p>
            <?php endif; ?>
        <?php if(!empty($error)): ?>
            <p class="error-message">
                <?php echo $error; ?>
            </p>
            <?php endif; ?>
        <div class="contact-form">
            <form action="#" method="POST">
                <div class="first-line">
                <div class="first-line-card">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first-name" placeholder="Enter your first name...">
                </div>
                <div class="first-line-card">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" placeholder="Enter your last name...">
                </div>
                </div>
                <div class="second-line">
                <div class="second-line-card">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Enter your e-mail...">
                </div>
                <div class="second-line-card">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone...">
                </div>
                </div>
                <div class="third-line">
                <label for="message">Message</label>
                <textarea name="message" id="message" rows="5" placeholder="Write your message here..."></textarea>
                </div><br>
                <button type="submit" class="send">Send Message</button>
            </form>
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