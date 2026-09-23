
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Vehicle ";

$search = trim($_GET['search'] ?? '');

if ($search != '') {

    $stmt = $pdo->prepare("
        SELECT
            v.*,
            c.name AS customer_name
        FROM vehicles v
        LEFT JOIN customers c
            ON v.customer_id = c.customer_id
        WHERE
            v.registration_no LIKE ?
            OR v.make LIKE ?
            OR v.model LIKE ?
            OR c.name LIKE ?
        ORDER BY v.vehicle_id DESC
    ");

    $stmt->execute([
        "%$search%",
        "%$search%",
        "%$search%",
        "%$search%"
    ]);

} else {

    $stmt = $pdo->query("
        SELECT
            v.*,
            c.name AS customer_name
        FROM vehicles v
        LEFT JOIN customers c
            ON v.customer_id = c.customer_id
        ORDER BY v.vehicle_id DESC
    ");
}

$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Vehicle </title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{
    font-family:Arial,sans-serif;
    background:#0f172a;
    color:#fff;
    margin:0;
    padding:20px;
}

.card{
    background:#1e293b;
    border-radius:15px;
    overflow:hidden;
}

.card-header{
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.card-body{
    padding:20px;
}

.btn{
    padding:10px 15px;
    text-decoration:none;
    border:none;
    border-radius:8px;
    color:#fff;
    cursor:pointer;
}

.btn-primary{background:#3b82f6;}
.btn-secondary{background:#64748b;}
.btn-warning{background:#f59e0b;}
.btn-danger{background:#ef4444;}

.form-control{
    padding:10px;
    border-radius:8px;
    border:1px solid #334155;
    background:#0f172a;
    color:#fff;
}

.search-bar{
    display:flex;
    gap:10px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#111827;
    padding:15px;
}

td{
    padding:15px;
    border-top:1px solid #334155;
}

tr:hover{
    background:#273449;
}

.alert{
    padding:15px;
    margin-bottom:15px;
    border-radius:10px;
}

.alert-success{
    background:#065f46;
}

.alert-warning{
    background:#92400e;
}

.badge{
    background:#0891b2;
    padding:5px 10px;
    border-radius:20px;
}

</style>

</head>
<body>

<?php if($msg=="added"): ?>
<div class="alert alert-success">
Vehicle Added Successfully
</div>
<?php endif; ?>

<?php if($msg=="updated"): ?>
<div class="alert alert-success">
Vehicle Updated Successfully
</div>
<?php endif; ?>

<?php if($msg=="deleted"): ?>
<div class="alert alert-warning">
Vehicle Deleted Successfully
</div>
<?php endif; ?>

<div class="card">

<div class="card-header">

<h2>
<i class="fas fa-car"></i>
Vehicle 
</h2>

<a href="add.php" class="btn btn-primary">
<i class="fas fa-plus"></i>
Add Vehicle
</a>

</div>

<div class="card-body">

<form method="GET" class="search-bar">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Vehicle..."
value="<?= htmlspecialchars($search) ?>">

<button type="submit" class="btn btn-secondary">
Search
</button>

</form>

</div>

<table>

<thead>
<tr>
<th>ID</th>
<th>Registration</th>
<th>Make</th>
<th>Model</th>
<th>Year</th>
<th>Color</th>
<th>Mileage</th>
<th>Owner</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if(count($vehicles)>0): ?>

<?php foreach($vehicles as $v): ?>

<tr>

<td><?= $v['vehicle_id'] ?></td>

<td>
<span class="badge">
<?= htmlspecialchars($v['registration_no']) ?>
</span>
</td>

<td><?= htmlspecialchars($v['make']) ?></td>

<td><?= htmlspecialchars($v['model']) ?></td>

<td><?= $v['year'] ?></td>

<td><?= htmlspecialchars($v['color']) ?></td>

<td><?= number_format($v['mileage']) ?> km</td>

<td><?= htmlspecialchars($v['customer_name']) ?></td>

<td>

<a href="edit.php?id=<?= $v['vehicle_id'] ?>"
class="btn btn-warning">
<i class="fas fa-edit"></i>
</a>

<a href="delete.php?id=<?= $v['vehicle_id'] ?>"
class="btn btn-danger"
onclick="return confirm('Delete this vehicle?')">
<i class="fas fa-trash"></i>
</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="9" align="center">
No Vehicles Found
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</body>
</html>
```
