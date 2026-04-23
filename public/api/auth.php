<?php
require_once __DIR__ . '/../../src/controllers/AuthController.php';

$action = $_GET['action'] ?? '';
AuthController::handle($action, $_SERVER['REQUEST_METHOD']);
