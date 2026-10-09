<?php

session_start();

require_once __DIR__ . '/config/database.php';

// check whether user has login

if(!isset($_SESSION['user']['id'])){
    header('Location:login.php');
    exit;
}

// only accept post method

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location:room.php');
    exit;
}

// get the form data

$roomId = filter_input(INPUT_POST,'room_id',FILTER_VALIDATE_INT);
$checkIn = trim($_POST['check_in_date'] ?? '');
$checkOut = trim($_POST['check_out_date'] ?? '');

// display an error and stop processing

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
            . htmlspecialchars($message,ENT_QUOTES,'UTF-8') .
        '</div>
        <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
        </div>
    </body>
    </html>';
    exit;
}

// validate the input

if(!$roomId || $checkIn === '' || $checkOut === ''){
    showError('Please provide a valid room and both dates.');
}

$checkInDate = DateTimeImmutable::createFromFormat('!Y-m-d',$checkIn);
$checkOutDate = DateTimeImmutable::createFromFormat('!Y-m-d',$checkOut);

if(!$checkInDate || !$checkOutDate || $checkInDate -> format('Y-m-d') !== $checkIn || $checkOutDate -> format('Y-m-d') !== $checkOut){
    showError('Please select valid check-in and check-out dates.');
}

$today = new DateTimeImmutable('today');

if($checkInDate < $today){
    showError('Check-in date cannot be in the past.');
}

if($checkOutDate <= $checkInDate){
    showError('Check-out date must be after check-in date.');
}

try{

// get the room and price from the database

$sql = "SELECT room_id,hotel_id,price_per_night,status FROM rooms WHERE room_id = ?";
$stmt = $pdo -> prepare($sql);
$stmt -> execute([$roomId]);
$room = $stmt -> fetch(PDO::FETCH_OBJ);

if(!$room){
    showError('The selected room could not be found.');
}
if(strtolower(trim($room -> status)) !== 'available'){
    showError('This room is currently unavailable');
}

// check for overlapping bookings

$sql = "SELECT COUNT(*) FROM bookings WHERE room_id = ? AND check_in_date < ? AND check_out_date > ? AND booking_status IN ('pending','confirmed')";
$stmt = $pdo -> prepare($sql);
$stmt -> execute([$roomId,$checkOut,$checkIn]);

$overlappingBookings = (int) $stmt -> fetchColumn();

if($overlappingBookings > 0){
    showError('This room already has a booking for those dates.Please choose different dates.');
}

// calculate the total price

$nights = (int) $checkInDate -> diff($checkOutDate) -> days;
$pricePerNight = (float) $room -> price_per_night;
$totalPrice = round($pricePerNight * $nights, 2);

if($nights <= 0 || $pricePerNight <= 0){
    showError('The room price or booking dates are invalid');
}

// save the booking to sql

$sql = "INSERT INTO bookings(user_id,hotel_id,room_id,check_in_date,check_out_date,total_price,booking_status)VALUES(?,?,?,?,?,?,'pending')";
$stmt = $pdo -> prepare($sql);
$stmt -> execute([$_SESSION['user']['id'],$room -> hotel_id,$roomId,$checkIn,$checkOut,$totalPrice]);

// redirect after successful booking

header('Location:mybooking.php?booking=success');
exit;
}catch (PDOException $e){

    // log technical detail on the server,not to the customer

    error_log('Booking error: ' . $e -> getMessage());
    showError('Unable to save your booking.Please check your database configuration and try again.');
}