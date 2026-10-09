<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

// Only admin can manage rooms
if (
    !isset($_SESSION['user']['role']) ||
    $_SESSION['user']['role'] !== 'admin'
) {
    header('Location: ../home.php');
    exit;
}

$error = '';
$success = '';

// Add or update room
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['save_room'])
) {
    $room_id = $_POST['room_id'] ?? '';
    $hotel_id = filter_input(
        INPUT_POST,
        'hotel_id',
        FILTER_VALIDATE_INT
    );
    $room_number = trim($_POST['room_number'] ?? '');
    $room_type = trim($_POST['room_type'] ?? '');
    $price = $_POST['price_per_night'] ?? '';
    $status = $_POST['status'] ?? '';

    $valid_statuses = ['available', 'unavailable', 'maintenance'];

    if (
        !$hotel_id ||
        $room_number === '' ||
        $room_type === '' ||
        $price === '' ||
        !is_numeric($price) ||
        (float)$price < 0 ||
        !in_array($status, $valid_statuses, true)
    ) {
        $error = 'Please enter valid information for all fields.';
    } else {
        try {
            if ($room_id !== '') {
                // Update room
                $sql = "UPDATE rooms
                        SET hotel_id = ?,
                            room_number = ?,
                            room_type = ?,
                            price_per_night = ?,
                            status = ?
                        WHERE room_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $hotel_id,
                    $room_number,
                    $room_type,
                    $price,
                    $status,
                    $room_id
                ]);

                $success = 'Room updated successfully.';
            } else {
                // Add room
                $sql = "INSERT INTO rooms
                        (hotel_id, room_number, room_type,
                         price_per_night, status)
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $hotel_id,
                    $room_number,
                    $room_type,
                    $price,
                    $status
                ]);

                $success = 'Room added successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to save room. Check the hotel, room number and database constraints.';
        }
    }
}

// Delete room
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_room'])
) {
    $room_id = filter_input(
        INPUT_POST,
        'room_id',
        FILTER_VALIDATE_INT
    );

    if ($room_id) {
        try {
            $stmt = $pdo->prepare(
                "DELETE FROM rooms WHERE room_id = ?"
            );
            $stmt->execute([$room_id]);

            $success = $stmt->rowCount() > 0
                ? 'Room deleted successfully.'
                : 'Room not found.';
        } catch (PDOException $e) {
            $error = 'Unable to delete this room. It may be linked to existing bookings.';
        }
    } else {
        $error = 'Invalid room ID.';
    }
}

// Get room to edit
$edit_room = null;

if (isset($_GET['edit'])) {
    $edit_id = filter_input(
        INPUT_GET,
        'edit',
        FILTER_VALIDATE_INT
    );

    if ($edit_id) {
        $stmt = $pdo->prepare(
            "SELECT * FROM rooms WHERE room_id = ?"
        );
        $stmt->execute([$edit_id]);
        $edit_room = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Get hotels for dropdown
$stmt = $pdo->query(
    "SELECT hotel_id, hotel_name
     FROM hotels_page
     ORDER BY hotel_name"
);
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all rooms with hotel names
$sql = "SELECT rooms.*, hotels_page.hotel_name
        FROM rooms
        INNER JOIN hotels_page
            ON rooms.hotel_id = hotels_page.hotel_id
        ORDER BY rooms.room_id DESC";

$stmt = $pdo->query($sql);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
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

        .status-badge {
            display: inline-block;
            min-width: 105px;
            padding: 6px 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }

        .status-available {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-unavailable {
            background: #f8d7da;
            color: #842029;
        }

        .status-maintenance {
            background: #fff3cd;
            color: #664d03;
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
            <h2 class="page-title">Manage Rooms</h2>
            <p class="text-secondary mb-0">
                Manage hotel rooms, prices and availability.
            </p>
        </div>

        <a
            href="admin_dashboard.php"
            class="btn btn-outline-secondary"
        >
            Back to Dashboard
        </a>

    </div>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>


    <!-- Add / Edit Room Form -->
    <div class="room-form">

        <h4 class="mb-4">
            <?= $edit_room ? 'Edit Room' : 'Add New Room' ?>
        </h4>

        <?php if (count($hotels) === 0): ?>

            <div class="alert alert-warning">
                Please add a hotel before adding rooms.
            </div>

        <?php else: ?>

            <form method="POST" action="admin-rooms.php">

                <input
                    type="hidden"
                    name="room_id"
                    value="<?= e($edit_room['room_id'] ?? '') ?>"
                >

                <div class="row g-3">

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

                    <div class="col-md-6">
                        <label class="form-label">Room Number</label>

                        <input
                            type="text"
                            name="room_number"
                            class="form-control"
                            value="<?= e($edit_room['room_number'] ?? '') ?>"
                            placeholder="e.g. 101"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Room Type</label>

                        <select
                            name="room_type"
                            class="form-select"
                            required
                        >
                            <option value="">Select Room Type</option>

                            <?php
                            $room_types = [
                                'Single Room',
                                'Double Room',
                                'Twin Room',
                                'Deluxe Room',
                                'Family Room',
                                'Suite'
                            ];
                            ?>

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
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >
                            <?php
                            $current_status =
                                $edit_room['status'] ?? 'available';
                            ?>

                            <option
                                value="available"
                                <?= $current_status === 'available'
                                    ? 'selected' : '' ?>
                            >
                                Available
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

                    <div class="col-12 d-flex gap-2">

                        <button
                            type="submit"
                            name="save_room"
                            value="1"
                            class="btn btn-primary"
                        >
                            <?= $edit_room ? 'Update Room' : 'Add Room' ?>
                        </button>

                        <?php if ($edit_room): ?>
                            <a
                                href="admin-rooms.php"
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

        <h4 class="mb-3">Room List</h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Hotel</th>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Price / Night</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($rooms) > 0): ?>

                    <?php foreach ($rooms as $room): ?>

                        <?php
                        $status_class = 'status-' . $room['status'];
                        ?>

                        <tr>
                            <td><?= e($room['room_id']) ?></td>

                            <td><?= e($room['hotel_name']) ?></td>

                            <td><?= e($room['room_number']) ?></td>

                            <td><?= e($room['room_type']) ?></td>

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

                            <td>
                                <div class="d-flex gap-2">

                                    <a
                                        href="admin-rooms.php?edit=<?= e($room['room_id']) ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="admin-rooms.php"
                                        onsubmit="return confirm('Are you sure you want to delete this room?');"
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
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="text-center py-4">
                            No rooms found.
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
```
