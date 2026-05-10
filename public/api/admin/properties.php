<?php
require_once __DIR__ . '/../../../src/controllers/admin/PropertyController.php';

$action = $_GET['action'] ?? 'index';
AdminPropertyController::handle($action, $_SERVER['REQUEST_METHOD']);
