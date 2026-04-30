<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../models/Property.php';

class PropertyController
{
    public static function handle(string $method): void
    {
        switch ($method) {
            case 'GET':
                self::list();
                break;
            default:
                sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function list(): void
    {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $properties = Property::getAll(['limit' => $limit, 'status' => 'available']);

        sendJson(['success' => true, 'properties' => $properties]);
    }
}
