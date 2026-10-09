<?php

session_start();

require_once __DIR__ . '/config/database.php';

$hotel_id = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$hotel_id){
    header('Location:hotel.php');
    exit;
}

// get hotel information

$stmt = $pdo -> prepare("SELECT * FROM hotels_page WHERE hotel_id = ?");
$stmt -> execute([$hotel_id]);
$hotel = $stmt -> fetch(PDO::FETCH_OBJ);



// check whether hotel exists

if(!$hotel){
    header('Location:hotel.php');
    exit;
}

// get room belonging to this hotel

$roomStmt = $pdo -> prepare("SELECT * FROM rooms WHERE hotel_id = ?");
$roomStmt -> execute([$hotel_id]);
$rooms = $roomStmt -> fetchAll(PDO::FETCH_OBJ);

// get review belonging to this hotel

$reviewSql = "SELECT reviews.*, users.fullname FROM reviews JOIN users ON reviews.user_id = users.user_id WHERE reviews.hotel_id = ? ORDER BY reviews.created_at DESC";
$reviewStmt = $pdo -> prepare($reviewSql);
$reviewStmt -> execute([$hotel_id]);
$reviews = $reviewStmt -> fetchAll(PDO::FETCH_OBJ);

$countSql = "SELECT COUNT(*)FROM reviews WHERE hotel_id = ?";
$countStmt = $pdo -> prepare($countSql);
$countStmt -> execute([$hotel_id]);
$totalReviews = $countStmt -> fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Detail</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>

