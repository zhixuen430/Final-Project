
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// ========================================
// 1. Check login and admin permission
// ========================================
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

if (
    !isset($_SESSION['user']['role']) ||
    $_SESSION['user']['role'] !== 'admin'
) {
    header('Location: ../home.php');
    exit;
}

// ========================================
// 2. Initialize variables
// ========================================
$error = '';
$success = '';

$valid_statuses = [
    'available',
    'occupied',
    'unavailable',
    'maintenance'
];

// CSRF protection token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

// Escape output safely
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// ========================================
// 3. Handle Add / Update Room
// ========================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['save_room'])
) {
    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            (string) $_POST['csrf_token']
        )
    ) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $room_id = trim($_POST['room_id'] ?? '');

        $hotel_id = filter_var(
            $_POST['hotel_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $room_number = trim($_POST['room_number'] ?? '');
        $room_type = trim($_POST['room_type'] ?? '');
        $bed_type = trim($_POST['bed_type'] ?? '');
        $price = trim($_POST['price_per_night'] ?? '');
        $status = $_POST['status'] ?? '';
        $image_url = trim($_POST['image_url'] ?? '');

        $valid_room_types = [
            'Single Room',
            'Double Room',
            'Twin Room',
            'Deluxe Room',
            'Family Room',
            'Suite'
        ];

        // Validate all fields
        if (
            !$hotel_id ||
            $room_number === '' ||
            $room_type === '' ||
            $bed_type === '' ||
            $price === '' ||
            $image_url === ''
        ) {
            $error = 'Please fill in all required fields.';
        } elseif (
            !in_array($room_type, $valid_room_types, true)
        ) {
            $error = 'Please select a valid room type.';
        } elseif (
            !is_numeric($price) ||
            !is_finite((float) $price) ||
            (float) $price < 0
        ) {
            $error = 'Please enter a valid room price.';
        } elseif (
            !in_array($status, $valid_statuses, true)
        ) {
            $error = 'Please select a valid room status.';
        } elseif (
            filter_var($image_url, FILTER_VALIDATE_URL) === false ||
            !in_array(
                strtolower((string) parse_url($image_url, PHP_URL_SCHEME)),
                ['http', 'https'],
                true
            )
        ) {
            $error = 'Please enter a valid HTTP or HTTPS image URL.';
        } elseif (
            $room_id !== '' &&
            filter_var($room_id, FILTER_VALIDATE_INT) === false
        ) {
            $error = 'Invalid room ID.';
        } else {
            try {
                // Check that the selected hotel exists
                $hotelStmt = $pdo->prepare(
                    'SELECT COUNT(*) FROM hotels_page WHERE hotel_id = ?'
                );
                $hotelStmt->execute([$hotel_id]);

                if ((int) $hotelStmt->fetchColumn() === 0) {
                    $error = 'The selected hotel does not exist.';
                } else {
                    if ($room_id !== '') {
                        // Update existing room
                        $sql = "UPDATE rooms
                                SET hotel_id = ?,
                                    room_number = ?,
                                    room_type = ?,
                                    bed_type = ?,
                                    price_per_night = ?,
                                    status = ?,
                                    image_url = ?
                                WHERE room_id = ?";

                        $stmt = $pdo->prepare($sql);

                        $stmt->execute([
                            $hotel_id,
                            $room_number,
                            $room_type,
                            $bed_type,
                            $price,
                            $status,
                            $image_url,
                            $room_id
                        ]);

                        if ($stmt->rowCount() > 0) {
                            $success = 'Room updated successfully.';
                        } else {
                            $checkStmt = $pdo->prepare(
                                'SELECT COUNT(*) FROM rooms WHERE room_id = ?'
                            );
                            $checkStmt->execute([$room_id]);

                            if ((int) $checkStmt->fetchColumn() > 0) {
                                $success = 'No changes were made to the room.';
                            } else {
                                $error = 'Room not found.';
                            }
                        }
                    } else {
                        // Add a new room
                        $sql = "INSERT INTO rooms
                                (
                                    hotel_id,
                                    room_number,
                                    room_type,
                                    bed_type,
                                    price_per_night,
                                    status,
                                    image_url
                                )
                                VALUES (?, ?, ?, ?, ?, ?, ?)";

                        $stmt = $pdo->prepare($sql);

                        $stmt->execute([
                            $hotel_id,
                            $room_number,
                            $room_type,
                            $bed_type,
                            $price,
                            $status,
                            $image_url
                        ]);

                        $success = 'Room added successfully.';
                    }
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Unable to save the room. Please check the database fields and constraints.';
            }
        }
    }
}

// ========================================
// 4. Handle Delete Room
// ========================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_room'])
) {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            (string) $_POST['csrf_token']
        )
    ) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $room_id = filter_var(
            $_POST['room_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        if (!$room_id) {
            $error = 'Invalid room ID.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'DELETE FROM rooms WHERE room_id = ?'
                );

                $stmt->execute([$room_id]);

                if ($stmt->rowCount() > 0) {
                    $success = 'Room deleted successfully.';
                } else {
                    $error = 'Room not found.';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());

                $error = 'Unable to delete this room. It may be linked to existing bookings or reviews.';
            }
        }
    }
}

