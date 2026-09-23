
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Mechanics";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

/* Get Mechanics */
$stmt = $pdo->query("
    SELECT
        m.*,
        COUNT(s.service_id) AS total_services
    FROM mechanics m
    LEFT JOIN services s
        ON m.mechanic_id = s.mechanic_id
    GROUP BY m.mechanic_id
    ORDER BY m.mechanic_id DESC
");

$mechanics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg === 'added'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    Mechanic added successfully.
</div>
<?php endif; ?>

<?php if ($msg === 'updated'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    Mechanic updated successfully.
</div>
<?php endif; ?>

<?php if ($msg === 'deleted'): ?>
<div class="alert alert-warning">
    <i class="fas fa-trash"></i>
    Mechanic deleted successfully.
</div>
<?php endif; ?>

<div class="card">

    <div class="card-header">
        <h3>
            <i class="fas fa-user-cog"></i>
            Mechanics
        </h3>

        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Mechanic
        </a>
    </div>

    <div class="table-responsive">

        <table class="table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Specialization</th>
                    <th>Services</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($mechanics)): ?>

                <tr>
                    <td colspan="7" style="text-align:center;">
                        No mechanics found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($mechanics as $m): ?>

                <tr>

                    <td><?= $m['mechanic_id'] ?></td>

                    <td>
                        <?= htmlspecialchars($m['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($m['phone']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($m['specialization']) ?>
                    </td>

                    <td>
                        <?= $m['total_services'] ?>
                    </td>

                    <td>

                        <?php if ($m['status'] === 'Active'): ?>

                            <span class="badge badge-success">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="badge badge-secondary">
                                Inactive
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <a
                            href="edit.php?id=<?= $m['mechanic_id'] ?>"
                            class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <a
                            href="delete.php?id=<?= $m['mechanic_id'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this mechanic?')">
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
