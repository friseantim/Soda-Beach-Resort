<?php
require_once "config/db.php";
require_once "includes/auth.php";

require_customer();

$customerId = (int)$_SESSION["customer_id"];

$stmt = $conn->prepare(
    "SELECT
        b.booking_id,
        b.booking_date,
        b.visit_date,
        b.total_amount,
        b.booking_status,
        d.category_name,
        c.cottage_number
     FROM BOOKING b
     LEFT JOIN DAY_PASS_CATEGORY d
        ON b.category_id = d.category_id
     LEFT JOIN COTTAGE c
        ON b.cottage_id = c.cottage_id
     WHERE b.customer_id = ?
     ORDER BY b.booking_id DESC"
);

$stmt->bind_param("i", $customerId);
$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Soda Beach Resort</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">Soda Beach Resort</a>
    <div class="nav-links">
        <a href="book.php">New Booking</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">
    <h1>My Bookings</h1>
    <p class="muted">
        Welcome, <?= e($_SESSION["customer_name"] ?? "Customer") ?>.
    </p>

    <section class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Visit Date</th>
                        <th>Day Pass</th>
                        <th>Cottage</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($bookings->num_rows === 0): ?>
                    <tr>
                        <td colspan="6">You have no bookings yet.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= (int)$booking["booking_id"] ?></td>
                            <td><?= e($booking["visit_date"]) ?></td>
                            <td><?= e($booking["category_name"] ?? "None") ?></td>
                            <td><?= e($booking["cottage_number"] ?? "None") ?></td>
                            <td>
                                ₱<?= number_format(
                                    (float)$booking["total_amount"], 2
                                ) ?>
                            </td>
                            <td>
                                <span class="badge">
                                    <?= e($booking["booking_status"]) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>