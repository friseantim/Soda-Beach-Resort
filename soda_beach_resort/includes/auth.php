<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function require_customer(): void
{
    if (empty($_SESSION["customer_id"])) {
        header("Location: login.php");
        exit;
    }
}