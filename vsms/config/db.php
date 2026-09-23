<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // Leave empty — XAMPP default
define('DB_NAME', 'vsms_db');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('
            <style>
                body { background:#0d1117; font-family:monospace; display:flex;
                       align-items:center; justify-content:center; min-height:100vh; }
                .box { background:#161b22; border:1px solid #f85149; border-radius:12px;
                       padding:40px; max-width:500px; width:100%; }
                h2   { color:#f85149; margin-bottom:14px; }
                p    { color:#8b949e; font-size:13px; line-height:1.8; }
                code { background:#21262d; padding:2px 8px; border-radius:4px; color:#f97316; }
            </style>
            <div class="box">
                <h2>❌ Database Connection Failed</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <hr style="border-color:#30363d;margin:16px 0">
                <p>
                    1. Open <code>XAMPP Control Panel</code><br>
                    2. Click <code>Start</code> next to <code>MySQL</code><br>
                    3. Make sure database <code>vsms_db</code> exists in phpMyAdmin<br>
                    4. Go to <code>localhost/phpmyadmin</code> and check
                </p>
            </div>');
        }
    }
    return $pdo;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /vsms/login.php');
        exit;
    }
}

function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash`'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
?>