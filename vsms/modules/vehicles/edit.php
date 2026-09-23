
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Edit Vehicle";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM vehicles
    WHERE vehicle_id = ?
");
$stmt->execute([$id]);

$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    header("Location: index.php");
    exit;
}

$customers = $pdo->query("
    SELECT customer_id, name
    FROM customers
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_id     = (int)($_POST['customer_id'] ?? 0);
    $make            = trim($_POST['make'] ?? '');
    $model           = trim($_POST['model'] ?? '');
    $year            = (int)($_POST['year'] ?? date('Y'));
    $registration_no = trim($_POST['registration_no'] ?? '');
    $color           = trim($_POST['color'] ?? '');
    $mileage         = (int)($_POST['mileage'] ?? 0);

    if (!$customer_id) {
        $errors[] = "Please select a customer.";
    }

    if (!$make) {
        $errors[] = "Make is required.";
    }

    if (!$registration_no) {
        $errors[] = "Registration number is required.";
    }

    if (empty($errors)) {

        $check = $pdo->prepare("
            SELECT COUNT(*)
            FROM vehicles
            WHERE registration_no = ?
            AND vehicle_id != ?
        ");

        $check->execute([$registration_no, $id]);

        if ($check->fetchColumn() > 0) {
            $errors[] = "Registration number already exists.";
        }
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE vehicles
            SET
                customer_id = ?,
                make = ?,
                model = ?,
                year = ?,
                registration_no = ?,
                color = ?,
                mileage = ?
            WHERE vehicle_id = ?
        ");

        $stmt->execute([
            $customer_id,
            $make,
            $model,
            $year,
            $registration_no,
            $color,
            $mileage,
            $id
        ]);

        header("Location: index.php?msg=updated");
        exit;
    }
}

$data = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? $_POST
    : $vehicle;
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?= implode("<br>", $errors) ?>
</div>
<?php endif; ?>

<!-- Keep your existing form HTML here -->

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
```
