<?php
require_once "auth.php";
requireAdmin();
require_once "db.php";

// id берілсе — өңдеу (UPDATE), берілмесе — жаңа бөлме (CREATE)
$id = (int) ($_GET["id"] ?? 0);
$isEdit = $id > 0;

$dorms = $pdo->query("SELECT id, name, floors FROM dormitories ORDER BY name")->fetchAll();

// Форманың бастапқы мәндері
$room = [
    "dormitory_id"    => "",
    "room_number"     => "",
    "floor"           => 1,
    "capacity"        => 2,
    "free_places"     => 2,
    "price_per_month" => "",
    "description"     => ""
];

// Өңдеу кезінде бөлмені дерекқордан алу (READ)
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();

    if (!$found) {
        header("Location: admin_rooms.php");
        exit;
    }
    $room = $found;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $room = [
        "dormitory_id"    => (int) ($_POST["dormitory_id"] ?? 0),
        "room_number"     => trim($_POST["room_number"] ?? ""),
        "floor"           => (int) ($_POST["floor"] ?? 0),
        "capacity"        => (int) ($_POST["capacity"] ?? 0),
        "free_places"     => (int) ($_POST["free_places"] ?? -1),
        "price_per_month" => (float) ($_POST["price_per_month"] ?? 0),
        "description"     => trim($_POST["description"] ?? "")
    ];

    // Таңдалған жатақхананы табу
    $dorm = null;
    foreach ($dorms as $d) {
        if ($d["id"] == $room["dormitory_id"]) {
            $dorm = $d;
        }
    }

    // Тексеру (валидация)
    if ($dorm === null) {
        $errors[] = "Жатақхананы таңдаңыз.";
    }
    if (!preg_match('/^[0-9]{1,4}[А-Яа-яA-Za-z]?$/u', $room["room_number"])) {
        $errors[] = "Бөлме нөмірі 1–4 саннан тұруы керек (мыс.: 105 немесе 105А).";
    }
    if ($dorm !== null && ($room["floor"] < 1 || $room["floor"] > $dorm["floors"])) {
        $errors[] = "Қабат 1-ден " . $dorm["floors"] . "-ге дейін болуы керек.";
    }
    if ($room["capacity"] < 1 || $room["capacity"] > 6) {
        $errors[] = "Орын саны 1-ден 6-ға дейін болуы керек.";
    }
    if ($room["free_places"] < 0 || $room["free_places"] > $room["capacity"]) {
        $errors[] = "Бос орын саны 0 мен жалпы орын саны аралығында болуы керек.";
    }
    if ($room["price_per_month"] <= 0) {
        $errors[] = "Бағасын дұрыс енгізіңіз.";
    }
    if (mb_strlen($room["description"]) > 500) {
        $errors[] = "Сипаттама 500 таңбадан аспауы керек.";
    }

    // Бір жатақханада бірдей нөмірлі екі бөлме болмауы керек
    if (count($errors) === 0) {
        $check = $pdo->prepare(
            "SELECT id FROM rooms WHERE dormitory_id = ? AND room_number = ? AND id <> ?"
        );
        $check->execute([$room["dormitory_id"], $room["room_number"], $id]);

        if ($check->fetch()) {
            $errors[] = "Бұл жатақханада №" . $room["room_number"] . " бөлмесі бұрыннан бар.";
        }
    }

    if (count($errors) === 0) {
        $values = [
            $room["dormitory_id"], $room["room_number"], $room["floor"], $room["capacity"],
            $room["free_places"], $room["price_per_month"], $room["description"]
        ];

        if ($isEdit) {
            // UPDATE
            $stmt = $pdo->prepare(
                "UPDATE rooms
                 SET dormitory_id = ?, room_number = ?, floor = ?, capacity = ?,
                     free_places = ?, price_per_month = ?, description = ?
                 WHERE id = ?"
            );
            $values[] = $id;
            $stmt->execute($values);
            header("Location: admin_rooms.php?msg=updated");
        } else {
            // CREATE
            $stmt = $pdo->prepare(
                "INSERT INTO rooms
                    (dormitory_id, room_number, floor, capacity, free_places, price_per_month, description)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute($values);
            header("Location: admin_rooms.php?msg=created");
        }
        exit;
    }
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$pageTitle = ($isEdit ? "Бөлмені өңдеу" : "Жаңа бөлме") . " | DormBook";
include "header.php";
?>

<div class="form-container wide">
    <a href="admin_rooms.php" class="back-link">← Бөлмелер тізіміне</a>

    <h1><?= $isEdit ? "№" . e($room["room_number"]) . " бөлмесін өңдеу" : "Жаңа бөлме қосу" ?></h1>
    <p class="intro">Барлық өрісті толтырыңыз.</p>

    <?php if (count($errors) > 0): ?>
        <div class="message error">
            <?php foreach ($errors as $err): ?>
                <div>• <?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin_room_form.php<?= $isEdit ? "?id=" . $id : "" ?>">
        <label for="dormitory_id">Жатақхана</label>
        <select id="dormitory_id" name="dormitory_id" required>
            <option value="">— Таңдаңыз —</option>
            <?php foreach ($dorms as $d): ?>
                <option value="<?= (int) $d["id"] ?>" <?= $d["id"] == $room["dormitory_id"] ? "selected" : "" ?>>
                    <?= e($d["name"]) ?> (<?= (int) $d["floors"] ?> қабат)
                </option>
            <?php endforeach; ?>
        </select>

        <div class="form-row">
            <div>
                <label for="room_number">Бөлме нөмірі</label>
                <input type="text" id="room_number" name="room_number" maxlength="5"
                       value="<?= e($room["room_number"]) ?>" placeholder="мыс.: 105" required>
            </div>
            <div>
                <label for="floor">Қабат</label>
                <input type="number" id="floor" name="floor" min="1" max="20"
                       value="<?= e($room["floor"]) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="capacity">Орын саны</label>
                <input type="number" id="capacity" name="capacity" min="1" max="6"
                       value="<?= e($room["capacity"]) ?>" required>
            </div>
            <div>
                <label for="free_places">Бос орын</label>
                <input type="number" id="free_places" name="free_places" min="0" max="6"
                       value="<?= e($room["free_places"]) ?>" required>
            </div>
        </div>

        <label for="price_per_month">Айлық бағасы (₸)</label>
        <input type="number" id="price_per_month" name="price_per_month" min="1000" step="500"
               value="<?= e($room["price_per_month"] === "" ? "" : (int) $room["price_per_month"]) ?>"
               placeholder="мыс.: 20000" required>

        <label for="description">Сипаттама</label>
        <textarea id="description" name="description" rows="3" maxlength="500"
                  placeholder="Бөлме туралы қысқаша"><?= e($room["description"]) ?></textarea>

        <button type="submit"><?= $isEdit ? "Сақтау" : "Қосу" ?></button>
    </form>
</div>

<?php include "footer.php"; ?>
