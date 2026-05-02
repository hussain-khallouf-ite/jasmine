<?php
require_once __DIR__ . '/../../../src/controllers/admin/DashboardController.php';

AdminDashboardController::handle($_SERVER['REQUEST_METHOD']);