// ========================================
// 5. Get room to edit
// ========================================
$edit_room = null;

if (isset($_GET['edit'])) {
    $edit_id = filter_var(
        $_GET['edit'],
        FILTER_VALIDATE_INT
    );

    if ($edit_id) {
        $stmt = $pdo->prepare(
            'SELECT * FROM rooms WHERE room_id = ?'
        );

        $stmt->execute([$edit_id]);
        $edit_room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$edit_room) {
            $error = 'Room not found.';
        }
    } else {
        $error = 'Invalid room ID.';
    }
}

// ========================================
// 6. Get hotels for dropdown
// ========================================
$hotelStmt = $pdo->query(
    'SELECT hotel_id, hotel_name
     FROM hotels_page
     ORDER BY hotel_name'
);

$hotels = $hotelStmt->fetchAll(PDO::FETCH_ASSOC);

// ========================================
// 7. Get all rooms and hotel names
// ========================================
$sql = "SELECT rooms.*, hotels_page.hotel_name
        FROM rooms
        INNER JOIN hotels_page
            ON rooms.hotel_id = hotels_page.hotel_id
        ORDER BY rooms.room_id DESC";

$stmt = $pdo->query($sql);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================================
// 8. Room types and bed types
// ========================================
$room_types = [
    'Single Room',
    'Double Room',
    'Twin Room',
    'Deluxe Room',
    'Family Room',
    'Suite'
];

