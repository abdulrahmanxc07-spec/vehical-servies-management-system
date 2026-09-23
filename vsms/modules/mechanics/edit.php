
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Edit Mechanic";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM mechanics
    WHERE mechanic_id = ?
");
$stmt->execute([$id]);

$mechanic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mechanic) {
    header("Location: index.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name           = trim($_POST['name'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $status         = $_POST['status'] ?? 'Active';

    if (empty($name)) {
        $errors[] = "Mechanic name is required.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE mechanics
            SET
                name = ?,
                phone = ?,
                specialization = ?,
                status = ?
            WHERE mechanic_id = ?
        ");

        $stmt->execute([
            $name,
            $phone,
            $specialization,
            $status,
            $id
        ]);

        header("Location: index.php?msg=updated");
        exit;
    }
}

$data = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? $_POST
    : $mechanic;
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?= implode("<br>", $errors) ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:700px">

    <div class="card-header">
        <h3>
            <i class="fas fa-user-cog"></i>
            Edit Mechanic
        </h3>

        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="card-body">

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name *</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?= htmlspecialchars($data['name']) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($data['phone']) ?>">
                </div>

                <div class="form-group">
                    <label>Specialization</label>
                    <input
                        type="text"
                        name="specialization"
                        class="form-control"
                        value="<?= htmlspecialchars($data['specialization']) ?>">
                </div>

                <div class="form-group">
                    <label>Status</label>

                    <select name="status" class="form-control">

                        <option value="Active"
                            <?= ($data['status'] == 'Active') ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="Inactive"
                            <?= ($data['status'] == 'Inactive') ? 'selected' : '' ?>>
                            Inactive
                        </option>

                    </select>
                </div>

            </div>

            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Mechanic
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
```
