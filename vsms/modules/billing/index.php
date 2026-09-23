<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();
$pdo = getDB();
// modules/billing/index.php
$pageTitle = 'Billing & Invoices';



$invoices = $pdo->query("
  SELECT i.*, s.service_type, v.registration_no, v.make, v.model, c.name AS customer_name
  FROM invoices i
  JOIN services s ON i.service_id = s.service_id
  JOIN vehicles v ON s.vehicle_id = v.vehicle_id
  JOIN customers c ON v.customer_id = c.customer_id
  ORDER BY i.invoice_id DESC
")->fetchAll();

$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE payment_status='Paid'")->fetchColumn();
$totalUnpaid   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE payment_status='Unpaid'")->fetchColumn();
$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg === 'added'):   ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> Invoice created.</div><?php endif; ?>
<?php if ($msg === 'paid'):    ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> Marked as Paid.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert alert-warning"><i class="fas fa-trash"></i> Invoice deleted.</div><?php endif; ?>

<!-- Summary -->
<div class="grid-2" style="margin-bottom:24px">
  <div class="report-total">
    <div class="amount">Rs. <?= number_format($totalRevenue, 2) ?></div>
    <div class="label">Total Revenue Collected</div>
  </div>
  <div style="background:rgba(255,179,71,0.12);border:1px solid rgba(255,179,71,0.3);border-radius:12px;padding:24px;text-align:center">
    <div style="font-size:30px;font-weight:700;color:var(--warning)">Rs. <?= number_format($totalUnpaid, 2) ?></div>
    <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Total Unpaid / Pending</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-file-invoice-dollar" style="color:var(--success)"></i> All Invoices</h3>
    <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Invoice</a>
  </div>
  <div class="table-responsive">
    <table>
      <thead>
        <tr><th>#</th><th>Vehicle</th><th>Customer</th><th>Service</th><th>Labor</th><th>Parts</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($invoices)): ?>
          <tr><td colspan="10" style="text-align:center;padding:30px" class="text-muted">No invoices yet.</td></tr>
        <?php else: foreach ($invoices as $inv): ?>
          <tr>
            <td>#<?= $inv['invoice_id'] ?></td>
            <td>
              <div class="fw-bold"><?= htmlspecialchars($inv['registration_no']) ?></div>
              <div class="text-muted"><?= htmlspecialchars($inv['make'] . ' ' . $inv['model']) ?></div>
            </td>
            <td><?= htmlspecialchars($inv['customer_name']) ?></td>
            <td><?= htmlspecialchars($inv['service_type']) ?></td>
            <td>Rs. <?= number_format($inv['labor_cost'], 2) ?></td>
            <td>Rs. <?= number_format($inv['parts_cost'], 2) ?></td>
            <td class="fw-bold">Rs. <?= number_format($inv['total_amount'], 2) ?></td>
            <td>
              <?php $badge = match($inv['payment_status']) {
                'Paid'    => 'badge-success',
                'Partial' => 'badge-warning',
                default   => 'badge-danger',
              }; ?>
              <span class="badge <?= $badge ?>"><?= $inv['payment_status'] ?></span>
            </td>
            <td class="text-muted"><?= date('d M Y', strtotime($inv['created_at'])) ?></td>
            <td>
              <?php if ($inv['payment_status'] !== 'Paid'): ?>
                <a href="mark_paid.php?id=<?= $inv['invoice_id'] ?>"
                   class="btn btn-success btn-sm"
                   onclick="return confirm('Mark as Paid?')">
                  <i class="fas fa-check"></i> Paid
                </a>
              <?php endif; ?>
              <a href="delete.php?id=<?= $inv['invoice_id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete this invoice?')">
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
