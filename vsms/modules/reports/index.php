<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();
$pdo = getDB();
// modules/reports/index.php
$pageTitle = 'Reports';



// Summary stats
$totalRevenue   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE payment_status='Paid'")->fetchColumn();
$totalInvoices  = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$totalServices  = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$completedSvcs  = $pdo->query("SELECT COUNT(*) FROM services WHERE status='Completed'")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalVehicles  = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();

// Monthly revenue (last 6 months)
$monthlyRevenue = $pdo->query("
  SELECT DATE_FORMAT(created_at, '%b %Y') AS month,
         DATE_FORMAT(created_at, '%Y-%m') AS month_sort,
         SUM(total_amount) AS revenue
  FROM invoices
  WHERE payment_status='Paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
  GROUP BY month, month_sort
  ORDER BY month_sort ASC
")->fetchAll();

// Service type breakdown
$serviceTypes = $pdo->query("
  SELECT service_type, COUNT(*) AS cnt
  FROM services
  GROUP BY service_type
  ORDER BY cnt DESC
  LIMIT 8
")->fetchAll();

// Top mechanics by jobs
$topMechanics = $pdo->query("
  SELECT m.name, COUNT(s.service_id) AS jobs,
         SUM(CASE WHEN s.status='Completed' THEN 1 ELSE 0 END) AS completed
  FROM mechanics m
  LEFT JOIN services s ON m.mechanic_id = s.mechanic_id
  GROUP BY m.mechanic_id, m.name
  ORDER BY jobs DESC
")->fetchAll();

// Recent completed services revenue
$topServices = $pdo->query("
  SELECT s.service_type, COUNT(i.invoice_id) AS invoices,
         SUM(i.total_amount) AS total
  FROM services s
  JOIN invoices i ON s.service_id = i.service_id
  WHERE i.payment_status='Paid'
  GROUP BY s.service_type
  ORDER BY total DESC
  LIMIT 5
")->fetchAll();
?>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom:28px">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div>
    <div class="stat-info">
      <div class="stat-value">Rs. <?= number_format($totalRevenue) ?></div>
      <div class="stat-label">Total Revenue (Paid)</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $totalInvoices ?></div>
      <div class="stat-label">Total Invoices</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-wrench"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $totalServices ?></div>
      <div class="stat-label">Total Services</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $completedSvcs ?></div>
      <div class="stat-label">Completed Services</div>
    </div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:24px">

  <!-- Monthly Revenue Chart -->
  <div class="card">
    <div class="card-header"><h3><i class="fas fa-chart-bar" style="color:var(--success)"></i> Monthly Revenue (Last 6 Months)</h3></div>
    <div class="card-body">
      <?php if (empty($monthlyRevenue)): ?>
        <p class="text-muted">No revenue data yet.</p>
      <?php else:
        $maxRev = max(array_column($monthlyRevenue, 'revenue')) ?: 1;
      ?>
        <div style="display:flex;align-items:flex-end;gap:12px;height:180px;padding-bottom:8px">
          <?php foreach ($monthlyRevenue as $mr):
            $height = round(($mr['revenue'] / $maxRev) * 150);
          ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
              <div style="font-size:11px;color:var(--text-muted)">Rs.<?= number_format($mr['revenue'] / 1000, 1) ?>k</div>
              <div style="width:100%;background:linear-gradient(to top,var(--primary),#e74c3c);border-radius:4px 4px 0 0;height:<?= $height ?>px;min-height:4px"></div>
              <div style="font-size:10px;color:var(--text-muted);text-align:center"><?= $mr['month'] ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Service Type Breakdown -->
  <div class="card">
    <div class="card-header"><h3><i class="fas fa-chart-pie" style="color:var(--info)"></i> Service Types</h3></div>
    <div class="card-body">
      <?php if (empty($serviceTypes)): ?>
        <p class="text-muted">No data yet.</p>
      <?php else:
        $maxCnt = max(array_column($serviceTypes, 'cnt')) ?: 1;
      ?>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach ($serviceTypes as $st):
            $pct = round(($st['cnt'] / $maxCnt) * 100);
          ?>
            <div>
              <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:13px"><?= htmlspecialchars($st['service_type']) ?></span>
                <span class="badge badge-info"><?= $st['cnt'] ?></span>
              </div>
              <div style="background:rgba(255,255,255,0.06);border-radius:4px;height:8px">
                <div style="background:var(--info);border-radius:4px;height:8px;width:<?= $pct ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="grid-2">

  <!-- Top Mechanics -->
  <div class="card">
    <div class="card-header"><h3><i class="fas fa-hard-hat" style="color:var(--warning)"></i> Mechanic Performance</h3></div>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Mechanic</th><th>Total Jobs</th><th>Completed</th><th>Rate</th></tr></thead>
        <tbody>
          <?php if (empty($topMechanics)): ?>
            <tr><td colspan="4" class="text-muted" style="padding:20px;text-align:center">No data</td></tr>
          <?php else: foreach ($topMechanics as $tm): ?>
            <?php $rate = $tm['jobs'] > 0 ? round(($tm['completed'] / $tm['jobs']) * 100) : 0; ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($tm['name']) ?></td>
              <td><span class="badge badge-info"><?= $tm['jobs'] ?></span></td>
              <td><span class="badge badge-success"><?= $tm['completed'] ?></span></td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <div style="flex:1;background:rgba(255,255,255,0.06);border-radius:4px;height:6px">
                    <div style="background:var(--success);border-radius:4px;height:6px;width:<?= $rate ?>%"></div>
                  </div>
                  <span style="font-size:12px"><?= $rate ?>%</span>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Revenue by Service Type -->
  <div class="card">
    <div class="card-header"><h3><i class="fas fa-trophy" style="color:var(--warning)"></i> Top Revenue by Service</h3></div>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Service Type</th><th>Invoices</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php if (empty($topServices)): ?>
            <tr><td colspan="3" class="text-muted" style="padding:20px;text-align:center">No paid invoices yet</td></tr>
          <?php else: foreach ($topServices as $ts): ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($ts['service_type']) ?></td>
              <td><?= $ts['invoices'] ?></td>
              <td style="color:var(--success);font-weight:600">Rs. <?= number_format($ts['total'], 2) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/includes/footer.php"; ?>
