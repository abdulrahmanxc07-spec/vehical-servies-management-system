<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();
$pdo = getDB();
// modules/inventory/edit.php
$pageTitle = 'Edit Part';



$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM spare_parts WHERE part_id = ?');
$stmt->execute([$id]);
$part = $stmt->fetch();
if (!$part) { header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part_name       = trim($_POST['part_name'] ?? '');
    $part_number     = trim($_POST['part_number'] ?? '');
    $quantity        = (int)($_POST['quantity'] ?? 0);
    $unit_price      = (float)($_POST['unit_price'] ?? 0);
    $low_stock_alert = (int)($_POST['low_stock_alert'] ?? 5);

    if (!$part_name) $errors[] = 'Part name is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE spare_parts SET part_name=?, part_number=?, quantity=?, unit_price=?, low_stock_alert=? WHERE part_id=?');
        $stmt->execute([$part_name, $part_number, $quantity, $unit_price, $low_stock_alert, $id]);
        header('Location: index.php?msg=updated'); exit;
    }
}
$data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $part;
?>

<?php if ($errors): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?></div><?php endif; ?>

<div class="card" style="max-width:700px">
  <div class="card-header">
    <h3><i class="fas fa-edit" style="color:var(--warning)"></i> Edit Spare Part</h3>
    <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
  <div class="card-body">
    <form method="POST">
      <div class="form-grid">
        <div class="form-group">
          <label>Part Name *</label>
          <input type="text" name="part_name" class="form-control" value="<?= htmlspecialchars($data['part_name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Part Number</label>
          <input type="text" name="part_number" class="form-control" value="<?= htmlspecialchars($data['part_number']) ?>">
        </div>
        <div class="form-group">
          <label>Quantity</label>
          <input type="number" name="quantity" class="form-control" min="0" value="<?= htmlspecialchars($data['quantity']) ?>">
        </div>
        <div class="form-group">
          <label>Unit Price (Rs.)</label>
          <input type="number" name="unit_price" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($data['unit_price']) ?>">
        </div>
        <div class="form-group">
          <label>Low Stock Alert</label>
          <input type="number" name="low_stock_alert" class="form-control" min="1" value="<?= htmlspecialchars($data['low_stock_alert']) ?>">
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Part</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
