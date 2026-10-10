
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

// Only admin can access this page
if (
    !isset($_SESSION['user']['role']) ||
    $_SESSION['user']['role'] !== 'admin'
) {
    header('Location: ../home.php');
    exit;
}

$error = '';
$success = '';

$valid_roles = ['admin', 'staff', 'customer'];

// Escape output
function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Add or update user
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['save_user'])
) {
    $user_id = trim($_POST['user_id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (
        $fullname === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($role, $valid_roles, true)
    ) {
        $error = 'Please enter valid information for all fields.';
    } elseif ($user_id === '' && strlen($password) < 8) {
        $error = 'New users must have a password of at least 8 characters.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            // Check whether email is already used
            $sql = "SELECT user_id
                    FROM users
                    WHERE email = ?
                    AND user_id != ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $email,
                $user_id === '' ? 0 : (int)$user_id
            ]);

            if ($stmt->fetch()) {
                $error = 'This email address is already registered.';
            } else {
                if ($user_id === '') {
                    // Create user
                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $sql = "INSERT INTO users
                            (fullname, email, password, role)
                            VALUES (?, ?, ?, ?)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $fullname,
                        $email,
                        $hashedPassword,
                        $role
                    ]);

                    $success = 'User added successfully.';
                } else {
                    // Update user without changing password
                    if ($password === '') {
                        $sql = "UPDATE users
                                SET fullname = ?,
                                    email = ?,
                                    role = ?
                                WHERE user_id = ?";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $fullname,
                            $email,
                            $role,
                            (int)$user_id
                        ]);
                    } else {
                        // Update user with a new password
                        $hashedPassword = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $sql = "UPDATE users
                                SET fullname = ?,
                                    email = ?,
                                    password = ?,
                                    role = ?
                                WHERE user_id = ?";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $fullname,
                            $email,
                            $hashedPassword,
                            $role,
                            (int)$user_id
                        ]);
                    }

                    // Update current session if admin edits own profile
                    if ((int)$user_id === (int)$_SESSION['user']['id']) {
                        $_SESSION['user']['role'] = $role;
                    }

                    $success = 'User updated successfully.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Unable to save user. Please check the database fields.';
        }
    }
}

// Delete user
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_user'])
) {
    $delete_id = filter_input(
        INPUT_POST,
        'delete_user',
        FILTER_VALIDATE_INT
    );

    if (!$delete_id) {
        $error = 'Invalid user ID.';
    } elseif (
        $delete_id === (int)$_SESSION['user']['id']
    ) {
        $error = 'You cannot delete your own account.';
    } else {
        try {
            // Prevent deleting the last admin account
            $stmt = $pdo->prepare(
                "SELECT role FROM users WHERE user_id = ?"
            );
            $stmt->execute([$delete_id]);
            $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$targetUser) {
                $error = 'User not found.';
            } elseif ($targetUser['role'] === 'admin') {
                $stmt = $pdo->query(
                    "SELECT COUNT(*) FROM users WHERE role = 'admin'"
                );

                if ((int)$stmt->fetchColumn() <= 1) {
                    $error = 'You cannot delete the last admin account.';
                }
            }

            if ($error === '') {
                $stmt = $pdo->prepare(
                    "DELETE FROM users WHERE user_id = ?"
                );
                $stmt->execute([$delete_id]);

                $success = 'User deleted successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to delete this user. The account may be linked to existing bookings or reviews.';
        }
    }
}

// Get user for editing
$edit_user = null;

$edit_id = filter_input(
    INPUT_GET,
    'edit',
    FILTER_VALIDATE_INT
);

if ($edit_id) {
    $stmt = $pdo->prepare(
        "SELECT user_id, fullname, email, role
         FROM users
         WHERE user_id = ?"
    );
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$edit_user) {
        $error = 'User not found.';
    }
}

