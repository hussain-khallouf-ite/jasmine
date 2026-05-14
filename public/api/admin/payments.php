<?php
require_once __DIR__ . '/../../../src/controllers/admin/PaymentController.php';

$method = $_SERVER['REQUEST_METHOD'];
AdminPaymentController::handle($method);
