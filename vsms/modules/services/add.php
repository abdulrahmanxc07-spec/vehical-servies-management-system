<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/config/db.php";
requireLogin();

$pdo = getDB();
$pageTitle = "Add Service";

require_once $_SERVER["DOCUMENT_ROOT"] . "/vsms/includes/header.php";

$errors = [];

/* Load Vehicles */
$vehicles = $pdo->query("
    SELECT v.vehicle_id,
           v.registration_no,
           v.make,
           v.model,
           c.name AS customer
    FROM vehicles v
    INNER JOIN customers c ON v.customer_id = c.customer_id
    ORDER BY v.registration_no
")->fetchAll(PDO::FETCH_ASSOC);

/* Load Active Mechanics */
$mechanics = $pdo->query("
    SELECT mechanic_id, name
    FROM mechanics
    WHERE status='Active'
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle_id   = (int)($_POST['vehicle_id'] ?? 0);
    $mechanic_id  = !empty($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : null;
    $service_type = trim($_POST['service_type'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $status       = trim($_POST['status'] ?? 'Pending');
    $start_date   = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
    $end_date     = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    if ($vehicle_id <= 0) {
        $errors[] = "Please select a vehicle.";
    }

    if (empty($service_type)) {
        $errors[] = "Service type is required.";
    }

    /* Daily limit = 10 services */
    $limit = 10;

    $check = $pdo->prepare("
        SELECT COUNT(*)
        FROM services
        WHERE DATE(start_date) = ?
    ");

    $check->execute([$start_date]);

    $todayCount = $check->fetchColumn();

    if ($todayCount >= $limit) {
        $errors[] = "Daily service limit reached. Maximum {$limit} services allowed per day.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO services
            (
                vehicle_id,
                mechanic_id,
                service_type,
                description,
                status,
                start_date,
                end_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $vehicle_id,
            $mechanic_id,
            $service_type,
            $description,
            $status,
            $start_date,
            $end_date
        ]);

        header("Location: index.php?msg=added");
        exit;
    }
}
?>