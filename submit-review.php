<?php

session_start();

// check user have login ? 

if(!isset($_SESSION['user']['id'])){
    header('Location:login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

// only allow form submition using post

if($_SERVER['REQUEST_METHOD']!=='POST'){
    header('Location:hotel.php');
    exit;
}

// get form data

$user_id = (int)$_SESSION['user']['id'];
$hotel_id = filter_input(INPUT_POST,'hotel_id',FILTER_VALIDATE_INT);
$room_id = filter_input(INPUT_POST,'room_id',FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST,'rating',FILTER_VALIDATE_INT);
$comment = trim($_POST['comment']?? '');

// validate the submit data

if(!$hotel_id || !$room_id || $rating === false || $rating === null || $rating < 1 || $rating > 5 || $comment === ''){
    header('Location:hotel.php?review=invalid');
    exit;
}

try{
    // check whether the hotel exists
    $hotelSql = "SELECT hotel_id FROM hotels_page WHERE hotel_id = ?";
    $hotelStmt = $pdo -> prepare($hotelSql);
    $hotelStmt -> execute([$hotel_id]);

    if(!$hotelStmt -> fetch()){
        header('Location:hotel.php?review=invalid');
        exit;
    }

    // check whether the room belong to this hotel
    $roomSql = "SELECT room_id FROM rooms where room_id = ? AND hotel_id = ?";
    $roomStmt = $pdo -> prepare($roomSql);
    $roomStmt -> execute([$room_id,$hotel_id]);

    if(!$roomStmt -> fetch()){
        header('Location:hotel-detail.php?id=' . $hotel_id . '&review=invalid');
        exit;
    }

    // insert review into database

    $sql = "INSERT INTO reviews(user_id,hotel_id,room_id,rating,comment)VALUES(?,?,?,?,?)";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute([$user_id,$hotel_id,$room_id,$rating,$comment]);

    // return to hotel-detail page

    header('Location:hotel-detail.php?id=' . $hotel_id . '&review=success');
    exit;
}catch(PDOException $e){
    error_log($e -> getMessage());
    header('Location:hotel-detail.php?id=' . $hotel_id . '&review=error');
    exit;
}

?>