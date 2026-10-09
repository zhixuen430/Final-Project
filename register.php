<?php

session_start();

require_once __DIR__ .'/config/database.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];

// check password

if($password !== $confirmPassword){
    $error = 'Password do not match.';
}else{

// check if email is already exists

$statement = $pdo ->prepare("SELECT * FROM users WHERE email = ?");
$statement ->execute([$email]);
if($statement -> fetch()){
    $error =  'That email is already registered.';
}else{

// hash password 

$hashedPassword = password_hash($password,PASSWORD_DEFAULT);

// inner user

$statement = $pdo ->prepare("INSERT INTO users(fullname,email,password)VALUES(?,?,?)");
$statement ->execute([
    $name,
    $email,
    $hashedPassword
]);
$userID = $pdo ->lastInsertId();

// store user information in session

$_SESSION['user'] = [
    'id' => $userID,
    'name' => $name,
    'email' => $email,
    'role' => 'customer'
];
 header('Location:home.php');
    exit;
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>
    <nav>
        <h1 class="header-title">HOTEL BOOKING</h1>
    </nav>
    <div class="box">
            <div class="hotel-photo">
                <img src="https://images.openai.com/static-rsc-4/ZOKN1p4_ncRFoLRvE3vMoDlvKhZupxvEHy84pNWNKFUlYPCH4fuPX0-VRrChYoNuqEQ4dIGseCuUv_2Xr1v4-PB3Armo_b_bFQQD6I-ZYbRqpD4ft4PfN84XmJG6bmlwZduAtM_TA3_w4eQcX_fJvV6iRDvGhauZiN7bFN1Aawwj9Akb_bhGumAAYBtiS3w4?purpose=fullsize" alt="photo">
            </div>
        <div class="container">
        <div class="right-container">
            <h1 class="text-center mb-1">Create Account</h1>
            <p class="subtitle mt-1">Sign up to start booking</p>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name" class="fw-bold">Full Name :</label><br>
                    <input type="text" id="name" name="name" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label for="email" class="fw-bold">Email :</label><br>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="password" class="fw-bold">Password :</label><br>
                    <input type="password" id="password" name="password" placeholder="........" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="fw-bold">Confirm Password :</label><br>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="........" required><br><br>
                </div>
                <button type="submit" class="signup">Sign Up</button>
            </form>
            <p class="auth-switch mt-3">Already have an account? <a href="login.php">Log in</a></p>
        </div>
        </div>
    </div>
    
</body>
</html>