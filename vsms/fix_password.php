<?php

require_once 'config/db.php';

$hash = password_hash('admin123', PASSWORD_DEFAULT);

$pdo = getDB();

$stmt = $pdo->prepare("
UPDATE users
SET password=?
WHERE email='admin@vsms.com'
");

$stmt->execute([$hash]);

echo "Password updated successfully!";