// Get all users
$stmt = $pdo->query(
    "SELECT user_id, fullname, email, role
     FROM users
     ORDER BY user_id DESC"
);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total user count
$totalUsers = count($users);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fa;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        .page-content {
            padding: 30px;
        }

        .page-title {
            color: #0f2747;
            font-weight: bold;
        }

        .user-form,
        .user-table {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 28px;
        }

        .summary-card {
            background: #0f2747;
            color: #ffffff;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 28px;
        }

        .summary-card h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .summary-card p {
            font-size: 30px;
            font-weight: bold;
            margin: 0;
        }

        .btn-primary {
            background: #0f2747;
            border-color: #0f2747;
        }

        .btn-primary:hover {
            background: #193d69;
            border-color: #193d69;
        }

        .table th {
            background: #0f2747;
            color: #ffffff;
            white-space: nowrap;
        }

        .role-badge {
            display: inline-block;
            min-width: 90px;
            padding: 6px 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .role-admin {
            background: #cfe2ff;
            color: #084298;
        }

        .role-staff {
            background: #fff3cd;
            color: #664d03;
        }

        .role-customer {
            background: #d1e7dd;
            color: #0f5132;
        }

        @media (max-width: 768px) {
            .page-content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="page-content">

    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 mb-4">
        <div>
            <h2 class="page-title">Manage Users</h2>
            <p class="text-secondary mb-0">
                View and manage system user accounts.
            </p>
        </div>

        <a href="admin_dashboard.php" class="btn btn-outline-primary">
            Back to Dashboard
        </a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <!-- Total Users -->
    <div class="summary-card">
        <h3>Total Users</h3>
        <p><?= $totalUsers ?></p>
    </div>

    <!-- Add / Edit User Form -->
    <div class="user-form">

        <h4 class="mb-4">
            <?= $edit_user ? 'Edit User' : 'Add New User' ?>
        </h4>

        <form method="post">

            <input
                type="hidden"
                name="user_id"
                value="<?= e($edit_user['user_id'] ?? '') ?>"
            >

            <div class="row g-3">

                <div class="col-md-6">
                    <label for="fullname" class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        class="form-control"
                        required
                        maxlength="100"
                        value="<?= e($edit_user['fullname'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        required
                        maxlength="255"
                        value="<?= e($edit_user['email'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        <?= $edit_user ? '' : 'required' ?>
                    >

                    <small class="text-secondary">
                        <?= $edit_user
                            ? 'Leave blank to keep the current password.'
                            : 'At least 8 characters.' ?>
                    </small>
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label">
                        Role
                    </label>

                    <select
                        id="role"
                        name="role"
                        class="form-select"
                        required
                    >
                        <?php foreach ($valid_roles as $role): ?>
                            <option
                                value="<?= e($role) ?>"
                                <?= (
                                    ($edit_user['role'] ?? 'customer') === $role
                                ) ? 'selected' : '' ?>
                            >
                                <?= ucfirst(e($role)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">

                    <button
                        type="submit"
                        name="save_user"
                        value="1"
                        class="btn btn-primary"
                    >
                        <?= $edit_user ? 'Update User' : 'Add User' ?>
                    </button>

                    <?php if ($edit_user): ?>
                        <a
                            href="admin_user.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>
                    <?php endif; ?>

                </div>

            </div>
        </form>
    </div>

    <!-- User List -->
    <div class="user-table">

        <h4 class="mb-3">User List</h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($users) > 0): ?>

                    <?php foreach ($users as $user): ?>

                        <tr>
                            <td><?= (int)$user['user_id'] ?></td>

                            <td><?= e($user['fullname']) ?></td>

                            <td><?= e($user['email']) ?></td>

                            <td>
                                <span class="role-badge role-<?= e($user['role']) ?>">
                                    <?= e($user['role']) ?>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    <a
                                        href="admin_user.php?edit=<?= (int)$user['user_id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');"
                                    >
                                        <button
                                            type="submit"
                                            name="delete_user"
                                            value="<?= (int)$user['user_id'] ?>"
                                            class="btn btn-sm btn-danger"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            No users found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>