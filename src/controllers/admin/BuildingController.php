<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Building.php';

class AdminBuildingController
{
    public static function handle(string $action, string $method): void
    {
        if (!isAuthenticated() || currentUser()['role'] !== 'admin') {
            sendJson(['success' => false, 'message' => 'غير مصرح لك بالوصول.'], 403);
        }

        switch ($action) {
            case 'index':
                if ($method === 'GET') self::index();
                else sendJson(['success' => false, 'message' => 'طريقة غير مسموحة'], 405);
                break;
            case 'store':
                if ($method === 'POST') self::store();
                else sendJson(['success' => false, 'message' => 'طريقة غير مسموحة'], 405);
                break;
            case 'update':
                if ($method === 'POST') self::update();
                else sendJson(['success' => false, 'message' => 'طريقة غير مسموحة'], 405);
                break;
            case 'destroy':
                if ($method === 'POST') self::destroy();
                else sendJson(['success' => false, 'message' => 'طريقة غير مسموحة'], 405);
                break;
            default:
                sendJson(['success' => false, 'message' => 'إجراء غير معروف.'], 400);
        }
    }

    private static function index(): void
    {
        try {
            $buildings = Building::getAll(['limit' => 1000]);
            sendJson(['success' => true, 'buildings' => $buildings]);
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'تعذر تحميل قائمة المباني.'], 500);
        }
    }

    private static function store(): void
    {
        $data = self::validateBuildingData($_POST);
        
        try {
            $building = Building::create($data);
            if ($building) {
                sendJson(['success' => true, 'message' => 'تم إضافة المبنى بنجاح.', 'building' => $building]);
            } else {
                sendJson(['success' => false, 'message' => 'حدث خطأ أثناء الإضافة.'], 500);
            }
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات أثناء الإضافة.'], 500);
        }
    }

    private static function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            sendJson(['success' => false, 'message' => 'معرف المبنى غير صالح.'], 400);
        }

        $data = self::validateBuildingData($_POST);
        
        try {
            $success = Building::update($id, $data);
            if ($success) {
                sendJson(['success' => true, 'message' => 'تم تحديث المبنى بنجاح.']);
            } else {
                sendJson(['success' => false, 'message' => 'حدث خطأ أثناء التحديث.'], 500);
            }
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات أثناء التحديث.'], 500);
        }
    }

    private static function destroy(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            sendJson(['success' => false, 'message' => 'معرف المبنى غير صالح.'], 400);
        }

        try {
            $success = Building::delete($id);
            if ($success) {
                sendJson(['success' => true, 'message' => 'تم حذف المبنى بنجاح.']);
            } else {
                sendJson(['success' => false, 'message' => 'حدث خطأ أثناء الحذف.'], 500);
            }
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات أثناء الحذف.'], 500);
        }
    }

    private static function validateBuildingData(array $input): array
    {
        $errors = [];
        $name = trim($input['name'] ?? '');
        if ($name === '') $errors['name'] = 'اسم المبنى مطلوب.';

        $floors = (int)($input['floors'] ?? 0);
        if ($floors <= 0) $errors['floors'] = 'عدد الطوابق غير صالح.';

        $total_area_m2 = (float)($input['total_area_m2'] ?? 0);
        if ($total_area_m2 <= 0) $total_area_m2 = null; // optional

        if (!empty($errors)) {
            sendJson(['success' => false, 'errors' => $errors], 422);
            exit;
        }

        return [
            'name' => $name,
            'description' => trim($input['description'] ?? ''),
            'floors' => $floors,
            'image_url' => trim($input['image_url'] ?? ''),
            'location' => trim($input['location'] ?? 'غير محددة'),
            'total_area_m2' => $total_area_m2
        ];
    }
}
