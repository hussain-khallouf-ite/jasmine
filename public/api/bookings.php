<?php

require_once __DIR__ . '/../../src/controllers/BookingController.php';

$controller = new BookingController();

$action = $_GET['action'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($action === 'list') {
            $controller->listUserBookings();
        } elseif ($action === 'details') {
            $controller->getDetails();
        } else {
            sendResponse(['error' => 'Action not found'], 404);
        }
        break;
    case 'POST':
        if ($action === 'create') {
            $controller->create();
        } elseif ($action === 'confirm') {
            $controller->confirm();
        } elseif ($action === 'cancel') {
            $controller->cancel();
        } else {
            sendResponse(['error' => 'Action not found'], 404);
        }
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}
