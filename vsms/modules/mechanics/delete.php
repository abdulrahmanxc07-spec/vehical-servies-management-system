<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/vsms/config/db.php";
requireLogin();

$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {

    $stmt = $pdo->prepare("
        SELECT mechanic_id
        FROM mechanics
        WHERE mechanic_id = ?
    ");
    $stmt->execute([$id]);

    if ($stmt->fetch()) {

        $delete = $pdo->prepare("
            DELETE FROM mechanics
            WHERE mechanic_id = ?
        ");
        $delete->execute([$id]);

        header("Location: index.php?msg=deleted");
        exit;
    }
}

header("Location: index.php");
exit;
?>
