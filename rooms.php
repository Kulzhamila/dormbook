<?php
// Әзірге бөлмелер массивте сақталады.
// 11-аптада бұл деректер MySQL-дегі rooms кестесінен алынады.
$rooms = [
    ["id" => 1, "dorm" => "№1 жатақхана", "number" => "101", "floor" => 1, "capacity" => 2, "free" => 1, "price" => 25000,
     "description" => "Екі орындық бөлме, жеке жуынатын бөлмесі бар"],
    ["id" => 2, "dorm" => "№1 жатақхана", "number" => "205", "floor" => 2, "capacity" => 3, "free" => 2, "price" => 20000,
     "description" => "Үш орындық бөлме, терезесі оңтүстікке қарайды"],
    ["id" => 3, "dorm" => "№2 жатақхана", "number" => "312", "floor" => 3, "capacity" => 4, "free" => 0, "price" => 18000,
     "description" => "Төрт орындық бөлме"],
    ["id" => 4, "dorm" => "№2 жатақхана", "number" => "118", "floor" => 1, "capacity" => 2, "free" => 2, "price" => 27000,
     "description" => "Екі орындық, жөндеуден кейін"],
    ["id" => 5, "dorm" => "№3 жатақхана", "number" => "407", "floor" => 4, "capacity" => 3, "free" => 1, "price" => 21000,
     "description" => "Үш орындық бөлме, оқу үстелдері бар"],
    ["id" => 6, "dorm" => "№3 жатақхана", "number" => "220", "floor" => 2, "capacity" => 4, "free" => 3, "price" => 17000,
     "description" => "Төрт орындық бөлме"]
];

$message = "";
$messageType = "error";

// Күннің дұрыс форматта (ЖЖЖЖ-АА-КК) екенін тексеру
function isValidDate($date) {
    $d = DateTime::createFromFormat("Y-m-d", $date);
    return $d && $d->format("Y-m-d") === $date;
}

// ---------- 1. Брондау формасын серверде өңдеу ($_POST) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $roomId = (int) ($_POST["room_id"] ?? 0);
    $name = trim($_POST["client_name"] ?? "");
    $phone = trim($_POST["client_phone"] ?? "");
    $startDate = $_POST["start_date"] ?? "";
    $endDate = $_POST["end_date"] ?? "";
    $today = date("Y-m-d");

    // Таңдалған бөлмені массивтен табу
    $selectedRoom = null;
    foreach ($rooms as $r) {
        if ($r["id"] === $roomId) {
            $selectedRoom = $r;
        }
    }

    if ($name === "" || $phone === "" || $startDate === "" || $endDate === "") {
        $message = "Барлық өрісті толтырыңыз.";
    } elseif ($selectedRoom === null) {
        $message = "Бөлме табылмады.";
    } elseif ($selectedRoom["free"] <= 0) {
        $message = "Бұл бөлмеде бос орын жоқ.";
    } elseif (!preg_match('/^\+?7[0-9]{10}$/', $phone)) {
        $message = "Телефон нөмірін +77001234567 форматында енгізіңіз.";
    } elseif (!isValidDate($startDate) || !isValidDate($endDate)) {
        $message = "Күнді дұрыс форматта енгізіңіз.";
    } elseif ($startDate < $today) {
        $message = "Кіру күні бүгінгі күннен ерте болмауы керек.";
    } elseif ($endDate <= $startDate) {
        $message = "Шығу күні кіру күнінен кейін болуы керек.";
    } else {
        $messageType = "success";
        $message = "Құрметті " . $name . ", " . $selectedRoom["dorm"] . " №" . $selectedRoom["number"]
                 . " бөлмесіне өтініміңіз қабылданды! Жақын арада " . $phone
                 . " нөмірі арқылы хабарласамыз.";
    }
}

// ---------- 2. Іздеу және сүзгілеу ($_GET) ----------
$search = trim($_GET["search"] ?? "");
$type = $_GET["type"] ?? "";
$freeOnly = isset($_GET["free"]);

$filteredRooms = [];

