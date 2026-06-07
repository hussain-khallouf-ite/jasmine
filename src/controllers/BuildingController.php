<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/database.php';
require_once __DIR__ . '/../models/Building.php';

class BuildingController
{
    public static function handle(string $method): void
    {
        switch ($method) {
            case 'GET':
                if (isset($_GET['action']) && $_GET['action'] === 'stats') {
                    self::stats();
                } elseif (isset($_GET['id'])) {
                    self::get((int)$_GET['id']);
                } else {
                    self::list();
                }
                break;
            default:
                sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function list(): void
    {
        try {
            $buildings = Building::getAll();
            sendJson(['success' => true, 'buildings' => $buildings]);
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'تعذر تحميل قائمة المباني.'], 500);
        }
    }

    private static function get(int $id): void
    {
        try {
            $building = Building::findById($id);

            if (!$building) {
                sendJson(['success' => false, 'message' => 'المبنى غير موجود.'], 404);
                return;
            }

            $properties = Building::getProperties($id);

            sendJson([
                'success' => true, 
                'building' => $building,
                'properties' => $properties
            ]);
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'تعذر تحميل تفاصيل المبنى.'], 500);
        }
    }

    private static function stats(): void
    {
        try {
            $pdo = getPDO();
            
            // Buildings count
            $stmt = $pdo->query('SELECT COUNT(*) FROM buildings');
            $buildingsCount = (int)$stmt->fetchColumn();

            // Total properties count
            $stmt = $pdo->query('SELECT COUNT(*) FROM properties');
            $propertiesCount = (int)$stmt->fetchColumn();

            // Available properties count
            $stmt = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'");
            $availablePropertiesCount = (int)$stmt->fetchColumn();

            sendJson([
                'success' => true,
                'stats' => [
                    'total_area_m2' => 50000,
                    'buildings_count' => $buildingsCount,
                    'parks_count' => 2,
                    'green_area_m2' => 15000,
                    'properties_count' => $propertiesCount,
                    'available_properties_count' => $availablePropertiesCount,
                    'commercial_count' => 12,
                    'swimming_pools' => 2,
                    'gyms_count' => 1,
                    'parking_slots' => 250
                ]
            ]);
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'تعذر تحميل إحصائيات المشروع.'], 500);
        }
    }
}
