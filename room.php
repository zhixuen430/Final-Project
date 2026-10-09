<?php
session_start();

require_once __DIR__ . '/config/database.php';

$sql = "SELECT rooms.*,hotels_page.hotel_name FROM rooms JOIN hotels_page ON rooms.hotel_id = hotels_page.hotel_id";

$statement = $pdo ->query($sql);

$rooms = $statement ->fetchAll(PDO::FETCH_OBJ);

?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>

<script>
    function filterRooms(){
        const hotel = document.getElementById("hotel").value;
        const roomtype = document.getElementById("room").value;
        const price = document.getElementById("price").value;
        const status = document.getElementById("status").value;

        const rooms = document.querySelectorAll(".room-card");

        rooms.forEach(function(room){
            const roomHotel = room.dataset.hotel.toLowerCase();
            const roomType = room.dataset.room.toLowerCase();
            const roomPrice = parseFloat(room.dataset.price);
            const roomStatus = room.dataset.status.toLowerCase();
        

        let hotelMatch = true;
        let roomMatch = true;
        let priceMatch = true;
        let statusMatch = true;

            if(hotel !== "all"){
                hotelMatch = roomHotel.includes(hotel.replace("-"," "));
            }
            if(roomtype !=="all"){
                roomMatch = roomType.includes(roomtype.replace("-"," "));
            }
            if(price == "under-100"){
                priceMatch = roomPrice < 100;
            }
            else if(price == "100-200"){
                priceMatch = roomPrice >= 100 && roomPrice <=200;
            }
            else if(price == "201-350"){
                priceMatch = roomPrice >= 201 && roomPrice <= 350;
            }
            else if(price == "351-500"){
                priceMatch = roomPrice >= 351 && roomPrice <= 500;
            }
            else if(price == "above-500"){
                priceMatch = roomPrice > 500;
            }
            if(status !== "all"){
                statusMatch = roomStatus.includes(status.replace("-"," "));
            }
            if(hotelMatch && roomMatch && priceMatch && statusMatch){
                room.style.display = "";
            }else{
                room.style.display = "none";
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
    <section id="room-page">
        <div class="room-page-title">
        <h1>Find Your Room</h1>
        <p>Choose a room that suits your stay.</p>
        </div>
    </section>
    <section id="room-filter">
        <div class="hotel-filter">
        <label for="hotel">Hotel</label>
        <select name="hotel" id="hotel" onchange="filterRooms()">
            <option value="all">All Hotels</option>
            <optgroup label="Johor">
                <option value="Amari Johor Bahru">Amari Johor Bahru</option>
                <option value="St. Giles Southkey Johor Bahru">St. Giles Southkey Johor Bahru</option>
                <option value="Renaissance Johor Bahru Hotel">Renaissance Johor Bahru Hotel</option>
                <option value="Holiday Villa Johor Bahru City Centre">Holiday Villa Johor Bahru City Centre</option>  
            </optgroup>
            <optgroup label="Penang">
                <option value="Eastern & Oriental Hotel">Eastern & Oriental Hotel</option>
                <option value="Shangri-La Rasa Sayang">Shangri-La Rasa Sayang</option>
                <option value="Royale Chulan Penang">Royale Chulan Penang</option>
                <option value="St.Giles Wembley Penang Hotel">St.Giles Wembley Penang Hotel</option>
            </optgroup>
            <optgroup label="Melaka">
                <option value="Hatten Hotel Melaka">Hatten Hotel Melaka</option>
                <option value="Swiss Garden Hotel Melaka">Swiss Garden Hotel Melaka</option>
                <option value="Holiday Inn Melaka">Holiday Inn Melaka</option>
            </optgroup>
            <optgroup label="Pahang">
                <option value="Hyatt Regency Kuantan Resort">Hyatt Regency Kuantan Resort</option>
                <option value="The Chateau Spa & Wellness Resort">The Chateau Spa & Wellness Resort</option>
                <option value="Mangala Estate Boutique Resort">Mangala Estate Boutique Resort</option>
                <option value="Colmar Tropicale Resort">Colmar Tropicale Resort</option>
            </optgroup>
            <optgroup label="Sabah">
                <option value="Hilton Kota Kinabalu">Hilton Kota Kinabalu</option>
                <option value="The Magellan Sutera Resort">The Magellan Sutera Resort</option>
                <option value="Shangri-La Tanjung Aru Resort">Shangri-La Tanjung Aru Resort</option>
            </optgroup>
            <optgroup label="Kuala Lumpur">
                <option value="Hilton Kuala Lumpur">Hilton Kuala Lumpur</option>
                <option value="The Ritz-Carlton, Kuala Lumpur">The Ritz-Carlton, Kuala Lumpur</option>
            </optgroup>
        </select>
        </div>
        <div class="room-filter">
            <label for="room">Room Type</label>
            <select name="room" id="room" onchange="filterRooms()">
                <option value="all">All Room Type</option>
                <option value="double">Double Room</option>
                <option value="twin">Twin Room</option>
                <option value="deluxe">Deluxe Room</option>
                <option value="suite">Suite Room</option>
                <option value="family">Family Room</option>
            </select>
        </div>
        <div class="price-filter">
            <label for="price">Price</label>
            <select name="price" id="price" onchange="filterRooms()">
                <option value="all">All Price</option>
                <option value="under-100">Under RM100</option>
                <option value="100-200">RM100-RM200</option>
                <option value="201-350">RM201-RM350</option>
                <option value="351-500">RM351-RM500</option>
                <option value="above-500">Above RM500</option>
            </select>
        </div>
        <div class="status-filter">
            <label for="status">Status</label>
            <select name="status" id="status" onchange="filterRooms()">
                <option value="all">All Rooms</option>
                <option value="available">Available</option>
                <option value="booked">Booked</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
    </section>
    <section id="rooms">
        <div class="room-container">
            <?php foreach($rooms as $room): ?>
            <div class="room-card"
                data-hotel = "<?php echo $room -> hotel_name; ?>"
                data-room = "<?php echo $room -> room_type; ?>"
                data-price = "<?php echo $room -> price_per_night; ?>"
                data-status = "<?php echo $room -> status; ?>">
                <div class="room-image">
                    <img src="<?php echo htmlspecialchars($room -> image_url); ?>" alt="<?php echo $room -> room_type; ?>">
                </div>
                <div class="room-detail">
                    <h3><?php echo $room -> room_type; ?></h3>
                    <p class="detail-p"><i class="bi bi-building me-2"></i><?php echo $room -> hotel_name; ?></p>
                    <p class="detail-p"><i class="bi bi-door-open me-2"></i>Room <?php echo $room -> room_number; ?></p>
                    <p class="detail-p"><i class="bi bi-lamp me-2"></i><?php echo $room -> bed_type; ?></p>
                    <p class="detail-price"><span>RM <?php echo $room -> price_per_night; ?></span>/night</p>
                    <div class="book-button">
                        <p class="status <?php echo $room -> status; ?>">
                            <?php echo ucfirst($room -> status); ?>
                        </p>
                        <?php if($room -> status === 'available'): ?>
                        <a href="booking.php?room_id=<?php echo (int)$room -> room_id; ?>" class="booking">Book Now</a>
                        <?php else: ?>
                            <span class="booking disabled">Not Available</span>
                        <?php endif; ?>
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