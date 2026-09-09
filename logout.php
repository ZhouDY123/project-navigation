<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && valid_csrf((string)($_POST['csrf_token'] ?? ''))) logout_admin();
header('Location: login.php');
exit;

