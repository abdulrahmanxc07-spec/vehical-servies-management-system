
<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Edit Customer";

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    header("Location: index.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $errors[] = "Customer name is required.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE customers
            SET name = ?, phone = ?, email = ?, address = ?
            WHERE customer_id = ?
        ");

        $stmt->execute([
            $name,
            $phone,
            $email,
            $address,
            $id
        ]);

        header("Location: index.php?msg=updated");
        exit;
    }
}

$data = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? $_POST
    : $customer;

require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/header.php";
?>

<style>
.edit-card{
    background:#1e293b;
    border-radius:20px;
    padding:30px;
    color:#fff;
    box-shadow:0 10px 25px rgba(0,0,0,.25);
}

.edit-title{
    margin-bottom:25px;
    font-size:24px;
    font-weight:600;
}

.form-label{
    color:#cbd5e1;
    margin-bottom:6px;
}

.form-control{
    background:#0f172a;
    border:1px solid #334155;
    color:#fff;
    border-radius:12px;
    padding:12px;
}

.form-control:focus{
    background:#0f172a;
    color:#fff;
    border-color:#6366f1;
    box-shadow:0 0 10px rgba(99,102,241,.4);
}

.btn-primary{
    background:#6366f1;
    border:none;
    border-radius:12px;
}

.btn-secondary{
    border-radius:12px;
}

.alert-danger{
    border-radius:12px;
}
</style>

<div class="container mt-4">

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?= implode("<br>", $errors) ?>
        </div>
    <?php endif; ?>

    <div class="edit-card">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="edit-title">
                <i class="fas fa-user-edit"></i>
                Edit Customer
            </h2>

            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer Name *</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?= htmlspecialchars($data['name']) ?>"
                        required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($data['phone']) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($data['email']) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <input
                        type="text"
                        name="address"
                        class="form-control"
                        value="<?= htmlspecialchars($data['address']) ?>">
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Customer
                </button>

                <a href="index.php" class="btn btn-secondary ms-2">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
```

