<?php

session_start();

require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Find user by email
    $statement = $pdo->prepare(
        "SELECT * FROM users WHERE email = ?"
    );

    $statement->execute([$email]);
    $user = $statement->fetch(PDO::FETCH_OBJ);

    // Check whether the user exists and the password is correct
    if ($user && password_verify($password, $user->password)) {

        // Store user information in session
        $_SESSION['user'] = [
            'id'       => $user->user_id,
            'fullname' => $user->fullname,
            'name'     => $user->fullname,
            'email'    => $user->email,
            'role'     => $user->role
        ];

        // Redirect according to role
        if ($user->role === 'admin') {

            header('Location: admin/admin_dashboard.php');
            exit;

        } elseif ($user->role === 'staff') {

            header('Location: staff/staff_dashboard.php');
            exit;

        } elseif ($user->role === 'customer') {

            header('Location: home.php');
            exit;

        } else {

            $error = 'Invalid user role.';
        }

    } else {

        $error = 'Invalid email or password.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
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
            <h1 class="text-center mb-1">Welcome Back</h1>
            <p class="subtitle mt-1">Log in to your account</p>

            <?php if (!empty($error)): ?>
    <div style="color: red; margin-bottom: 15px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email" class="fw-bold">Email :</label><br>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="password" class="fw-bold">Password :</label><br>
                    <input type="password" id="password" name="password" placeholder="........" required><br><br>
                </div>
                <button type="submit" class="login">Log In</button>
            </form>
            <p class="auth-switch mt-3">Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
        </div>
    </div>
</body>
</html>