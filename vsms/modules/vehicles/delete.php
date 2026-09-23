
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/vsms/config/db.php';
requireLogin();

$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT make, model, registration_no
    FROM vehicles
    WHERE vehicle_id = ?
");
$stmt->execute([$id]);

$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        DELETE FROM vehicles
        WHERE vehicle_id = ?
    ");

    $stmt->execute([$id]);

    header('Location: index.php?msg=deleted');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/vsms/includes/header.php';
?>

<style>
.delete-card{
    max-width:600px;
    margin:40px auto;
    background:#1e293b;
    color:#fff;
    border-radius:20px;
    padding:30px;
    box-shadow:0 10px 25px rgba(0,0,0,.25);
}

.vehicle-info{
    background:#0f172a;
    padding:15px;
    border-radius:12px;
    margin:20px 0;
}

.btn-danger{
    border-radius:12px;
}

.btn-secondary{
    border-radius:12px;
}
</style>

<div class="delete-card">

    <h3>
        <i class="fas fa-trash text-danger"></i>
        Delete Vehicle
    </h3>

    <p class="mt-3">
        Are you sure you want to delete this vehicle?
    </p>

    <div class="vehicle-info">
        <strong>
            <?= htmlspecialchars($vehicle['make']) ?>
            <?= htmlspecialchars($vehicle['model']) ?>
        </strong>
        <br>
        Registration:
        <?= htmlspecialchars($vehicle['registration_no']) ?>
    </div>

    <form method="POST">

        <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i>
            Delete Vehicle
        </button>

        <a href="index.php" class="btn btn-secondary">
            Cancel
        </a>

    </form>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/vsms/includes/footer.php'; ?>
```

