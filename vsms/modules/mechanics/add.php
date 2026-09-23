
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Add Mechanic";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

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
            INSERT INTO mechanics
            (
                name,
                phone,
                specialization,
                status
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $phone,
            $specialization,
            $status
        ]);

        header("Location: index.php?msg=added");
        exit;
    }
}
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?= implode("<br>", $errors) ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:700px">

    <div class="card-header">
        <h3>
            <i class="fas fa-hard-hat"></i>
            Add Mechanic
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
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Specialization</label>
                    <input
                        type="text"
                        name="specialization"
                        class="form-control"
                        placeholder="Engine, Electrical, Body Repair..."
                        value="<?= htmlspecialchars($_POST['specialization'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">

                        <option value="Active"
                            <?= (($_POST['status'] ?? 'Active') === 'Active') ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="Inactive"
                            <?= (($_POST['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>
                            Inactive
                        </option>

                    </select>
                </div>

            </div>

            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Mechanic
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
