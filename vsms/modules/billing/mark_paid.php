<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/vsms/config/db.php';
requireLogin();
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("UPDATE invoices SET payment_status='Paid', payment_date=CURDATE() WHERE invoice_id=?")->execute([$id]);
}
header('Location: index.php?msg=paid'); exit;
