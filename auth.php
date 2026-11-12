<?php
// Әкімші беттерін қорғау.
// Қолдану: require_once "auth.php"; requireAdmin();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdmin() {
    // Жүйеге кірмеген болса — кіру бетіне
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    // Әкімші емес болса — басты бетке
    if (($_SESSION["user_role"] ?? "") !== "admin") {
        header("Location: index.php");
        exit;
    }
}
