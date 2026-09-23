<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();
$pdo = getDB();
// modules/billing/add.php
$pageTitle = 'Create Invoice';



// Only show services that don't already have an invoice
$services = $pdo->query("
  SELECT s.service_id, s.service_type, v.registration_no, v.make, v.model, c.name AS customer
  FROM services s
  JOIN vehicles v  ON s.vehicle_id = v.vehicle_id
  JOIN customers c ON v.customer_id = c.customer_id
  WHERE s.service_id NOT IN (SELECT service_id FROM invoices)
  ORDER BY s.service_id DESC
")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id    = (int)($_POST['service_id']    ?? 0);
    $labor_cost    = (float)($_POST['labor_cost']  ?? 0);
    $parts_cost    = (float)($_POST['parts_cost']  ?? 0);
    $payment_status= $_POST['payment_status']      ?? 'Unpaid';
    $notes         = trim($_POST['notes']          ?? '');
    $total         = $labor_cost + $parts_cost;

    if (!$service_id) $errors[] = 'Please select a service.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO invoices (service_id, labor_cost, parts_cost, total_amount, payment_status, notes) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$service_id, $labor_cost, $parts_cost, $total, $payment_status, $notes]);
        if ($payment_status === 'Paid') {
            $pdo->prepare("UPDATE invoices SET payment_date=CURDATE() WHERE invoice_id=?")->execute([$pdo->lastInsertId()]);
        }
        header('Location: index.php?msg=added'); exit;
    }
}
?>

<?php if ($errors): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?></div><?php endif; ?>

<div class="card" style="max-width:700px">
  <div class="card-header">
    <h3><i class="fas fa-file-invoice-dollar" style="color:var(--success)"></i> Create Invoice</h3>
    <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
  <div class="card-body">
    <form method="POST" id="invoiceForm">
      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1">
          <label>Service *</label>
          <select name="service_id" class="form-control" required>
            <option value="">-- Select Completed Service --</option>
            <?php if (empty($services)): ?>
              <option disabled>All services already have invoices</option>
            <?php else: foreach ($services as $s): ?>
              <option value="<?= $s['service_id'] ?>" <?= (($_POST['service_id'] ?? '') == $s['service_id']) ? 'selected' : '' ?>>
                #<?= $s['service_id'] ?> — <?= htmlspecialchars($s['registration_no'] . ' ' . $s['make'] . ' ' . $s['model'] . ' — ' . $s['service_type'] . ' (' . $s['customer'] . ')') ?>
              </option>
            <?php endforeach; endif; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Labor Cost (Rs.)</label>
          <input type="number" name="labor_cost" id="labor" class="form-control" min="0" step="0.01"
                 value="<?= htmlspecialchars($_POST['labor_cost'] ?? '0') ?>" oninput="calcTotal()">
        </div>
        <div class="form-group">
          <label>Parts Cost (Rs.)</label>
          <input type="number" name="parts_cost" id="parts" class="form-control" min="0" step="0.01"
                 value="<?= htmlspecialchars($_POST['parts_cost'] ?? '0') ?>" oninput="calcTotal()">
        </div>
        <div class="form-group">
          <label>Total Amount (Rs.)</label>
          <input type="text" id="total_display" class="form-control" readonly
                 style="background:rgba(0,208,132,0.08);border-color:rgba(0,208,132,0.3);color:var(--success);font-weight:600"
                 value="0.00">
        </div>
        <div class="form-group">
          <label>Payment Status</label>
          <select name="payment_status" class="form-control">
            <option value="Unpaid"  <?= (($_POST['payment_status'] ?? 'Unpaid') === 'Unpaid')  ? 'selected' : '' ?>>Unpaid</option>
            <option value="Partial" <?= (($_POST['payment_status'] ?? '') === 'Partial') ? 'selected' : '' ?>>Partial</option>
            <option value="Paid"    <?= (($_POST['payment_status'] ?? '') === 'Paid')    ? 'selected' : '' ?>>Paid</option>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label>Notes</label>
          <textarea name="notes" class="form-control"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Invoice</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
function calcTotal() {
  const l = parseFloat(document.getElementById('labor').value) || 0;
  const p = parseFloat(document.getElementById('parts').value) || 0;
  document.getElementById('total_display').value = 'Rs. ' + (l + p).toFixed(2);
}
calcTotal();
</script>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
