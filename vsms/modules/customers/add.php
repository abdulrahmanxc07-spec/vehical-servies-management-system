<?php


require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/config/db.php";
requireLogin();

$pageTitle = 'Add Customer';
$pdo = getDB();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $errors[] = 'Customer name is required.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO customers (name, phone, email, address)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $phone,
            $email,
            $address
        ]);

        header('Location: index.php?msg=added');
        exit;
    }
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/includes/header.php";
?>

<div class="card" style="max-width:800px; margin:0 auto;">
    
    <div class="card-header">
        <h3>
            <i class="fas fa-user-plus" style="color:var(--primary)"></i>
            Add New Customer
        </h3>

        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span style="color:red">*</span></label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea
                        name="address"
                        class="form-control"
                        rows="4"
                    ><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Customer
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/includes/footer.php"; ?>
