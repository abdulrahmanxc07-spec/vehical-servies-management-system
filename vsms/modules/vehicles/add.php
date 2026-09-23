
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Add Vehicle";

require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/includes/header.php";

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

    if (empty($make)) {
        $errors[] = "Make is required.";
    }

    if (empty($model)) {
        $errors[] = "Model is required.";
    }

    if (empty($registration_no)) {
        $errors[] = "Registration number is required.";
    }

    if (empty($errors)) {

        $check = $pdo->prepare("
            SELECT COUNT(*)
            FROM vehicles
            WHERE registration_no = ?
        ");

        $check->execute([$registration_no]);

        if ($check->fetchColumn() > 0) {
            $errors[] = "Registration number already exists.";
        }
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO vehicles
            (
                customer_id,
                make,
                model,
                year,
                registration_no,
                color,
                mileage
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $customer_id,
            $make,
            $model,
            $year,
            $registration_no,
            $color,
            $mileage
        ]);

        header("Location: index.php?msg=added");
        exit;
    }
}
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?= implode('<br>', $errors) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Add Vehicle</h3>
        <a href="index.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="card-body">

        <form method="POST">

            <div class="form-group">
                <label>Customer *</label>

                <select name="customer_id" class="form-control" required>
                    <option value="">Select Customer</option>

                    <?php foreach ($customers as $customer): ?>
                        <option
                            value="<?= $customer['customer_id'] ?>"
                            <?= (($_POST['customer_id'] ?? '') == $customer['customer_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Make *</label>

                <input
                    type="text"
                    name="make"
                    class="form-control"
                    value="<?= htmlspecialchars($_POST['make'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Model *</label>

                <input
                    type="text"
                    name="model"
                    class="form-control"
                    value="<?= htmlspecialchars($_POST['model'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Year</label>

                <input
                    type="number"
                    name="year"
                    class="form-control"
                    value="<?= htmlspecialchars($_POST['year'] ?? date('Y')) ?>">
            </div>

            <div class="form-group">
                <label>Registration Number *</label>

                <input
                    type="text"
                    name="registration_no"
                    class="form-control"
                    value="<?= htmlspecialchars($_POST['registration_no'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Color</label>

                <input
                    type="text"
                    name="color"
                    class="form-control"
                    value="<?= htmlspecialchars($_POST['color'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Mileage (KM)</label>

                <input
                    type="number"
                    name="mileage"
                    class="form-control"
                    value="<?= htmlspecialchars($_POST['mileage'] ?? '0') ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                Save Vehicle
            </button>

            <a href="index.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/includes/footer.php"; ?>
```
