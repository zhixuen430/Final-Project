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

// Delete hotel
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_hotel'])) {

    $hotel_id = filter_input(
        INPUT_POST,
        'hotel_id',
        FILTER_VALIDATE_INT
    );

    if ($hotel_id) {
        try {
            $statement = $pdo->prepare(
                "DELETE FROM hotels_page WHERE hotel_id = ?"
            );

            $statement->execute([$hotel_id]);

            if ($statement->rowCount() > 0) {
                $success = 'Hotel deleted successfully.';
            } else {
                $error = 'Hotel not found.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to delete this hotel. It may have related rooms or bookings.';
        }
    } else {
        $error = 'Invalid hotel ID.';
    }
}

// Add or update hotel
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['save_hotel'])) {

    $hotel_id = $_POST['hotel_id'] ?? '';
    $hotel_name = trim($_POST['hotel_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $image_url = trim($_POST['image_url'] ?? '');

    if (
        $hotel_name === '' ||
        $location === '' ||
        $description === '' ||
        $price === '' ||
        $rating === '' ||
        $image_url === ''
    ) {
        $error = 'Please fill in all fields.';
    } elseif (
        !is_numeric($price) ||
        !is_numeric($rating) ||
        (float)$price < 0 ||
        (float)$rating < 0 ||
        (float)$rating > 5
    ) {
        $error = 'Enter a valid price and a rating between 0 and 5.';
    } else {
        try {
            if ($hotel_id !== '') {
                // Update existing hotel
                $sql = "UPDATE hotels_page
                        SET hotel_name = ?,
                            location = ?,
                            description = ?,
                            price = ?,
                            rating = ?,
                            image_url = ?
                        WHERE hotel_id = ?";

                $statement = $pdo->prepare($sql);

                $statement->execute([
                    $hotel_name,
                    $location,
                    $description,
                    $price,
                    $rating,
                    $image_url,
                    $hotel_id
                ]);

                $success = 'Hotel updated successfully.';
            } else {
                // Add new hotel
                $sql = "INSERT INTO hotels_page
                        (hotel_name, location, description,
                         price, rating, image_url)
                        VALUES (?, ?, ?, ?, ?, ?)";

                $statement = $pdo->prepare($sql);

                $statement->execute([
                    $hotel_name,
                    $location,
                    $description,
                    $price,
                    $rating,
                    $image_url
                ]);

                $success = 'Hotel added successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to save hotel. Please check your database columns and values.';
        }
    }
}

// Get hotel to edit
$edit_hotel = null;

if (isset($_GET['edit'])) {
    $edit_id = filter_input(
        INPUT_GET,
        'edit',
        FILTER_VALIDATE_INT
    );

    if ($edit_id) {
        $statement = $pdo->prepare(
            "SELECT * FROM hotels_page WHERE hotel_id = ?"
        );

        $statement->execute([$edit_id]);
        $edit_hotel = $statement->fetch(PDO::FETCH_ASSOC);
    }
}

// Get all hotels
$statement = $pdo->query(
    "SELECT * FROM hotels_page ORDER BY hotel_id DESC"
);

$hotels = $statement->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Hotels</title>

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

        .hotel-form,
        .hotel-table {
            background: white;
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

        .hotel-image {
            width: 90px;
            height: 65px;
            object-fit: cover;
            border-radius: 6px;
        }

        .table th {
            background: #0f2747;
            color: white;
            white-space: nowrap;
        }

        .description-cell {
            min-width: 200px;
            max-width: 300px;
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
            <h2 class="page-title">Manage Hotels</h2>
            <p class="text-secondary mb-0">
                Add, edit and delete hotel information.
            </p>
        </div>

        <a href="admin_dashboard.php" class="btn btn-outline-secondary">
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


    <!-- Add / Edit Form -->
    <div class="hotel-form">

        <h4 class="mb-4">
            <?= $edit_hotel ? 'Edit Hotel' : 'Add New Hotel' ?>
        </h4>

        <form method="POST" action="admin-hotels.php">

            <input
                type="hidden"
                name="hotel_id"
                value="<?= e($edit_hotel['hotel_id'] ?? '') ?>"
            >

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Hotel Name</label>
                    <input
                        type="text"
                        name="hotel_name"
                        class="form-control"
                        value="<?= e($edit_hotel['hotel_name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control"
                        value="<?= e($edit_hotel['location'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Price per Night (RM)</label>
                    <input
                        type="number"
                        name="price"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?= e($edit_hotel['price'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Rating (0–5)</label>
                    <input
                        type="number"
                        name="rating"
                        class="form-control"
                        min="0"
                        max="5"
                        step="0.1"
                        value="<?= e($edit_hotel['rating'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Image URL</label>
                    <input
                        type="url"
                        name="image_url"
                        class="form-control"
                        value="<?= e($edit_hotel['image_url'] ?? '') ?>"
                        placeholder="https://example.com/hotel.jpg"
                        required
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        required
                    ><?= e($edit_hotel['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2">

                    <button
                        type="submit"
                        name="save_hotel"
                        value="1"
                        class="btn btn-primary"
                    >
                        <?= $edit_hotel ? 'Update Hotel' : 'Add Hotel' ?>
                    </button>

                    <?php if ($edit_hotel): ?>
                        <a
                            href="admin-hotels.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>
                    <?php endif; ?>

                </div>

            </div>

        </form>
    </div>


    <!-- Hotel List -->
    <div class="hotel-table">

        <h4 class="mb-3">Hotel List</h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Hotel Name</th>
                        <th>Location</th>
                        <th>Price / Night</th>
                        <th>Rating</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($hotels) > 0): ?>

                    <?php foreach ($hotels as $hotel): ?>

                        <tr>
                            <td><?= e($hotel['hotel_id']) ?></td>

                            <td>
                                <img
                                    src="<?= e($hotel['image_url']) ?>"
                                    alt="<?= e($hotel['hotel_name']) ?>"
                                    class="hotel-image"
                                >
                            </td>

                            <td><?= e($hotel['hotel_name']) ?></td>

                            <td><?= e($hotel['location']) ?></td>

                            <td>
                                RM <?= number_format(
                                    (float)$hotel['price'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                <?= e($hotel['rating']) ?> / 5
                            </td>

                            <td class="description-cell">
                                <?= e($hotel['description']) ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    <a
                                        href="admin-hotels.php?edit=<?= e($hotel['hotel_id']) ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="admin-hotels.php"
                                        onsubmit="return confirm('Are you sure you want to delete this hotel?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="hotel_id"
                                            value="<?= e($hotel['hotel_id']) ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_hotel"
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
                        <td colspan="8" class="text-center py-4">
                            No hotels found.
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
