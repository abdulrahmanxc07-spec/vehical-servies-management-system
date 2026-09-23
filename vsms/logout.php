<?php
require_once 'config/db.php';
session_destroy();
header('Location: /vsms/login.php');
exit;
