<?php
require_once "auth.php";
requireAdmin();
require_once "db.php";

$message = "";
$messageType = "success";

// ---------- UPDATE: өтінімді мақұлдау немесе қабылдамау ($_POST) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bookingId = (int) ($_POST["booking_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    $stmt = $pdo->prepare("SELECT room_id FROM bookings WHERE id = ? AND status = 'pending'");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $messageType = "error";
        $message = "Өтінім табылмады немесе бұрын қаралған.";
    } elseif ($action === "approve") {
        // Мақұлдағанда бөлмедегі бос орын 1-ге азаяды
        $pdo->beginTransaction();

        $room = $pdo->prepare(
            "UPDATE rooms SET free_places = free_places - 1 WHERE id = ? AND free_places > 0"
        );
        $room->execute([$booking["room_id"]]);

        if ($room->rowCount() === 1) {
            $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?")
                ->execute([$bookingId]);
            $pdo->commit();
            $message = "Өтінім мақұлданды.";
        } else {
            $pdo->rollBack();
            $messageType = "error";
            $message = "Бөлмеде бос орын қалмаған.";
        }
    } elseif ($action === "reject") {
        $pdo->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?")
            ->execute([$bookingId]);
        $message = "Өтінім қабылданбады.";
    }
}

// ---------- READ: іздеу, сүзгі, сұрыптау, пагинация ($_GET) ----------
include "pagination.php";

$q = trim($_GET["q"] ?? "");
$status = $_GET["status"] ?? "";
$dateFrom = $_GET["date_from"] ?? "";
$dateTo = $_GET["date_to"] ?? "";
$sort = $_GET["sort"] ?? "newest";

// Күн форматын тексеру (ЖЖЖЖ-АА-КК), қате болса — елемеу
$isDate = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1;
if (!$isDate($dateFrom)) $dateFrom = "";
if (!$isDate($dateTo)) $dateTo = "";

$sortOptions = [
    "newest"     => ["Жаңалары бірінші",        "b.created_at DESC, b.id DESC"],
    "oldest"     => ["Ескілері бірінші",        "b.created_at ASC, b.id ASC"],
    "start_asc"  => ["Кіру күні (ерте → кеш)",  "b.start_date ASC, b.id"],
    "start_desc" => ["Кіру күні (кеш → ерте)",  "b.start_date DESC, b.id"]
];
if (!isset($sortOptions[$sort])) {
    $sort = "newest";
}

$where = " WHERE 1 = 1";
$params = [];

if ($q !== "") {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR r.room_number LIKE ?)";
    array_push($params, "%$q%", "%$q%", "%$q%");
}

if (in_array($status, ["pending", "approved", "rejected", "cancelled"], true)) {
    $where .= " AND b.status = ?";
    $params[] = $status;
}

// Кіру күні бойынша сүзгі (аралық)
if ($dateFrom !== "") {
    $where .= " AND b.start_date >= ?";
    $params[] = $dateFrom;
}
if ($dateTo !== "") {
    $where .= " AND b.start_date <= ?";
    $params[] = $dateTo;
}

$from = " FROM bookings b
          JOIN users u ON u.id = b.user_id
          JOIN rooms r ON r.id = b.room_id
          JOIN dormitories d ON d.id = r.dormitory_id";

$countStmt = $pdo->prepare("SELECT COUNT(*)" . $from . $where);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

// Пагинация: бір бетте 8 өтінім
$perPage = 8;
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min(max(1, (int) ($_GET["page"] ?? 1)), $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT b.*, u.full_name, u.email, u.phone,
            r.room_number, r.free_places, d.name AS dorm_name"
    . $from . $where
    . " ORDER BY " . $sortOptions[$sort][1]
    . " LIMIT " . $perPage . " OFFSET " . $offset
);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$filters = array_filter([
    "q"         => $q,
    "status"    => $status,
    "date_from" => $dateFrom,
    "date_to"   => $dateTo,
    "sort"      => $sort === "newest" ? "" : $sort
], fn($v) => $v !== "");

