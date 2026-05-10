<?php
require_once __DIR__ . '/../../src/controllers/PropertyController.php';

PropertyController::handle($_SERVER['REQUEST_METHOD']);
