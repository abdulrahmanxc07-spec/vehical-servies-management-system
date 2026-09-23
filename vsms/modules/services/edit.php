<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Edit Service";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    header("Location: index.php");
    exit;
}

$vehicles = $pdo->query("
    SELECT vehicle_id, registration_no, make, model
    FROM vehicles
    ORDER BY registration_no
")->fetchAll(PDO::FETCH_ASSOC);

$mechanics = $pdo->query("
    SELECT mechanic_id, name
    FROM mechanics
    WHERE status='Active'
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle_id   = (int)($_POST['vehicle_id'] ?? 0);
    $mechanic_id  = !empty($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : null;
    $service_type = trim($_POST['service_type'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $status       = $_POST['status'] ?? 'Pending';
    $start_date   = $_POST['start_date'] ?? null;
    $end_date     = $_POST['end_date'] ?? null;

    if (!$vehicle_id) {
        $errors[] = "Please select a vehicle.";
    }

    if (empty($service_type)) {
        $errors[] = "Service type is required.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE services SET
                vehicle_id = ?,
                mechanic_id = ?,
                service_type = ?,
                description = ?,
                status = ?,
                start_date = ?,
                end_date = ?
            WHERE service_id = ?
        ");

        $stmt->execute([
            $vehicle_id,
            $mechanic_id,
            $service_type,
            $description,
            $status,
            $start_date ?: null,
            $end_date ?: null,
            $id
        ]);

        header("Location: index.php?msg=updated");
        exit;
    }
}

$data = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $service;
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $error): ?>
        <div><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Edit Service</h3>
    </div>

    <div class="card-body">

        <form method="POST">

            <div class="form-group">
                <label>Vehicle *</label>
                <select name="vehicle_id" class="form-control" required>
                    <option value="">Select Vehicle</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['vehicle_id'] ?>"
                            <?= ($data['vehicle_id'] == $v['vehicle_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(
                                $v['registration_no'].' - '.$v['make'].' '.$v['model']
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Mechanic</label>
                <select name="mechanic_id" class="form-control">
                    <option value="">Not Assigned</option>
                    <?php foreach ($mechanics as $m): ?>
                        <option value="<?= $m['mechanic_id'] ?>"
                            <?= ($data['mechanic_id'] == $m['mechanic_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Service Type *</label>
                <input
                    type="text"
                    name="service_type"
                    class="form-control"
                    value="<?= htmlspecialchars($data['service_type']) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">

                    <option value="Pending"
                        <?= ($data['status'] == 'Pending') ? 'selected' : '' ?>>
                        Pending
                    </option>

                    <option value="In Progress"
                        <?= ($data['status'] == 'In Progress') ? 'selected' : '' ?>>
                        In Progress
                    </option>

                    <option value="Completed"
                        <?= ($data['status'] == 'Completed') ? 'selected' : '' ?>>
                        Completed
                    </option>

                    <option value="Cancelled"
                        <?= ($data['status'] == 'Cancelled') ? 'selected' : '' ?>>
                        Cancelled
                    </option>

                </select>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input
                    type="date"
                    name="start_date"
                    class="form-control"
                    value="<?= htmlspecialchars($data['start_date']) ?>">
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input
                    type="date"
                    name="end_date"
                    class="form-control"
                    value="<?= htmlspecialchars($data['end_date']) ?>">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea
                    name="description"
                    class="form-control"
                    rows="4"><?= htmlspecialchars($data['description']) ?></textarea>
            </div>

            <br>

            <button type="submit" class="btn btn-primary">
                Update Service
            </button>

            <a href="index.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?> 
```
