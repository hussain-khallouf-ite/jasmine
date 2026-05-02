<?php
require_once __DIR__ . '/../../../src/controllers/admin/AuthController.php';

$action = $_GET['action'] ?? '';
AdminAuthController::handle($action, $_SERVER['REQUEST_METHOD']);
