<?php
session_start();

require_once __DIR__ . '/config/database.php';

$statement = $pdo ->query("SELECT * FROM hotels_page LIMIT 3");

$hotels = $statement ->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>

<script>
const checkin = document.getElementById("checkin");
const checkout = document.getElementById("checkout");

const today = new Date().toISOString().split("T")[0];

checkin.min = today;
checkout.min = today;

checkin.addEventListener("change", function () {
    checkout.min = checkin.value;

    if (checkout.value && checkout.value <= checkin.value) {
        checkout.value = "";
    }
});

function searchHotels() {
    const location = document.getElementById("location").value.trim();
    const checkinDate = checkin.value;
    const checkoutDate = checkout.value;

    if (location === "") {
        alert("Please enter a location.");
        return;
    }

    if (checkinDate === "") {
        alert("Please select check-in date.");
        return;
    }

    if (checkoutDate === "") {
        alert("Please select check-out date.");
        return;
    }

    if (checkoutDate <= checkinDate) {
        alert("Check-out date must be after check-in date.");
        return;
    }

    window.location.href =
        "hotel.php?location=" + encodeURIComponent(location) +
        "&checkin=" + encodeURIComponent(checkinDate) +
        "&checkout=" + encodeURIComponent(checkoutDate);
}
</script>



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
    <section id="search-bar">
        <form action="room.php" method="GET" class="search-item">
            <div class="form-group">
                <label for="location">Where</label><br>
                <input type="text" name="location" id="location" placeholder="Hotel or Location">
            </div>
            <div class="form-group">
                <label for="checkin">Check-In</label><br>
                <input type="date" name="checkin" id="checkin">
            </div>
            <div class="form-group">
                <label for="checkout">Check-Out</label><br>
                <input type="date" name="checkout" id="checkout">
            </div>
            <button class="search" onclick="searchHotels()"><i class="bi bi-search me-2"></i>Search</button>
        </form>
    </section>
    <section id="reason">
        <div class="why">
            <h1>Why Choose Us ?</h1>
            <p>We make it easy to find and book your perfect stay.</p>
        </div>
        <div class="why-container">
            <div class="why-card">
                <i class="bi bi-search fs-3 mb-2"></i>
                <h2>Easy Search</h2>
                <p>Find your ideal room with ease.</p>
            </div>
            <div class="why-card">
                <i class="bi bi-buildings fs-3 mb-2"></i>
                <h2>Wide Choice</h2>
                <p>Explore different hotels.</p>
            </div>
            <div class="why-card">
                <i class="bi bi-grid-3x3-gap fs-3 mb-2"></i>
                <h2>Easy Booking</h2>
                <p>Book your room easily and quickly.</p>
            </div>
        </div>
    </section>
    <section id="popular-hotel">
        <div class="popular">
            <h1>Popular Hotels</h1>
            <p>Discover some of our featured stays</p>
        </div>
        <div class="popular-container">
            <?php foreach($hotels as $hotel): ?>
            <div class="popular-card">
                <div class="popular-photo">
                    <img src="<?php echo htmlspecialchars ($hotel ->image_url); ?>" alt="<?php echo htmlspecialchars ($hotel ->hotel_name); ?>">
                </div>
                <div class="popular-detail">
                    <h4><?php echo $hotel ->hotel_name; ?></h4>
                    <p style="color: #6B7280;"><i class="bi bi-geo-fill me-2"></i><?php echo $hotel ->location; ?></p>
                    <p style="color: #6b7280"><i class="bi bi-card-text me-2" style="color: #c9a227;"></i><?php echo $hotel ->description; ?></p>
                    <a href="hotel-detail.php?id=<?php echo $hotel ->hotel_id; ?>" class="view-hotel">View Hotel</a>
                </div>
            </div>
            <?php endforeach; ?>
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