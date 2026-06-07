<?php
require_once __DIR__ . '/../../src/controllers/BuildingController.php';

BuildingController::handle($_SERVER['REQUEST_METHOD']);
