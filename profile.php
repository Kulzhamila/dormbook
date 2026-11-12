<?php
session_start();

// Бетті қорғау: жүйеге кірмеген пайдаланушы login.php бетіне жіберіледі
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$userId = $_SESSION["user_id"];
$message = "";

// Күтудегі өтінімнен бас тарту ($_POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_id"])) {
    $stmt = $pdo->prepare(
        "UPDATE bookings SET status = 'cancelled'
         WHERE id = ? AND user_id = ? AND status = 'pending'"
    );
    $stmt->execute([(int) $_POST["cancel_id"], $userId]);

    if ($stmt->rowCount() > 0) {
        $message = "Өтінімнен бас тартылды.";
    }
}

// Пайдаланушы туралы мәлімет
$stmt = $pdo->prepare(
    "SELECT full_name, email, student_id, phone, created_at FROM users WHERE id = ?"
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Пайдаланушы дерекқордан өшірілген болса — сессияны жабу
if (!$user) {
    header("Location: logout.php");
    exit;
}

// Пайдаланушының брондаулары
$stmt = $pdo->prepare(
    "SELECT b.id, b.start_date, b.end_date, b.status, b.created_at,
            r.room_number, r.price_per_month, d.name AS dorm_name
     FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     JOIN dormitories d ON d.id = r.dormitory_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC, b.id DESC"
);
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

// Мәртебе атаулары
$statusNames = [
    "pending"   => "Күтуде",
    "approved"  => "Мақұлданды",
    "rejected"  => "Қабылданбады",
    "cancelled" => "Бас тартылды"
];

$pageTitle = "Жеке кабинет | DormBook";
include "header.php";
?>

<section class="profile-section">
    <div class="profile-card">
        <div class="avatar"><?= htmlspecialchars(mb_substr($user["full_name"], 0, 1)) ?></div>

        <h1>Қош келдіңіз, <?= htmlspecialchars($user["full_name"]) ?>!</h1>
        <p class="intro">Бұл — жүйеге кірген пайдаланушыға ғана көрінетін жеке кабинет.</p>

        <ul class="profile-info">
            <li><span>Email:</span> <?= htmlspecialchars($user["email"]) ?></li>
            <li><span>Студенттік ID:</span> <?= htmlspecialchars($user["student_id"]) ?></li>
            <li><span>Телефон:</span> <?= htmlspecialchars($user["phone"]) ?></li>
            <li><span>Тіркелген күні:</span> <?= date("d.m.Y", strtotime($user["created_at"])) ?></li>
        </ul>

        <a href="logout.php" class="logout-btn">Жүйеден шығу</a>
    </div>

    <div class="bookings-card">
        <h2>Менің брондауларым</h2>

        <?php if ($message !== ""): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (count($bookings) === 0): ?>
            <p class="empty-message">
                Сізде әзірге брондау жоқ.<br>
                <a href="rooms.php">Бөлме таңдау →</a>
            </p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Жатақхана</th>
                            <th>Бөлме</th>
                            <th>Мерзімі</th>
                            <th>Бағасы</th>
                            <th>Мәртебе</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b["dorm_name"]) ?></td>
                                <td>№<?= htmlspecialchars($b["room_number"]) ?></td>
                                <td>
                                    <?= date("d.m.Y", strtotime($b["start_date"])) ?> —
                                    <?= date("d.m.Y", strtotime($b["end_date"])) ?>
                                </td>
                                <td><?= number_format($b["price_per_month"], 0, ".", " ") ?> ₸</td>
                                <td>
                                    <span class="badge <?= $b["status"] ?>">
                                        <?= $statusNames[$b["status"]] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b["status"] === "pending"): ?>
                                        <form method="POST" action="profile.php">
                                            <input type="hidden" name="cancel_id" value="<?= (int) $b["id"] ?>">
                                            <button type="submit" class="cancel-btn">Бас тарту</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include "footer.php"; ?>
