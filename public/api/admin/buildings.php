<?php
require_once __DIR__ . '/../../../src/controllers/admin/BuildingController.php';

$action = $_GET['action'] ?? 'index';
AdminBuildingController::handle($action, $_SERVER['REQUEST_METHOD']);
