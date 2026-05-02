<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/database.php';
require_once __DIR__ . '/../helpers/template.php';
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
        $options = [
            'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 12,
            'status' => 'available'
        ];

        if (!empty($_GET['type'])) {
            $options['type'] = $_GET['type'];
        }

        if (!empty($_GET['rooms'])) {
            $options['rooms'] = $_GET['rooms'];
        }

        $properties = Property::getAll($options);

        sendJson(['success' => true, 'properties' => $properties]);
    }

    public static function renderListPage(): void
    {
        $properties = [];
        $errorMessage = null;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 12;

        try {
            $properties = Property::getAvailable($limit);
        } catch (PDOException $e) {
            $errorMessage = 'تعذر تحميل الشقق في الوقت الحالي. يرجى المحاولة مرة أخرى لاحقاً.';
        }

        require __DIR__ . '/../views/property-list.php';
    }
}
