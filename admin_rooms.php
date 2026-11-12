<?php
require_once "auth.php";
requireAdmin();
require_once "db.php";

$message = "";
$messageType = "success";

// ---------- DELETE: бөлмені жою ($_POST) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([(int) $_POST["delete_id"]]);

    if ($stmt->rowCount() > 0) {
        $message = "Бөлме өшірілді.";
    } else {
        $messageType = "error";
        $message = "Бөлме табылмады.";
    }
}

// Қосу/өңдеу бетінен қайтқанда хабарлама көрсету
$flash = [
    "created" => "Жаңа бөлме қосылды.",
    "updated" => "Бөлме мәліметтері сақталды."
];
if (isset($_GET["msg"], $flash[$_GET["msg"]])) {
    $message = $flash[$_GET["msg"]];
}

// ---------- READ: барлық бөлмелер ----------
$rooms = $pdo->query(
    "SELECT r.*, d.name AS dorm_name,
            (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.id) AS booking_count
     FROM rooms r
     JOIN dormitories d ON d.id = r.dormitory_id
     ORDER BY d.name, r.room_number"
)->fetchAll();

$pageTitle = "Әкімші: бөлмелер | DormBook";
include "header.php";
?>

<section class="page-title">
    <p class="subtitle">ӘКІМШІ ПАНЕЛІ</p>
    <h1>Бөлмелерді басқару</h1>
    <p>Бөлме қосу, өңдеу және жою.</p>
</section>

<section class="admin-section">
    <div class="admin-tabs">
        <a href="admin_rooms.php" class="active">Бөлмелер</a>
        <a href="admin_bookings.php">Өтінімдер</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Барлық бөлмелер (<?= count($rooms) ?>)</h2>
            <a href="admin_room_form.php" class="main-btn small">+ Жаңа бөлме</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Жатақхана</th>
                        <th>Бөлме</th>
                        <th>Қабат</th>
                        <th>Орын</th>
                        <th>Бос</th>
                        <th>Бағасы</th>
                        <th>Өтінім</th>
                        <th>Әрекет</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r["dorm_name"]) ?></td>
                            <td><b>№<?= htmlspecialchars($r["room_number"]) ?></b></td>
                            <td><?= (int) $r["floor"] ?></td>
                            <td><?= (int) $r["capacity"] ?></td>
                            <td><?= (int) $r["free_places"] ?></td>
                            <td><?= number_format($r["price_per_month"], 0, ".", " ") ?> ₸</td>
                            <td><?= (int) $r["booking_count"] ?></td>
                            <td class="actions">
                                <a href="admin_room_form.php?id=<?= (int) $r["id"] ?>" class="edit-btn">Өңдеу</a>

                                <form method="POST" action="admin_rooms.php" class="delete-form"
                                      data-confirm="№<?= htmlspecialchars($r["room_number"]) ?> бөлмесін және оның өтінімдерін өшіресіз бе?">
                                    <input type="hidden" name="delete_id" value="<?= (int) $r["id"] ?>">
                                    <button type="submit" class="cancel-btn">Жою</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($rooms) === 0): ?>
            <p class="empty-message">Әзірге бөлме жоқ.</p>
        <?php endif; ?>
    </div>
</section>

<?php include "footer.php"; ?>
