<?php
session_start();

require_once __DIR__ . '/config/database.php';

$sql = "SELECT * FROM hotels_page";

$statement = $pdo ->query($sql);

$hotels = $statement ->fetchAll(PDO::FETCH_OBJ);

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>

<script>
    function filterHotels(){
        const location = document.getElementById("location").value;
        const price = document.getElementById("price").value;
        const rating = document.getElementById("rating").value;

        const hotels = document.querySelectorAll(".hotels-card");

        hotels.forEach(function(hotel){
            const hotelLocation = hotel.dataset.location.toLowerCase();
            const hotelPrice = parseFloat(hotel.dataset.price);
            const hotelRating = parseFloat(hotel.dataset.rating);

            let locationMatch = true;
            let priceMatch = true;
            let ratingMatch = true;

            if(location !== ""){
                locationMatch = hotelLocation.includes(location.replace("-"," "));
            }

            if(price == "under-100"){
                priceMatch = hotelPrice < 100;
            }
            else if(price == "100-200"){
                priceMatch = hotelPrice >= 100 && hotelPrice <=200;
            }
            else if(price == "201-350"){
                priceMatch = hotelPrice >= 201 && hotelPrice <= 350;
            }
            else if(price == "351-500"){
                priceMatch = hotelPrice >= 351 && hotelPrice <= 500;
            }
            else if(price == "above-500"){
                priceMatch = hotelPrice > 500;
            }

            if(rating !== ""){
                ratingMatch = hotelRating >= parseFloat(rating);
            }

            if(locationMatch && priceMatch && ratingMatch){
                hotel.style.display = "flex";
            }else{
                hotel.style.display = "none";
            }
        });
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
    <section id="hotel-section">
        <h1>Find Your Hotel</h1>
        <p>Explore our hotels and find a stay that suits you.</p>
    </section>
    <section id="filter-section">
        <div class="location-option">
            <label for="location">Location</label>
            <select name="location" id="location" onchange="filterHotels()">
                <option value="">All Malaysia</option>
                <option value="johor">Johor</option>
                <option value="melaka">Melaka</option>
                <option value="pahang">Pahang</option>
                <option value="penang">Penang</option>
                <option value="sabah">Sabah</option>
                <option value="kuala-lumpur">Kuala Lumpur</option>
            </select>
        </div>
        <div class="price-range">
            <label for="price">Price Range</label>
            <select name="price" id="price" onchange="filterHotels()">
                <option value="">Any Price</option>
                <option value="under-100">Under RM100</option>
                <option value="100-200">RM100-RM200</option>
                <option value="201-350">RM201-RM350</option>
                <option value="351-500">RM351-RM500</option>
                <option value="above-500">Above RM500</option>
            </select>
        </div>
        <div class="rating-section">
            <label for="rating">Rating</label>
            <select name="rating" id="rating" onchange="filterHotels()">
                <option value="">Any Rating</option>
                <option value="5">★★★★★ 5.0</option>
                <option value="4">★★★★☆ 4.0 & Above</option>
                <option value="3">★★★☆☆ 3.0 & Above</option>
            </select>
        </div>
    </section>
    <section id="hotels">
        <div class="hotels-container">
            <?php foreach($hotels as $hotel): ?>
            <div class="hotels-card"
                data-location="<?php echo $hotel -> location; ?>"
                data-price="<?php echo $hotel -> price; ?>"
                data-rating="<?php echo $hotel -> rating; ?>">
                <div class="hotels-image">
                    <img src="<?php echo htmlspecialchars ($hotel -> image_url); ?>" alt="<?php echo htmlspecialchars ($hotel -> hotel_name); ?>">
                </div>
                <div class="hotels-detail">
                <div class="hotels-name">
                    <h4><?php echo $hotel -> hotel_name; ?></h4>
                    <p><span>RM<?php echo $hotel -> price; ?></span>/night</p>
                </div>
                <div class="location-rating">
                    <p style="color: #6B7280;"><i class="bi bi-geo-fill me-2" style="color: #3B82F6;"></i><?php echo $hotel -> location; ?></p>
                    <p style="color: #374151;"><i class="bi bi-star-fill me-2" style="color: #F59E0B;"></i><?php echo $hotel -> rating; ?></p>
                </div>
                <div class="view-button">
                <a href="hotel-detail.php?id=<?php echo $hotel ->hotel_id; ?>" class="view">View Hotel</a>
                </div> 
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