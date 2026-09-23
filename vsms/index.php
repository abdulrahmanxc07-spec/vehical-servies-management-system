<?php
$pageTitle = 'Dashboard';
require_once 'config/db.php';
requireLogin();
$pdo = getDB();

$totalCustomers  = $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalVehicles   = $pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
$activeServices  = $pdo->query("SELECT COUNT(*) FROM services WHERE status IN ('Pending','In Progress')")->fetchColumn();
$totalRevenue    = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE payment_status='Paid'")->fetchColumn();
$pendingPayments = $pdo->query("SELECT COUNT(*) FROM invoices WHERE payment_status='Unpaid'")->fetchColumn();
$lowStock        = $pdo->query('SELECT COUNT(*) FROM spare_parts WHERE quantity <= low_stock_alert')->fetchColumn();

$recentServices = $pdo->query("
  SELECT s.service_id, s.service_type, s.status, s.created_at,
         v.registration_no, v.make, v.model,
         c.name AS customer_name,
         m.name AS mechanic_name
  FROM services s
  JOIN vehicles v  ON s.vehicle_id  = v.vehicle_id
  JOIN customers c ON v.customer_id = c.customer_id
  LEFT JOIN mechanics m ON s.mechanic_id = m.mechanic_id
  ORDER BY s.created_at DESC LIMIT 8
")->fetchAll();

$recentInvoices = $pdo->query("
  SELECT i.invoice_id, i.total_amount, i.payment_status, i.created_at,
         v.registration_no
  FROM invoices i
  JOIN services s ON i.service_id = s.service_id
  JOIN vehicles v ON s.vehicle_id = v.vehicle_id
  ORDER BY i.created_at DESC LIMIT 5
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-users"></i></div>
    <div class="stat-info"><div class="stat-value"><?= $totalCustomers ?></div><div class="stat-label">Total Customers</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-car"></i></div>
    <div class="stat-info"><div class="stat-value"><?= $totalVehicles ?></div><div class="stat-label">Registered Vehicles</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-wrench"></i></div>
    <div class="stat-info"><div class="stat-value"><?= $activeServices ?></div><div class="stat-label">Active Services</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div>
    <div class="stat-info"><div class="stat-value">Rs. <?= number_format($totalRevenue) ?></div><div class="stat-label">Total Revenue</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-file-invoice"></i></div>
    <div class="stat-info"><div class="stat-value"><?= $pendingPayments ?></div><div class="stat-label">Pending Payments</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="stat-info"><div class="stat-value"><?= $lowStock ?></div><div class="stat-label">Low Stock Items</div></div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-wrench" style="color:var(--primary)"></i> Recent Services</h3>
      <a href="/vsms/modules/services/index.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Vehicle</th><th>Type</th><th>Mechanic</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (empty($recentServices)): ?>
          <tr><td colspan="4" class="text-muted" style="padding:20px;text-align:center">No services yet</td></tr>
        <?php else: foreach ($recentServices as $s):
          $badge = match($s['status']) {
            'Completed' => 'badge-success', 'In Progress' => 'badge-info',
            'Pending'   => 'badge-warning', default => 'badge-danger'
          }; ?>
          <tr>
            <td><div class="fw-bold"><?= htmlspecialchars($s['registration_no']) ?></div>
                <div class="text-muted"><?= htmlspecialchars($s['make'].' '.$s['model']) ?></div></td>
            <td><?= htmlspecialchars($s['service_type']) ?></td>
            <td><?= htmlspecialchars($s['mechanic_name'] ?? '—') ?></td>
            <td><span class="badge <?= $badge ?>"><?= $s['status'] ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:20px">
    <div class="card">
      <div class="card-header"><h3><i class="fas fa-bolt" style="color:var(--warning)"></i> Quick Actions</h3></div>
      <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <a href="/vsms/modules/customers/add.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Customer</a>
        <a href="/vsms/modules/vehicles/add.php"  class="btn btn-secondary"><i class="fas fa-car"></i> Add Vehicle</a>
        <a href="/vsms/modules/services/add.php"  class="btn btn-secondary"><i class="fas fa-wrench"></i> New Service</a>
        <a href="/vsms/modules/billing/add.php"   class="btn btn-secondary"><i class="fas fa-file-invoice"></i> Create Invoice</a>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <h3><i class="fas fa-file-invoice-dollar" style="color:var(--success)"></i> Recent Invoices</h3>
        <a href="/vsms/modules/billing/index.php" class="btn btn-secondary btn-sm">View All</a>
      </div>
      <div class="table-responsive">
        <table>
          <thead><tr><th>ID</th><th>Vehicle</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (empty($recentInvoices)): ?>
            <tr><td colspan="4" class="text-muted" style="padding:20px;text-align:center">No invoices yet</td></tr>
          <?php else: foreach ($recentInvoices as $inv):
            $badge = $inv['payment_status']==='Paid' ? 'badge-success' : 'badge-warning'; ?>
            <tr>
              <td>#<?= $inv['invoice_id'] ?></td>
              <td><?= htmlspecialchars($inv['registration_no']) ?></td>
              <td>Rs. <?= number_format($inv['total_amount'],2) ?></td>
              <td><span class="badge <?= $badge ?>"><?= $inv['payment_status'] ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
