
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Customers";

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";

$search = trim($_GET['search'] ?? '');

if ($search) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM customers
        WHERE name LIKE ?
           OR phone LIKE ?
           OR email LIKE ?
        ORDER BY customer_id DESC
    ");

    $stmt->execute([
        "%{$search}%",
        "%{$search}%",
        "%{$search}%"
    ]);
} else {
    $stmt = $pdo->query("
        SELECT *
        FROM customers
        ORDER BY customer_id DESC
    ");
}

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg === 'added'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    Customer added successfully.
</div>
<?php endif; ?>

<?php if ($msg === 'updated'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    Customer updated successfully.
</div>
<?php endif; ?>

<?php if ($msg === 'deleted'): ?>
<div class="alert alert-warning">
    <i class="fas fa-trash"></i>
    Customer deleted successfully.
</div>
<?php endif; ?>

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>
            <i class="fas fa-users"></i>
            All Customers
        </h3>

        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Customer
        </a>
    </div>

    <div class="card-body">

        <form method="GET" class="search-bar mb-3">

            <div class="d-flex gap-2">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by name, phone or email..."
                    value="<?= htmlspecialchars($search) ?>">

                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i>
                    Search
                </button>

                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">
                        Clear
                    </a>
                <?php endif; ?>

            </div>

        </form>

    </div>

    <div class="table-responsive">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($customers)): ?>

                <tr>
                    <td colspan="7" class="text-center">
                        No customers found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($customers as $c): ?>

                <tr>

                    <td><?= $c['customer_id'] ?></td>

                    <td>
                        <?= htmlspecialchars($c['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($c['phone']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($c['email']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($c['address']) ?>
                    </td>

                    <td>
                        <?= !empty($c['created_at'])
                            ? date('d M Y', strtotime($c['created_at']))
                            : '-' ?>
                    </td>

                    <td>

                        <a
                            href="edit.php?id=<?= $c['customer_id'] ?>"
                            class="btn btn-warning btn-sm">

                            <i class="fas fa-edit"></i>
                        </a>

                        <a
                            href="delete.php?id=<?= $c['customer_id'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this customer?')">

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
