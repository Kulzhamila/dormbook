<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DormBook — Жатақхана бөлмелерін брондау</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>

    <button class="burger" id="burger" aria-label="Мәзірді ашу">☰</button>

    <nav id="nav">
        <a href="index.php" class="active">Басты бет</a>
        <a href="rooms.php">Бөлмелер</a>
        <a href="login.php">Кіру</a>
        <a href="login.php" class="main-btn small">Тіркелу</a>
    </nav>
</header>

<main>
<section class="hero">
    <div class="hero-content">
        <p class="subtitle">СТУДЕНТТЕРГЕ АРНАЛҒАН ЖҮЙЕ</p>
        <h1>Жатақханадан бөлмені<br>онлайн броньда</h1>
        <p class="description">
            Бос бөлмелерді қара, бағасы мен орын санын салыстыр
            және өтінішті кезексіз жібер.
        </p>

        <a href="rooms.php" class="main-btn light">Бөлмелерді қарау →</a>
    </div>
</section>

<section class="features">
    <div class="feature-box">
        <div class="feature-icon">🏠</div>
        <h3>3 жатақхана</h3>
        <p>Барлық жатақхананың бөлмелері бір жерде</p>
    </div>

    <div class="feature-box">
        <div class="feature-icon">⏱</div>
        <h3>Кезексіз</h3>
        <p>Өтінімді үйден шықпай онлайн жібер</p>
    </div>

    <div class="feature-box">
        <div class="feature-icon">✅</div>
        <h3>Ашық мәртебе</h3>
        <p>Өтінімнің қай кезеңде екенін кабинеттен бақыла</p>
    </div>
</section>

<section class="steps-section">
    <p class="subtitle">ҚАЛАЙ ЖҰМЫС ІСТЕЙДІ</p>
    <h2>Бөлме брондау — 3 қадам</h2>

    <div class="steps">
        <div class="step">
            <span class="step-number">1</span>
            <h3>Тіркел</h3>
            <p>Аты-жөніңді, студенттік ID мен телефоныңды енгіз.</p>
        </div>

        <div class="step">
            <span class="step-number">2</span>
            <h3>Бөлме таңда</h3>
            <p>Бөлме түрі мен бос орын бойынша сүзгіні қолдан.</p>
        </div>

        <div class="step">
            <span class="step-number">3</span>
            <h3>Өтінім жібер</h3>
            <p>Күндерді таңда — әкімші өтінімді қарап, жауап береді.</p>
        </div>
    </div>
</section>
</main>

<footer class="footer">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>
    <p>© 2026 DormBook. Жатақхана бөлмелерін брондау жүйесі. Оқу жобасы.</p>
</footer>

<script src="script.js"></script>
</body>
</html>
