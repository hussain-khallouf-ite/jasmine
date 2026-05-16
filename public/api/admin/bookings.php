<?php
require_once __DIR__ . '/../../../src/controllers/admin/BookingController.php';

$method = $_SERVER['REQUEST_METHOD'];
AdminBookingController::handle($method);
