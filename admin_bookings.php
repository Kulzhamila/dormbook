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

// ---------- READ: барлық өтінімдер ----------
$bookings = $pdo->query(
    "SELECT b.*, u.full_name, u.email, u.phone,
            r.room_number, r.free_places, d.name AS dorm_name
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN rooms r ON r.id = b.room_id
     JOIN dormitories d ON d.id = r.dormitory_id
     ORDER BY b.created_at DESC, b.id DESC"
)->fetchAll();

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

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Барлық өтінімдер (<?= count($bookings) ?>)</h2>
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
                                    <form method="POST" action="admin_bookings.php">
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
            <p class="empty-message">Әзірге өтінім жоқ.</p>
        <?php endif; ?>
    </div>
</section>

<?php include "footer.php"; ?>
