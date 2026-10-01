<?php

$host = 'localhost';
$dbname = 'hotel_booking';
$user = 'root';
$password = '';

$pdo = new PDO("mysql:host=localhost;dbname=hotel_booking","root","");
$pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

?>