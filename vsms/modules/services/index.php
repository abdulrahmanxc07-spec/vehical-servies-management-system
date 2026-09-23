
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Services";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

$search = trim($_GET['search'] ?? '');

if ($search) {

    $stmt = $pdo->prepare("
        SELECT
            s.*,
            v.registration_no,
            v.make,
            v.model,
            m.name AS mechanic_name
        FROM services s
        JOIN vehicles v ON s.vehicle_id = v.vehicle_id
        LEFT JOIN mechanics m ON s.mechanic_id = m.mechanic_id
        WHERE
            s.service_type LIKE ?
            OR v.registration_no LIKE ?
            OR v.make LIKE ?
            OR v.model LIKE ?
        ORDER BY s.service_id DESC
    ");

    $stmt->execute([
        "%{$search}%",
        "%{$search}%",
        "%{$search}%",
        "%{$search}%"
    ]);

} else {

    $stmt = $pdo->query("
        SELECT
            s.*,
            v.registration_no,
            v.make,
            v.model,
            m.name AS mechanic_name
        FROM services s
        JOIN vehicles v ON s.vehicle_id = v.vehicle_id
        LEFT JOIN mechanics m ON s.mechanic_id = m.mechanic_id
        ORDER BY s.service_id DESC
    ");
}

$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg === 'added'): ?>
<div class="alert alert-success">
    Service created successfully.
</div>
<?php endif; ?>

<?php if ($msg === 'updated'): ?>
<div class="alert alert-success">
    Service updated successfully.
</div>
<?php endif; ?>

<?php if ($msg === 'deleted'): ?>
<div class="alert alert-warning">
    Service deleted successfully.
</div>
<?php endif; ?>

<div class="card">

    <div class="card-header">
        <h3>
            <i class="fas fa-wrench"></i>
            Service Records
        </h3>

        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            New Service
        </a>
    </div>

    <div class="card-body">

        <form method="GET" class="search-bar">

            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search services..."
                value="<?= htmlspecialchars($search) ?>">

            <button type="submit" class="btn btn-secondary">
                Search
            </button>

            <?php if ($search): ?>
                <a href="index.php" class="btn btn-secondary">
                    Clear
                </a>
            <?php endif; ?>

        </form>

    </div>

    <div class="table-responsive">

        <table class="table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehicle</th>
                    <th>Service Type</th>
                    <th>Mechanic</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($services)): ?>

                <tr>
                    <td colspan="8" style="text-align:center;">
                        No service records found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($services as $service): ?>

                <tr>

                    <td><?= $service['service_id'] ?></td>

                    <td>
                        <?= htmlspecialchars(
                            $service['registration_no']
                            .' - '.
                            $service['make']
                            .' '.
                            $service['model']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($service['service_type']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $service['mechanic_name'] ?? 'Not Assigned'
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($service['status']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($service['start_date']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($service['end_date']) ?>
                    </td>

                    <td>

                        <a
                            href="edit.php?id=<?= $service['service_id'] ?>"
                            class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <a
                            href="delete.php?id=<?= $service['service_id'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this service record?')">
                            <i class="fas fa-trash"></i>
                        </a>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
```
