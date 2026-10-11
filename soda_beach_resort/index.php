<?php
require_once "config/db.php";
require_once "includes/auth.php";

$categories = $conn->query(
    "SELECT category_id, category_name, price, description
     FROM DAY_PASS_CATEGORY
     ORDER BY category_id"
);

$cottages = $conn->query(
    "SELECT cottage_id, cottage_number, capacity, rental_rate
     FROM COTTAGE
     WHERE status = 'Available'
     ORDER BY cottage_id"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soda Beach Resort</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">Soda Beach Resort</a>
    <div class="nav-links">
        <a href="#passes">Day Passes</a>
        <a href="#cottages">Cottages</a>

        <?php if (!empty($_SESSION["customer_id"])): ?>
            <a href="my-bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<main class="container">
    <section class="hero">
        <h1>Your Beach Escape Starts Here</h1>
        <p>
            Plan your visit, choose a day pass, and reserve a cottage
            at Soda Beach Resort.
        </p>
        <a class="btn" href="book.php">Book Your Visit</a>
    </section>

    <section id="passes">
        <h2>Day-Pass Categories</h2>
        <div class="grid">
            <?php while ($pass = $categories->fetch_assoc()): ?>
                <article class="card">
                    <h3><?= e($pass["category_name"]) ?></h3>
                    <p><?= e($pass["description"] ?? "") ?></p>
                    <p class="price">
                        ₱<?= number_format((float)$pass["price"], 2) ?>
                    </p>
                    <a class="btn" href="book.php">Book a Pass</a>
                </article>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="cottages">
        <h2>Available Cottages</h2>
        <div class="grid">
            <?php if ($cottages->num_rows === 0): ?>
                <p>No cottages are currently listed as available.</p>
            <?php else: ?>
                <?php while ($cottage = $cottages->fetch_assoc()): ?>
                    <article class="card">
                        <h3><?= e($cottage["cottage_number"]) ?></h3>
                        <p>
                            Capacity:
                            <?= (int)$cottage["capacity"] ?> guests
                        </p>
                        <p class="price">
                            ₱<?= number_format(
                                (float)$cottage["rental_rate"], 2
                            ) ?>
                        </p>
                        <a class="btn" href="book.php">Reserve Cottage</a>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer>
    Soda Beach Resort — Online Day-Pass E-Ticketing and Cottage Booking
</footer>
</body>
</html>