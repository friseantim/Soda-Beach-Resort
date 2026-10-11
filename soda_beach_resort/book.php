<?php
require_once "config/db.php";
require_once "includes/auth.php";

require_customer();

$error = "";
$success = "";

$categories = $conn->query(
    "SELECT category_id, category_name, price
     FROM DAY_PASS_CATEGORY
     ORDER BY category_id"
)->fetch_all(MYSQLI_ASSOC);

$cottages = $conn->query(
    "SELECT cottage_id, cottage_number, capacity, rental_rate
     FROM COTTAGE
     WHERE status = 'Available'
     ORDER BY cottage_id"
)->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $categoryId = filter_input(
        INPUT_POST, "category_id", FILTER_VALIDATE_INT
    );
    $cottageInput = $_POST["cottage_id"] ?? "";
    $visitDate = $_POST["visit_date"] ?? "";

    $cottageId = null;

    if ($cottageInput !== "") {
        $cottageId = filter_var(
            $cottageInput, FILTER_VALIDATE_INT
        );
    }

    $date = DateTime::createFromFormat("!Y-m-d", $visitDate);
    $validDate = $date && $date->format("Y-m-d") === $visitDate;

    if (!$validDate || $visitDate < date("Y-m-d")) {
        $error = "Please choose today or a future visit date.";
    } elseif (!$categoryId && !$cottageId) {
        $error = "Select a day-pass category, a cottage, or both.";
    } elseif ($cottageInput !== "" && !$cottageId) {
        $error = "Invalid cottage selection.";
    } else {
        try {
            $conn->begin_transaction();

            $total = 0.00;

            if ($categoryId) {
                $stmt = $conn->prepare(
                    "SELECT price FROM DAY_PASS_CATEGORY
                     WHERE category_id = ?"
                );
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                $pass = $stmt->get_result()->fetch_assoc();

                if (!$pass) {
                    throw new RuntimeException("Invalid day-pass category.");
                }

                $total += (float)$pass["price"];
            } else {
                $categoryId = null;
            }

            if ($cottageId !== null) {
                $stmt = $conn->prepare(
                    "SELECT cottage_number, rental_rate
                     FROM COTTAGE
                     WHERE cottage_id = ?
                       AND status = 'Available'
                     FOR UPDATE"
                );
                $stmt->bind_param("i", $cottageId);
                $stmt->execute();
                $cottage = $stmt->get_result()->fetch_assoc();

                if (!$cottage) {
                    throw new RuntimeException(
                        "That cottage is not available."
                    );
                }

                $stmt = $conn->prepare(
                    "SELECT booking_id
                     FROM BOOKING
                     WHERE cottage_id = ?
                       AND visit_date = ?
                       AND booking_status IN ('Pending', 'Confirmed')
                     LIMIT 1"
                );
                $stmt->bind_param("is", $cottageId, $visitDate);
                $stmt->execute();

                if ($stmt->get_result()->num_rows > 0) {
                    throw new RuntimeException(
                        "That cottage already has a pending or confirmed booking for this date."
                    );
                }

                $total += (float)$cottage["rental_rate"];
            }

            $customerId = (int)$_SESSION["customer_id"];

            $stmt = $conn->prepare(
                "INSERT INTO BOOKING
                 (customer_id, cottage_id, category_id, visit_date,
                  total_amount, booking_status)
                 VALUES (?, ?, ?, ?, ?, 'Pending')"
            );

            $stmt->bind_param(
                "iiisd",
                $customerId,
                $cottageId,
                $categoryId,
                $visitDate,
                $total
            );

            $stmt->execute();
            $bookingId = $conn->insert_id;

            $conn->commit();

            $success = "Booking submitted successfully. Your booking number is #"
                . $bookingId
                . ". The current status is Pending.";
        } catch (Throwable $exception) {
            $conn->rollback();

            if ($exception instanceof RuntimeException) {
                $error = $exception->getMessage();
            } else {
                error_log($exception->getMessage());
                $error = "We could not save your booking. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Visit | Soda Beach Resort</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">Soda Beach Resort</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="my-bookings.php">My Bookings</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">
    <section class="form-card">
        <h1>Book Your Visit</h1>
        <p class="muted">
            Select a day pass, a cottage, or both.
        </p>

        <?php if ($error !== ""): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="notice"><?= e($success) ?></div>
            <a class="btn" href="my-bookings.php">View My Bookings</a>
        <?php else: ?>
            <form method="POST" data-validate>
                <label for="visit_date">Visit Date</label>
                <input id="visit_date" type="date" name="visit_date"
                       required value="<?= e($_POST["visit_date"] ?? "") ?>">

                <label for="category_id">Day-Pass Category</label>
                <select id="category_id" name="category_id">
                    <option value="">No day pass</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category["category_id"] ?>"
                            <?= (string)($_POST["category_id"] ?? "") ===
                                (string)$category["category_id"] ? "selected" : "" ?>>
                            <?= e($category["category_name"]) ?>
                            — ₱<?= number_format((float)$category["price"], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="cottage_id">Cottage</label>
                <select id="cottage_id" name="cottage_id">
                    <option value="">No cottage</option>
                    <?php foreach ($cottages as $cottage): ?>
                        <option value="<?= (int)$cottage["cottage_id"] ?>"
                            <?= (string)($_POST["cottage_id"] ?? "") ===
                                (string)$cottage["cottage_id"] ? "selected" : "" ?>>
                            <?= e($cottage["cottage_number"]) ?>
                            — <?= (int)$cottage["capacity"] ?> guests
                            — ₱<?= number_format((float)$cottage["rental_rate"], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <p class="muted">
                    The total uses the selected pass rate and cottage rental rate.
                    Prices are sample rates until you update them in the database.
                </p>

                <button class="btn btn-full" type="submit">
                    Submit Booking
                </button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>