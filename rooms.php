<?php
session_start();
require_once "db.php";

$message = "";
$messageType = "error";

// Күннің дұрыс форматта (ЖЖЖЖ-АА-КК) екенін тексеру
function isValidDate($date) {
    $d = DateTime::createFromFormat("Y-m-d", $date);
    return $d && $d->format("Y-m-d") === $date;
}

// ---------- 1. Брондау формасын өңдеу ($_POST) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Брондау тек жүйеге кірген пайдаланушыға рұқсат етіледі
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php?need_login=1");
        exit;
    }

    $roomId = (int) ($_POST["room_id"] ?? 0);
    $startDate = $_POST["start_date"] ?? "";
    $endDate = $_POST["end_date"] ?? "";
    $today = date("Y-m-d");

    if ($roomId <= 0 || $startDate === "" || $endDate === "") {
        $message = "Барлық өрісті толтырыңыз.";
    } elseif (!isValidDate($startDate) || !isValidDate($endDate)) {
        $message = "Күнді дұрыс форматта енгізіңіз.";
    } elseif ($startDate < $today) {
        $message = "Басталу күні бүгінгі күннен ерте болмауы керек.";
    } elseif ($endDate <= $startDate) {
        $message = "Аяқталу күні басталу күнінен кейін болуы керек.";
    } else {
        $stmt = $pdo->prepare("SELECT room_number, free_places FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch();

        if (!$room) {
            $message = "Бөлме табылмады.";
        } elseif ($room["free_places"] <= 0) {
            $message = "Бұл бөлмеде бос орын жоқ.";
        } else {
            // Бір бөлмеге екі рет өтінім жібермеу үшін тексеру
            $check = $pdo->prepare(
                "SELECT COUNT(*) FROM bookings
                 WHERE user_id = ? AND room_id = ? AND status IN ('pending', 'approved')"
            );
            $check->execute([$_SESSION["user_id"], $roomId]);

            if ($check->fetchColumn() > 0) {
                $message = "Сіз бұл бөлмеге өтінім жіберіп қойғансыз.";
            } else {
                $insert = $pdo->prepare(
                    "INSERT INTO bookings (user_id, room_id, start_date, end_date)
                     VALUES (?, ?, ?, ?)"
                );
                $insert->execute([$_SESSION["user_id"], $roomId, $startDate, $endDate]);

                $messageType = "success";
                $message = "№" . $room["room_number"] . " бөлмесіне өтінім жіберілді! "
                         . "Мәртебесін жеке кабинеттен көре аласыз.";
            }
        }
    }
}

// ---------- 2. Іздеу және сүзгілеу ($_GET) ----------
$search = trim($_GET["search"] ?? "");
$type = $_GET["type"] ?? "";
$freeOnly = isset($_GET["free"]);

$sql = "SELECT r.*, d.name AS dorm_name
        FROM rooms r
        JOIN dormitories d ON d.id = r.dormitory_id
        WHERE 1 = 1";
$params = [];

if ($search !== "") {
    $sql .= " AND (d.name LIKE ? OR r.room_number LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

if (in_array($type, ["2", "3", "4"], true)) {
    $sql .= " AND r.capacity = ?";
    $params[] = (int) $type;
}

if ($freeOnly) {
    $sql .= " AND r.free_places > 0";
}

$sql .= " ORDER BY d.name, r.room_number";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$isLoggedIn = isset($_SESSION["user_id"]);
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
            <?php if ($messageType === "success"): ?>
                <a href="profile.php">Кабинетке өту →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p class="result-count">Табылған бөлмелер: <b><?= count($rooms) ?></b></p>

    <div class="rooms-grid">
        <?php foreach ($rooms as $room): ?>
            <article class="room-card">
                <div class="room-top">
                    <span class="room-number">№<?= htmlspecialchars($room["room_number"]) ?></span>
                    <span class="room-type"><?= (int) $room["capacity"] ?> орындық</span>
                </div>

                <div class="room-info">
                    <h3><?= htmlspecialchars($room["dorm_name"]) ?></h3>

                    <p class="room-meta"><?= (int) $room["floor"] ?>-қабат</p>
                    <p class="room-desc"><?= htmlspecialchars($room["description"] ?? "") ?></p>

                    <?php if ($room["free_places"] > 0): ?>
                        <p class="status free">Бос орын: <?= (int) $room["free_places"] ?></p>
                    <?php else: ?>
                        <p class="status busy">Бос орын жоқ</p>
                    <?php endif; ?>

                    <p class="price">
                        <?= number_format($room["price_per_month"], 0, ".", " ") ?> ₸ / ай
                    </p>

                    <?php if ($room["free_places"] <= 0): ?>
                        <button class="book-btn" disabled>Брондау мүмкін емес</button>

                    <?php elseif (!$isLoggedIn): ?>
                        <a href="login.php?need_login=1" class="book-btn">Брондау үшін кіріңіз</a>

                    <?php else: ?>
                        <form method="POST" action="rooms.php" class="booking-form">
                            <input type="hidden" name="room_id" value="<?= (int) $room["id"] ?>">

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

    <?php if (count($rooms) === 0): ?>
        <p class="empty-message">Іздеу бойынша бөлме табылмады.</p>
    <?php endif; ?>
</section>

<?php include "footer.php"; ?>
