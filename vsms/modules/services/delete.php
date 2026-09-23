<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vsms/config/db.php';
requireLogin();

$pdo = getDB();

$pageTitle = "Delete Service";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT service_id, service_type
        FROM services
        WHERE service_id = ?
    ");
    $stmt->execute([$id]);

    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        header("Location: index.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $delete = $pdo->prepare("
            DELETE FROM services
            WHERE service_id = ?
        ");

        $delete->execute([$id]);

        header("Location: index.php?msg=deleted");
        exit;
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vsms/includes/header.php';
?>

<div class="card" style="max-width:700px;margin:30px auto;">
    
    <div class="card-header">
        <h3>
            <i class="fas fa-trash text-danger"></i>
            Delete Service
        </h3>
    </div>

    <div class="card-body">

        <div class="alert alert-warning">
            <strong>Warning!</strong><br>
            This action cannot be undone.
        </div>

        <p>
            Are you sure you want to delete the following service?
        </p>

        <table class="table">
            <tr>
                <th>Service ID</th>
                <td><?= $service['service_id']; ?></td>
            </tr>
            <tr>
                <th>Service Type</th>
                <td><?= htmlspecialchars($service['service_type']); ?></td>
            </tr>
        </table>

        <form method="POST">

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                Confirm Delete
            </button>

            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancel
            </a>

        </form>

    </div>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/vsms/includes/footer.php'; ?>