<script>
    document.addEventListener("DOMContentLoaded",function(){
        const stars = document.querySelectorAll(".star-rating i");
        const ratingInput = document.getElementById("rating");
        const reviewForm = document.querySelector('form[action="submit-review.php"]');

    // click a star to select the rating
    if(ratingInput){
    stars.forEach(function(star){
        star.addEventListener("click",function(){
            const rating = Number(this.dataset.rating);
            ratingInput.value = rating;

            // update the appearance of all stars

            stars.forEach(function(item){
                const itemRating = Number(item.dataset.rating);

                if(itemRating <= rating){
                    item.classList.remove("bi-star");
                    item.classList.add("bi-star-fill","active");
                }else{
                    item.classList.remove("bi-star-fill","active");
                    item.classList.add("bi-star");
                }
            });
        });
    });
}
    // check rating before submitting the form

    if(reviewForm && ratingInput){
        reviewForm.addEventListener("submit",function(event){
            if(Number(ratingInput.value) < 1 || Number(ratingInput.value) > 5){
                event.preventDefault();
                alert("Please select a rating from 1 to 5 stars.")
            }
        });
    }
 });
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
    <section id="detail-page">
        <div class="detail-card">
            <div class="detail-image">
                <img src="<?php echo $hotel -> image_url; ?>" alt="<?php echo $hotel -> hotel_name; ?>">
            </div>
            <div class="detail-text">
                    <h2><?php echo $hotel -> hotel_name; ?></h2>
                    <p style="color: #6B7280;"><i class="bi bi-geo-fill me-2" style="color: #3B82F6;"></i><?php echo $hotel -> location; ?></p>
                    <p style="color: #374151;"><i class="bi bi-star-fill me-2" style="color: #F59E0B;"></i><?php echo $hotel -> rating; ?> / 5</p>
                    <hr>
                    <h3>About This Hotel</h3>
                    <p><?php echo $hotel -> description; ?></p>
                    <div class="price-link">
                    <p><span>RM<?php echo $hotel -> price; ?></span>/night</p>
                    <a href="room.php?id=<?php echo (int) $hotel ->hotel_id; ?>" class="view">View Rooms</a>
                    </div>
            </div>
        </div>
        <div class="detail-room">
            <h3 style="padding-left: 30px; padding-top: 30px; font-size: 2.1rem; color: ##0F2747;">Available Rooms</h3>
            <p style="padding-left: 30px; font-size: 1.3rem; color: #6B7280;">Choose your room that suite your needs.</p>
            <?php if(empty($rooms)): ?>
                <p>No rooms are available for this hotel yet.</p>
            <?php else: ?>
                <div class="detail-room-list">
                    <?php foreach($rooms as $room): ?>
                        <div class="detail-room-card">
                            <div class="detail-room-image">
                                <img src="<?php echo $room -> image_url; ?>" alt="<?php echo $room -> room_type; ?>">
                            </div>
                            <div class="detail-room-info">
                                <h4><?php echo $room -> room_type; ?></h4>
                                <p><i class="bi bi-lamp me-2"></i><?php echo $room -> bed_type; ?></p>
                                <p><i class="bi bi-door-open me-2"></i><?php echo $room -> room_number; ?></p>
                                <p><span class="detail-room-price">RM<?php echo $room -> price_per_night; ?></span>/night</p>
                                <div class="book-btn">
                                <?php if(strtolower(trim($room -> status)) === 'available'): ?>
                                    <span class="room-status available">Available</span>
                                    <a href="booking.php?room_id<?php echo (int)$room -> room_id; ?>" class="detail-book-btn">Book Now</a>
                                <?php else: ?>
                                    <span class="room-status unavailable"><?php echo ucfirst($room -> status); ?></span>
                                <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                </div>
                <?php endif; ?>
        </div>
        <div class="review-container">
            <h3>Hotel Facilities</h3>
            <div class="facilities">
                <p><i class="bi bi-wifi me-2" style="color: #2563EB;"></i>Wi-Fi</p>
                <p><i class="bi bi-water me-2" style="color: #2563EB;"></i>Swimming Pool</p>
                <p><i class="bi bi-car-front me-2" style="color: #2563EB;"></i>Free Parking</p>
                <p><i class="bi bi-snow me-2" style="color: #2563EB;"></i>Air Conditioning</p>
                <p><i class="bi bi-cup-hot me-2" style="color: #2563EB;"></i>Restaurant</p>
            </div>
            <hr>
            <h3>Guest Reviews</h3>
            <hr>
            <h6>Overall Rating</h6>
            <p><i class="bi bi-star-fill me-2" style="color: #F59E0B;"></i><?php echo $hotel -> rating; ?> / 5</p>
            <p>
                Based on <?php echo $totalReviews; ?>
                <?php echo $totalReviews === 1 ? 'review' : 'reviews'; ?>
            </p>
            <hr>
            <h6>Write a Review</h6>
            <?php if(isset($_SESSION['user']['id'])): ?>
            <form action="submit-review.php" method="post">
                <!-- current hotel_id -->
                <input type="hidden" name="hotel_id" value="<?php echo (int) $hotel -> hotel_id; ?>">
                <!-- room selection -->
                 <select name="room_id" id="room_id" required>
                    <option value="">Select a room</option>
                    <?php foreach($rooms as $room): ?>
                        <option value="<?php echo (int) $room -> room_id; ?>">
                            <?php echo $room -> room_type; ?>
                        </option>
                    <?php endforeach; ?>
                 </select>
                <label for="rating">Rating</label>
                <div class="star-rating">
                <i class="bi bi-star" data-rating="1"></i>
                <i class="bi bi-star" data-rating="2"></i>
                <i class="bi bi-star" data-rating="3"></i>
                <i class="bi bi-star" data-rating="4"></i>
                <i class="bi bi-star" data-rating="5"></i>
                </div>
                <input type="hidden" id="rating" name="rating" value="0">
                <div class="write-review">
                    <label for="comment">Your Review</label>
                    <textarea name="comment" id="comment" rows="3" placeholder="Write your review here..." required></textarea>
                </div>
                <button class="submit-review" type="submit">Submit Review</button>
            </form>
            <?php else: ?>
                <p>Please <a href="login.php">Log In</a> or <a href="register.php">Register</a> to write a review.</p>
            <?php endif; ?>
            <div class="review-section">
                <hr>
                <?php if(empty($reviews)): ?>
                    <p>No reviews yet.Be the first to review this hotel!</p>
                <?php else: ?>
                <?php foreach($reviews as $review): ?>
                    <div class="review-card">
                        <h5><?= htmlspecialchars($review -> fullname); ?></h5>
                    <div class="review-rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= (int) $review -> rating): ?>
                                <i class="bi bi-star-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <p><?= nl2br(htmlspecialchars($review -> comment)) ?></p>
                <small><?= htmlspecialchars($review -> created_at) ?></small>
                    </div>
                <?php endforeach; ?>
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
</body>
</html>