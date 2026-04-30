<?php

require_once __DIR__ . '/../helpers/database.php';
require_once __DIR__ . '/../helpers/template.php';
require_once __DIR__ . '/../models/Property.php';

class PropritePageController
{
    public static function renderListPage(): void
    {
        $proprites = [];
        $errorMessage = null;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 12;

        try {
            $proprites = Property::getAvailable($limit);
        } catch (PDOException $e) {
            $errorMessage = 'تعذر تحميل الشقق في الوقت الحالي. يرجى المحاولة مرة أخرى لاحقاً.';
        }

        require __DIR__ . '/../views/proprite-list.php';
    }
}
