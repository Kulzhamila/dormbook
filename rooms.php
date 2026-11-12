<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бөлмелер | DormBook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>

    <button class="burger" id="burger" aria-label="Мәзірді ашу">☰</button>

    <nav id="nav">
        <a href="index.php">Басты бет</a>
        <a href="rooms.php" class="active">Бөлмелер</a>
        <a href="login.php">Кіру</a>
        <a href="login.php" class="main-btn small">Тіркелу</a>
    </nav>
</header>

<main>
<section class="page-title">
    <p class="subtitle">ҚОЛЖЕТІМДІ БӨЛМЕЛЕР</p>
    <h1>Жатақхана бөлмелері</h1>
    <p>Өзіңізге ыңғайлы бөлмені тауып, брондау өтінімін жіберіңіз.</p>
</section>

<section class="search-section">
    <!-- Іздеу JavaScript арқылы бетте бірден жұмыс істейді (script.js) -->
    <div class="search-box">
        <input type="text" id="liveSearch"
               placeholder="Жатақхана немесе бөлме нөмірі (мыс.: №2, 205)...">

        <select id="typeFilter">
            <option value="">Барлық бөлме түрі</option>
            <option value="2">2 орындық</option>
            <option value="3">3 орындық</option>
            <option value="4">4 орындық</option>
        </select>

        <label class="checkbox">
            <input type="checkbox" id="freeOnly">
            Тек бос орны барлар
        </label>
    </div>
</section>

<section class="rooms-section">
    <div class="rooms-grid">
        <article class="room-card" data-search="№1 жатақхана 101" data-type="2" data-free="1">
            <div class="room-top">
                <span class="room-number">№101</span>
                <span class="room-type">2 орындық</span>
            </div>

            <div class="room-info">
                <h3>№1 жатақхана</h3>
                <p class="room-meta">1-қабат</p>
                <p class="room-desc">Екі орындық бөлме, жеке жуынатын бөлмесі бар</p>
                <p class="status free">Бос орын: 1</p>
                <p class="price">25 000 ₸ / ай</p>
                <a href="login.php" class="book-btn">Брондау</a>
            </div>
        </article>
        <article class="room-card" data-search="№1 жатақхана 205" data-type="3" data-free="2">
            <div class="room-top">
                <span class="room-number">№205</span>
                <span class="room-type">3 орындық</span>
            </div>

            <div class="room-info">
                <h3>№1 жатақхана</h3>
                <p class="room-meta">2-қабат</p>
                <p class="room-desc">Үш орындық бөлме, терезесі оңтүстікке қарайды</p>
                <p class="status free">Бос орын: 2</p>
                <p class="price">20 000 ₸ / ай</p>
                <a href="login.php" class="book-btn">Брондау</a>
            </div>
        </article>
        <article class="room-card" data-search="№2 жатақхана 312" data-type="4" data-free="0">
            <div class="room-top">
                <span class="room-number">№312</span>
                <span class="room-type">4 орындық</span>
            </div>

            <div class="room-info">
                <h3>№2 жатақхана</h3>
                <p class="room-meta">3-қабат</p>
                <p class="room-desc">Төрт орындық бөлме</p>
                <p class="status busy">Бос орын жоқ</p>
                <p class="price">18 000 ₸ / ай</p>
                <button class="book-btn" disabled>Брондау мүмкін емес</button>
            </div>
        </article>
        <article class="room-card" data-search="№2 жатақхана 118" data-type="2" data-free="2">
            <div class="room-top">
                <span class="room-number">№118</span>
                <span class="room-type">2 орындық</span>
            </div>

            <div class="room-info">
                <h3>№2 жатақхана</h3>
                <p class="room-meta">1-қабат</p>
                <p class="room-desc">Екі орындық, жөндеуден кейін</p>
                <p class="status free">Бос орын: 2</p>
                <p class="price">27 000 ₸ / ай</p>
                <a href="login.php" class="book-btn">Брондау</a>
            </div>
        </article>
        <article class="room-card" data-search="№3 жатақхана 407" data-type="3" data-free="1">
            <div class="room-top">
                <span class="room-number">№407</span>
                <span class="room-type">3 орындық</span>
            </div>

            <div class="room-info">
                <h3>№3 жатақхана</h3>
                <p class="room-meta">4-қабат</p>
                <p class="room-desc">Үш орындық бөлме, оқу үстелдері бар</p>
                <p class="status free">Бос орын: 1</p>
                <p class="price">21 000 ₸ / ай</p>
                <a href="login.php" class="book-btn">Брондау</a>
            </div>
        </article>
        <article class="room-card" data-search="№3 жатақхана 220" data-type="4" data-free="3">
            <div class="room-top">
                <span class="room-number">№220</span>
                <span class="room-type">4 орындық</span>
            </div>

            <div class="room-info">
                <h3>№3 жатақхана</h3>
                <p class="room-meta">2-қабат</p>
                <p class="room-desc">Төрт орындық бөлме</p>
                <p class="status free">Бос орын: 3</p>
                <p class="price">17 000 ₸ / ай</p>
                <a href="login.php" class="book-btn">Брондау</a>
            </div>
        </article>
    </div>

    <p id="emptyMessage" class="empty-message" hidden>Іздеу бойынша бөлме табылмады.</p>
</section>
</main>

<footer class="footer">
    <a href="index.php" class="logo">Dorm<span>Book</span></a>
    <p>© 2026 DormBook. Жатақхана бөлмелерін брондау жүйесі. Оқу жобасы.</p>
</footer>

<script src="script.js"></script>
</body>
</html>
