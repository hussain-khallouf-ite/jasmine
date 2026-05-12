<?php
require_once __DIR__ . '/../../../src/controllers/admin/ReportController.php';

$method = $_SERVER['REQUEST_METHOD'];
AdminReportController::handle($method);