$statusNames = [
    "pending"   => "Күтуде",
    "approved"  => "Мақұлданды",
    "rejected"  => "Қабылданбады",
    "cancelled" => "Бас тартылды"
];

$pageTitle = "Әкімші: өтінімдер | DormBook";
include "header.php";
?>

<section class="page-title">
    <p class="subtitle">ӘКІМШІ ПАНЕЛІ</p>
    <h1>Брондау өтінімдері</h1>
    <p>Студенттердің өтінімдерін мақұлдау немесе қабылдамау.</p>
</section>

<section class="admin-section">
    <div class="admin-tabs">
        <a href="admin_rooms.php">Бөлмелер</a>
        <a href="admin_bookings.php" class="active">Өтінімдер</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="GET" action="admin_bookings.php" class="filter-box">
        <label class="wide-field">
            Іздеу
            <input type="text" name="q" placeholder="Студент аты, email немесе бөлме нөмірі"
                   value="<?= htmlspecialchars($q, ENT_QUOTES, "UTF-8") ?>">
        </label>

        <label>
            Мәртебе
            <select name="status">
                <option value="">Барлығы</option>
                <?php foreach ($statusNames as $key => $name): ?>
                    <option value="<?= $key ?>" <?= $status === $key ? "selected" : "" ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Кіру күні: бастап
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
        </label>

        <label>
            дейін
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
        </label>

        <label>
            Сұрыптау
            <select name="sort">
                <?php foreach ($sortOptions as $key => $opt): ?>
                    <option value="<?= $key ?>" <?= $sort === $key ? "selected" : "" ?>><?= $opt[0] ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="filter-buttons">
            <button type="submit">Сүзу</button>
            <?php if (count($filters) > 0): ?>
                <a href="admin_bookings.php" class="reset-link">Тазалау</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Өтінімдер: <?= $total ?></h2>
            <?php if ($totalPages > 1): ?>
                <span class="result-count"><?= $page ?>/<?= $totalPages ?>-бет</span>
            <?php endif; ?>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Бөлме</th>
                        <th>Мерзімі</th>
                        <th>Жіберілген</th>
                        <th>Мәртебе</th>
                        <th>Әрекет</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <b><?= htmlspecialchars($b["full_name"]) ?></b><br>
                                <small><?= htmlspecialchars($b["email"]) ?> · <?= htmlspecialchars($b["phone"]) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($b["dorm_name"]) ?>, №<?= htmlspecialchars($b["room_number"]) ?><br>
                                <small>Бос орын: <?= (int) $b["free_places"] ?></small>
                            </td>
                            <td>
                                <?= date("d.m.Y", strtotime($b["start_date"])) ?> —
                                <?= date("d.m.Y", strtotime($b["end_date"])) ?>
                            </td>
                            <td><?= date("d.m.Y H:i", strtotime($b["created_at"])) ?></td>
                            <td>
                                <span class="badge <?= $b["status"] ?>"><?= $statusNames[$b["status"]] ?></span>
                            </td>
                            <td class="actions">
                                <?php if ($b["status"] === "pending"): ?>
                                    <form method="POST" action="admin_bookings.php<?= htmlspecialchars(pageUrl($page, $filters)) ?>">
                                        <input type="hidden" name="booking_id" value="<?= (int) $b["id"] ?>">
                                        <button type="submit" name="action" value="approve" class="approve-btn">Мақұлдау</button>
                                        <button type="submit" name="action" value="reject" class="cancel-btn">Қабылдамау</button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($bookings) === 0): ?>
            <p class="empty-message">Сүзгі бойынша өтінім табылмады.</p>
        <?php endif; ?>
    </div>

    <?php renderPagination($page, $totalPages, $filters); ?>
</section>

<?php include "footer.php"; ?>
