<?php
// Әр беттің жоғарғы бөлігі: <head>, логотип және мәзір.
// Қолдану: $pageTitle = "..."; include "header.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? "DormBook — Жатақхана бөлмелерін брондау";
$currentPage = basename($_SERVER["PHP_SELF"]);
$isLoggedIn = isset($_SESSION["user_id"]);

// Ағымдағы бетті мәзірде белгілеу
function activeClass($page, $currentPage) {
    return $page === $currentPage ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, "UTF-8") ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>

    <button class="burger" id="burger" aria-label="Мәзірді ашу">☰</button>

    <nav id="nav">
        <a href="index.php"<?= activeClass("index.php", $currentPage) ?>>Басты бет</a>
        <a href="rooms.php"<?= activeClass("rooms.php", $currentPage) ?>>Бөлмелер</a>

        <?php if ($isLoggedIn && ($_SESSION["user_role"] ?? "") === "admin"): ?>
            <a href="admin_rooms.php"<?= strpos($currentPage, "admin") === 0 ? ' class="active"' : '' ?>>⚙ Әкімші панелі</a>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <a href="profile.php"<?= activeClass("profile.php", $currentPage) ?>>
                👤 <?= htmlspecialchars($_SESSION["user_name"], ENT_QUOTES, "UTF-8") ?>
            </a>
            <a href="logout.php" class="main-btn small">Шығу</a>
        <?php else: ?>
            <a href="login.php"<?= activeClass("login.php", $currentPage) ?>>Кіру</a>
            <a href="register.php" class="main-btn small">Тіркелу</a>
        <?php endif; ?>
    </nav>
</header>

<main>
