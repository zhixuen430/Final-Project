```php
<?php
session_start();

require_once __DIR__ . '/config/database.php';

// STEP 1: Check whether the user is logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// STEP 2: Allow cancellation only through POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mybooking.php');
    exit;
}

// STEP 3: Get user ID and booking ID
$userId = $_SESSION['user']['id'];
$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

// STEP 4: Validate booking ID
if (!$bookingId || $bookingId <= 0) {
    header('Location: mybooking.php?error=invalid_booking');
    exit;
}

try {
    // STEP 5: Cancel only the current user's pending booking
    $sql = "UPDATE bookings
            SET booking_status = 'Cancelled'
            WHERE booking_id = ?
              AND user_id = ?
              AND booking_status = 'Pending'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bookingId, $userId]);

    // STEP 6: Check whether a booking was cancelled
    if ($stmt->rowCount() > 0) {
        header('Location: mybooking.php?cancel=success');
        exit;
    } else {
        // Booking does not exist, belongs to another user,
        // or is not in Pending status
        header('Location: mybooking.php?error=cancel_failed');
        exit;
    }

} catch (PDOException $e) {
    error_log('Cancel booking error: ' . $e->getMessage());

    header('Location: mybooking.php?error=system_error');
    exit;
}
?>
```
