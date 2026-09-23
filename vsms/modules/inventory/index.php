<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();
$pdo = getDB();
// modules/inventory/index.php
$pageTitle = 'Inventory';



$parts = $pdo->query("SELECT * FROM spare_parts ORDER BY part_id DESC")->fetchAll();
$msg   = $_GET['msg'] ?? '';
?>

<?php if ($msg === 'added'):   ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> Part added.</div><?php endif; ?>
<?php if ($msg === 'updated'): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> Part updated.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert alert-warning"><i class="fas fa-trash"></i> Part deleted.</div><?php endif; ?>

<!-- Low stock warning -->
<?php $lowStock = array_filter($parts, fn($p) => $p['quantity'] <= $p['low_stock_alert']); ?>
<?php if (!empty($lowStock)): ?>
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong><?= count($lowStock) ?> item(s) are low in stock:</strong>
    <?= implode(', ', array_map(fn($p) => htmlspecialchars($p['part_name']), $lowStock)) ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-boxes" style="color:var(--info)"></i> Spare Parts Inventory</h3>
    <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Part</a>
  </div>
  <div class="table-responsive">
    <table>
      <thead>
        <tr><th>#</th><th>Part Name</th><th>Part No.</th><th>Quantity</th><th>Unit Price</th><th>Stock Value</th><th>Low Alert</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($parts)): ?>
          <tr><td colspan="8" style="text-align:center;padding:30px" class="text-muted">No parts in inventory.</td></tr>
        <?php else: foreach ($parts as $p): ?>
          <?php $isLow = $p['quantity'] <= $p['low_stock_alert']; ?>
          <tr>
            <td><?= $p['part_id'] ?></td>
            <td class="fw-bold <?= $isLow ? 'low-stock' : '' ?>">
              <?= htmlspecialchars($p['part_name']) ?>
              <?php if ($isLow): ?><span class="badge badge-danger" style="margin-left:6px">LOW</span><?php endif; ?>
            </td>
            <td class="text-muted"><?= htmlspecialchars($p['part_number']) ?></td>
            <td>
              <span class="badge <?= $isLow ? 'badge-danger' : 'badge-success' ?>">
                <?= $p['quantity'] ?> units
              </span>
            </td>
            <td>Rs. <?= number_format($p['unit_price'], 2) ?></td>
            <td>Rs. <?= number_format($p['quantity'] * $p['unit_price'], 2) ?></td>
            <td class="text-muted"><?= $p['low_stock_alert'] ?> units</td>
            <td>
              <a href="edit.php?id=<?= $p['part_id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
              <a href="delete.php?id=<?= $p['part_id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete this part?')">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