foreach ($rooms as $room) {
    $text = $room["dorm"] . " " . $room["number"];

    $matchesText = $search === "" || mb_stripos($text, $search, 0, "UTF-8") !== false;
    $matchesType = $type === "" || (string) $room["capacity"] === $type;
    $matchesFree = !$freeOnly || $room["free"] > 0;

    if ($matchesText && $matchesType && $matchesFree) {
        $filteredRooms[] = $room;
    }
}

$today = date("Y-m-d");

$pageTitle = "Бөлмелер | DormBook";
include "header.php";
?>

<section class="page-title">
    <p class="subtitle">ҚОЛЖЕТІМДІ БӨЛМЕЛЕР</p>
    <h1>Жатақхана бөлмелері</h1>
    <p>Өзіңізге ыңғайлы бөлмені тауып, брондау өтінімін жіберіңіз.</p>
</section>

<section class="search-section">
    <form method="GET" action="rooms.php" class="search-box">
        <input type="text" name="search"
               placeholder="Жатақхана немесе бөлме нөмірі (мыс.: №2, 205)..."
               value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>">

        <select name="type">
            <option value="">Барлық бөлме түрі</option>
            <?php foreach (["2", "3", "4"] as $t): ?>
                <option value="<?= $t ?>" <?= $type === $t ? "selected" : "" ?>>
                    <?= $t ?> орындық
                </option>
            <?php endforeach; ?>
        </select>

        <label class="checkbox">
            <input type="checkbox" name="free" value="1" <?= $freeOnly ? "checked" : "" ?>>
            Тек бос орны барлар
        </label>

        <button type="submit">Іздеу</button>

        <?php if ($search !== "" || $type !== "" || $freeOnly): ?>
            <a href="rooms.php" class="reset-link">Тазалау</a>
        <?php endif; ?>
    </form>
</section>

<section class="rooms-section">

    <?php if ($message !== ""): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <p class="result-count">Табылған бөлмелер: <b><?= count($filteredRooms) ?></b></p>

    <div class="rooms-grid">
        <?php foreach ($filteredRooms as $room): ?>
            <article class="room-card">
                <div class="room-top">
                    <span class="room-number">№<?= htmlspecialchars($room["number"]) ?></span>
                    <span class="room-type"><?= $room["capacity"] ?> орындық</span>
                </div>

                <div class="room-info">
                    <h3><?= htmlspecialchars($room["dorm"]) ?></h3>

                    <p class="room-meta"><?= $room["floor"] ?>-қабат</p>
                    <p class="room-desc"><?= htmlspecialchars($room["description"]) ?></p>

                    <?php if ($room["free"] > 0): ?>
                        <p class="status free">Бос орын: <?= $room["free"] ?></p>
                    <?php else: ?>
                        <p class="status busy">Бос орын жоқ</p>
                    <?php endif; ?>

                    <p class="price">
                        <?= number_format($room["price"], 0, ".", " ") ?> ₸ / ай
                    </p>

                    <?php if ($room["free"] <= 0): ?>
                        <button class="book-btn" disabled>Брондау мүмкін емес</button>
                    <?php else: ?>
                        <form method="POST" action="rooms.php" class="booking-form">
                            <input type="hidden" name="room_id" value="<?= $room["id"] ?>">

                            <label class="field">
                                Аты-жөніңіз
                                <input type="text" name="client_name" placeholder="Атыңыз" required>
                            </label>

                            <label class="field">
                                Телефон
                                <input type="tel" name="client_phone" placeholder="+77001234567" required>
                            </label>

                            <div class="date-row">
                                <label>
                                    Кіру күні
                                    <input type="date" name="start_date" min="<?= $today ?>" required>
                                </label>
                                <label>
                                    Шығу күні
                                    <input type="date" name="end_date" min="<?= $today ?>" required>
                                </label>
                            </div>

                            <p class="form-error" hidden></p>
                            <button type="submit" class="book-btn">Брондау</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (count($filteredRooms) === 0): ?>
        <p class="empty-message">
            «<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>» бойынша бөлме табылмады.
        </p>
    <?php endif; ?>
</section>

<?php include "footer.php"; ?>
