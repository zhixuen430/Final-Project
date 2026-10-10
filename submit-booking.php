
<?php

session_start();

require_once __DIR__ . '/config/database.php';

// Check whether the user has logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: room.php');
    exit;
}

// Get form data
$roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
$checkIn = trim($_POST['check_in_date'] ?? '');
$checkOut = trim($_POST['check_out_date'] ?? '');

// Display an error message
function showError($message)
{
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="alert alert-danger">'
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') .
            '</div>
            <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
        </div>
    </body>
    </html>';
    exit;
}

// Validate input
if (!$roomId || $checkIn === '' || $checkOut === '') {
    showError('Please provide a valid room and both dates.');
}

$checkInDate = DateTimeImmutable::createFromFormat('!Y-m-d', $checkIn);
$checkOutDate = DateTimeImmutable::createFromFormat('!Y-m-d', $checkOut);

if (
    !$checkInDate ||
    !$checkOutDate ||
    $checkInDate->format('Y-m-d') !== $checkIn ||
    $checkOutDate->format('Y-m-d') !== $checkOut
) {
    showError('Please select valid check-in and check-out dates.');
}

$today = new DateTimeImmutable('today');

if ($checkInDate < $today) {
    showError('Check-in date cannot be in the past.');
}

if ($checkOutDate <= $checkInDate) {
    showError('Check-out date must be after check-in date.');
}

try {

    // Start a transaction
    $pdo->beginTransaction();

    // Lock the room record to prevent simultaneous booking checks
    $sql = "SELECT room_id, hotel_id, price_per_night, status
            FROM rooms
            WHERE room_id = ?
            FOR UPDATE";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$room) {
        throw new RuntimeException(
            'The selected room could not be found.'
        );
    }

    // Only maintenance rooms cannot be booked
    if (strtolower(trim($room->status)) === 'maintenance') {
        throw new RuntimeException(
            'This room is currently unavailable.'
        );
    }

    // Check for overlapping bookings
    // A booking is excluded only when its status is cancelled
    $sql = "SELECT COUNT(*)
            FROM bookings
            WHERE room_id = ?
              AND check_in_date < ?
              AND check_out_date > ?
              AND booking_status <> 'cancelled'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $roomId,
        $checkOut,
        $checkIn
    ]);

    $overlappingBookings = (int) $stmt->fetchColumn();

    if ($overlappingBookings > 0) {
        throw new RuntimeException(
            'This room is already booked for those dates. Please choose different dates.'
        );
    }

    // Calculate total price
    $nights = (int) $checkInDate->diff($checkOutDate)->days;
    $pricePerNight = (float) $room->price_per_night;

    if ($nights <= 0 || $pricePerNight <= 0) {
        throw new RuntimeException(
            'The room price or booking dates are invalid.'
        );
    }

    $totalPrice = round($pricePerNight * $nights, 2);

    // Save the booking
    $sql = "INSERT INTO bookings
            (
                user_id,
                hotel_id,
                room_id,
                check_in_date,
                check_out_date,
                total_price,
                booking_status
            )
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_SESSION['user']['id'],
        $room->hotel_id,
        $roomId,
        $checkIn,
        $checkOut,
        $totalPrice
    ]);

    // Commit only when everything succeeds
    $pdo->commit();

    // Redirect after successful booking
    header('Location: mybooking.php?booking=success');
    exit;

} catch (Throwable $e) {

    // Roll back changes if anything fails
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e instanceof PDOException) {
        error_log('Booking error: ' . $e->getMessage());

        showError(
            'Unable to save your booking. Please try again.'
        );
    }

    showError($e->getMessage());
}