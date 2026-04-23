<?php
require_once __DIR__ . '/../../config/database.php';

function getPDO(): PDO
{
    global $pdo;
    return $pdo;
}
