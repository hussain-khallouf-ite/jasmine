<?php
require_once __DIR__ . '/../../src/controllers/UserController.php';

UserController::handle($_SERVER['REQUEST_METHOD']);