$bed_types = [
    'Single Bed',
    'Double Bed',
    'Twin Beds',
    'Queen Bed',
    'King Bed'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms | Hotel Booking</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../assets/css/admin.css">

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

        .room-form,
        .room-table {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 28px;
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
            color: #fff;
            white-space: nowrap;
        }

        .room-image {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 7px;
        }

        .status-badge {
            display: inline-block;
            min-width: 110px;
            padding: 6px 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .status-available {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-occupied {
            background: #cfe2ff;
            color: #084298;
        }

        .status-unavailable {
            background: #f8d7da;
            color: #842029;
        }

        .status-maintenance {
            background: #fff3cd;
            color: #664d03;
        }

        .room-actions {
            min-width: 150px;
        }

        @media (max-width: 768px) {
            .page-content {
                padding: 15px;
            }

            .room-form,
            .room-table {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<div class="page-content">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 mb-4">

        <div>
            <h2 class="page-title">Manage Rooms</h2>
            <p class="text-secondary mb-0">
                Add, edit and manage hotel room information.
            </p>
        </div>

        <a
            href="admin_dashboard.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Dashboard
        </a>

    </div>

    <!-- Success Message -->
    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i>
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Add / Edit Room Form -->
    <div class="room-form">

        <h4 class="mb-4">
            <i class="bi bi-door-open"></i>
            <?= $edit_room ? 'Edit Room' : 'Add New Room' ?>
        </h4>

        <?php if (count($hotels) === 0): ?>

            <div class="alert alert-warning">
                Please add a hotel before adding rooms.
            </div>

        <?php else: ?>

            <form method="POST" action="admin_room.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($csrf_token) ?>"
                >

                <input
                    type="hidden"
                    name="room_id"
                    value="<?= e($edit_room['room_id'] ?? '') ?>"
                >

                <div class="row g-3">

                    <!-- Hotel -->
                    <div class="col-md-6">
                        <label class="form-label">Hotel</label>

                        <select
                            name="hotel_id"
                            class="form-select"
                            required
                        >
                            <option value="">Select Hotel</option>

                            <?php foreach ($hotels as $hotel): ?>
                                <option
                                    value="<?= e($hotel['hotel_id']) ?>"
                                    <?= (
                                        (string)($edit_room['hotel_id'] ?? '')
                                        === (string)$hotel['hotel_id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= e($hotel['hotel_name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <!-- Room Number -->
                    <div class="col-md-6">
                        <label class="form-label">Room Number</label>

                        <input
                            type="text"
                            name="room_number"
                            class="form-control"
                            value="<?= e($edit_room['room_number'] ?? '') ?>"
                            placeholder="e.g. 101"
                            maxlength="20"
                            required
                        >
                    </div>

                    <!-- Room Type -->
                    <div class="col-md-6">
                        <label class="form-label">Room Type</label>

                        <select
                            name="room_type"
                            class="form-select"
                            required
                        >
                            <option value="">Select Room Type</option>

                            <?php foreach ($room_types as $type): ?>
                                <option
                                    value="<?= e($type) ?>"
                                    <?= (
                                        ($edit_room['room_type'] ?? '') === $type
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= e($type) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <!-- Bed Type -->
                    <div class="col-md-6">
                        <label class="form-label">Bed Type</label>

                        <select
                            name="bed_type"
                            class="form-select"
                            required
                        >
                            <option value="">Select Bed Type</option>

                            <?php foreach ($bed_types as $bed): ?>
                                <option
                                    value="<?= e($bed) ?>"
                                    <?= (
                                        ($edit_room['bed_type'] ?? '') === $bed
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= e($bed) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <!-- Price -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Price per Night (RM)
                        </label>

                        <input
                            type="number"
                            name="price_per_night"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="<?= e($edit_room['price_per_night'] ?? '') ?>"
                            placeholder="e.g. 250.00"
                            required
                        >
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">Room Status</label>

                        <?php
                        $current_status =
                            $edit_room['status'] ?? 'available';
                        ?>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >
                            <option
                                value="available"
                                <?= $current_status === 'available'
                                    ? 'selected' : '' ?>
                            >
                                Available
                            </option>

                            <option
                                value="occupied"
                                <?= $current_status === 'occupied'
                                    ? 'selected' : '' ?>
                            >
                                Occupied
                            </option>

                            <option
                                value="unavailable"
                                <?= $current_status === 'unavailable'
                                    ? 'selected' : '' ?>
                            >
                                Unavailable
                            </option>

                            <option
                                value="maintenance"
                                <?= $current_status === 'maintenance'
                                    ? 'selected' : '' ?>
                            >
                                Maintenance
                            </option>
                        </select>
                    </div>

                    <!-- Image URL -->
                    <div class="col-12">
                        <label class="form-label">Room Image URL</label>

                        <input
                            type="url"
                            name="image_url"
                            class="form-control"
                            value="<?= e($edit_room['image_url'] ?? '') ?>"
                            placeholder="https://example.com/room.jpg"
                            required
                        >

                        <small class="text-secondary">
                            Enter a valid image URL beginning with
                            https:// or http://.
                        </small>
                    </div>

                    <!-- Image Preview -->
                    <?php if (!empty($edit_room['image_url'])): ?>
                        <div class="col-12">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img
                                    src="<?= e($edit_room['image_url']) ?>"
                                    alt="Current room image"
                                    class="room-image"
                                >
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Buttons -->
                    <div class="col-12 d-flex flex-wrap gap-2">

                        <button
                            type="submit"
                            name="save_room"
                            value="1"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-save"></i>
                            <?= $edit_room ? 'Update Room' : 'Add Room' ?>
                        </button>

                        <?php if ($edit_room): ?>
                            <a
                                href="admin_room.php"
                                class="btn btn-secondary"
                            >
                                Cancel
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

            </form>

        <?php endif; ?>

    </div>

    <!-- Room List -->
    <div class="room-table">

        <div class="d-flex justify-content-between
                    align-items-center flex-wrap gap-2 mb-3">

            <h4 class="mb-0">Room List</h4>

            <span class="text-secondary">
                Total Rooms: <?= count($rooms) ?>
            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Image</th>
                        <th>Hotel</th>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Bed Type</th>
                        <th>Price / Night</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($rooms) > 0): ?>

                        <?php foreach ($rooms as $room): ?>

                            <?php
                            $status_class = in_array(
                                $room['status'],
                                $valid_statuses,
                                true
                            )
                                ? 'status-' . $room['status']
                                : '';
                            ?>

                            <tr>

                                <td>
                                    <?= e($room['room_id']) ?>
                                </td>

                                <td>
                                    <?php if (!empty($room['image_url'])): ?>
                                        <img
                                            src="<?= e($room['image_url']) ?>"
                                            alt="<?= e($room['room_type']) ?>"
                                            class="room-image"
                                            loading="lazy"
                                        >
                                    <?php else: ?>
                                        <span class="text-secondary">
                                            No image
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= e($room['hotel_name']) ?>
                                </td>

                                <td>
                                    <?= e($room['room_number']) ?>
                                </td>

                                <td>
                                    <?= e($room['room_type']) ?>
                                </td>

                                <td>
                                    <?= e($room['bed_type']) ?>
                                </td>

                                <td>
                                    RM <?= number_format(
                                        (float)$room['price_per_night'],
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <span class="status-badge <?= e($status_class) ?>">
                                        <?= e(ucfirst($room['status'])) ?>
                                    </span>
                                </td>

                                <td class="room-actions">
                                    <div class="d-flex gap-2">

                                        <!-- Edit -->
                                        <a
                                            href="admin_room.php?edit=<?= e($room['room_id']) ?>"
                                            class="btn btn-sm btn-warning"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>

                                        <!-- Delete -->
                                        <form
                                            method="POST"
                                            action="admin_room.php"
                                            onsubmit="return confirm('Are you sure you want to delete this room?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($csrf_token) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="room_id"
                                                value="<?= e($room['room_id']) ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_room"
                                                value="1"
                                                class="btn btn-sm btn-danger"
                                            >
                                                <i class="bi bi-trash"></i>
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center py-4">
                                No rooms found. Add your first room above.
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