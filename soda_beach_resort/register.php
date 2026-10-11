<?php
require_once "config/db.php";
require_once "includes/auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone_number"] ?? "");
    $password = $_POST["password"] ?? "";

    if (
        $firstName === "" ||
        $lastName === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $error = "Enter valid details. Your password must have at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare(
                "INSERT INTO CUSTOMER
                 (first_name, last_name, email, phone_number, password_hash)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $firstName,
                $lastName,
                $email,
                $phone,
                $hash
            );

            $stmt->execute();

            header("Location: login.php?registered=1");
            exit;
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() === 1062) {
                $error = "That email address is already registered.";
            } else {
                error_log($exception->getMessage());
                $error = "Registration failed. Please try again.";
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
    <title>Register | Soda Beach Resort</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">Soda Beach Resort</a>
    <a href="login.php">Already have an account? Login</a>
</nav>

<main class="container">
    <section class="form-card">
        <h1>Create Account</h1>
        <p class="muted">Register to book day passes and cottages.</p>

        <?php if ($error !== ""): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <label for="first_name">First Name</label>
            <input id="first_name" name="first_name" maxlength="50"
                   required value="<?= e($_POST["first_name"] ?? "") ?>">

            <label for="last_name">Last Name</label>
            <input id="last_name" name="last_name" maxlength="50"
                   required value="<?= e($_POST["last_name"] ?? "") ?>">

            <label for="email">Email</label>
            <input id="email" type="email" name="email"
                   maxlength="100" required
                   value="<?= e($_POST["email"] ?? "") ?>">

            <label for="phone_number">Phone Number</label>
            <input id="phone_number" name="phone_number"
                   maxlength="20"
                   value="<?= e($_POST["phone_number"] ?? "") ?>">

            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   minlength="8" required autocomplete="new-password">

            <button class="btn btn-full" type="submit">
                Register
            </button>
        </form>
    </section>
</main>
</body>
</html>