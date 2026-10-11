<?php
require_once "config/db.php";
require_once "includes/auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT customer_id, first_name, password_hash
         FROM CUSTOMER
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $customer = $stmt->get_result()->fetch_assoc();

    if (
        $customer &&
        password_verify($password, $customer["password_hash"])
    ) {
        session_regenerate_id(true);

        $_SESSION["customer_id"] = (int)$customer["customer_id"];
        $_SESSION["customer_name"] = $customer["first_name"];

        header("Location: my-bookings.php");
        exit;
    }

    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Soda Beach Resort</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">Soda Beach Resort</a>
    <a href="register.php">Create Account</a>
</nav>

<main class="container">
    <section class="form-card">
        <h1>Customer Login</h1>

        <?php if (isset($_GET["registered"])): ?>
            <div class="notice">
                Registration successful. You can now log in.
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <label for="email">Email</label>
            <input id="email" type="email" name="email"
                   maxlength="100" required autocomplete="email">

            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password">

            <button class="btn btn-full" type="submit">Login</button>
        </form>
    </section>
</main>
</body>
</html>