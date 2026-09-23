<?php
// includes/header.php

require_once __DIR__ . '/../config/db.php';

requireLogin();

$pdo = getDB();
$flash = getFlash();
$current = basename(dirname($_SERVER['PHP_SELF']));
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | VSMS</title>

    <link rel="stylesheet" href="/vsms/assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="logo">🔧</div>
            <div>
                <h2>VSMS</h2>
                <span>Vehicle Service Management System</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section">Main</div>

            <a href="/vsms/index.php"
               class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'modules') === false) ? 'active' : '' ?>">
                <i class="fas fa-gauge-high"></i>
                Dashboard
            </a>

            <div class="nav-section">Management</div>

            <a href="/vsms/modules/customers/index.php"
               class="nav-item <?= $current == 'customers' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                Customers
            </a>

            <a href="/vsms/modules/vehicles/index.php"
               class="nav-item <?= $current == 'vehicles' ? 'active' : '' ?>">
                <i class="fas fa-car"></i>
                Vehicles
            </a>

            <a href="/vsms/modules/services/index.php"
               class="nav-item <?= $current == 'services' ? 'active' : '' ?>">
                <i class="fas fa-screwdriver-wrench"></i>
                Services
            </a>

            <a href="/vsms/modules/mechanics/index.php"
               class="nav-item <?= $current == 'mechanics' ? 'active' : '' ?>">
                <i class="fas fa-user-gear"></i>
                Mechanics
            </a>

            <div class="nav-section">Operations</div>

            <a href="/vsms/modules/inventory/index.php"
               class="nav-item <?= $current == 'inventory' ? 'active' : '' ?>">
                <i class="fas fa-boxes-stacked"></i>
                Inventory
            </a>

            <a href="/vsms/modules/billing/index.php"
               class="nav-item <?= $current == 'billing' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                Billing
            </a>

            <a href="/vsms/modules/reports/index.php"
               class="nav-item <?= $current == 'reports' ? 'active' : '' ?>">
                <i class="fas fa-chart-column"></i>
                Reports
            </a>

        </nav>

        <div class="sidebar-footer">

            <div class="sidebar-user">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                </div>

                <div class="user-info">
                    <div class="user-name">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    </div>

                    <div class="user-role">
                        <?= htmlspecialchars($_SESSION['user_role'] ?? 'Staff') ?>
                    </div>
                </div>
            </div>

            <a href="/vsms/logout.php" class="logout-btn">
                <i class="fas fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <div class="topbar">

            <div>
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
            </div>

            <div class="topbar-right">
                <i class="fas fa-calendar-days"></i>
                <?= date('l, d M Y') ?>
            </div>

        </div>

        <div class="page-body">

            <?php if (!empty($flash)): ?>

                <div class="alert alert-<?= $flash['type'] == 'success' ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>

            <?php endif; ?>