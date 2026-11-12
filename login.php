<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Жүйеге кіру | DormBook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>

    <button class="burger" id="burger" aria-label="Мәзірді ашу">☰</button>

    <nav id="nav">
        <a href="index.php">Басты бет</a>
        <a href="rooms.php">Бөлмелер</a>
        <a href="login.php" class="active">Кіру</a>
        <a href="login.php" class="main-btn small">Тіркелу</a>
    </nav>
</header>

<main>
<div class="form-container">
    <h1>Жүйеге кіру</h1>

    <p class="intro">
        Бөлме брондау үшін email мен құпиясөзіңізді енгізіңіз.
    </p>

    <!-- Әзірге форма JavaScript арқылы тексеріледі (script.js) -->
    <form id="staticLoginForm">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="name@example.com" required>

        <label for="password">Құпиясөз</label>
        <input type="password" id="password" name="password" placeholder="Құпиясөзіңіз" required>

        <p class="form-error" hidden></p>
        <button type="submit">Кіру</button>
    </form>

    <p class="bottom">
        Аккаунтыңыз жоқ па?
        <a href="login.php">Тіркелу</a>
    </p>
</div>
</main>

<footer class="footer">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>
    <p>© 2026 DormBook. Жатақхана бөлмелерін брондау жүйесі. Оқу жобасы.</p>
</footer>

<script src="script.js"></script>
</body>
</html>
