<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/vsms/config/db.php';
requireLogin();
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) { $pdo->prepare('DELETE FROM spare_parts WHERE part_id=?')->execute([$id]); }
header('Location: index.php?msg=deleted'); exit;
