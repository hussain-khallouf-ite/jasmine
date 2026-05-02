<?php
require_once __DIR__ . '/../../../src/controllers/admin/UserController.php';

$action = $_GET['action'] ?? 'index';
AdminUserController::handle($action, $_SERVER['REQUEST_METHOD']);
