<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Property.php';

class AdminPropertyController
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
        $properties = Property::getAll(['status' => 'all', 'limit' => 1000]);
        sendJson(['success' => true, 'properties' => $properties]);
    }

    private static function store(): void
    {
        $data = self::validatePropertyData($_POST);
        
        $property = Property::create($data);
        if ($property) {
            sendJson(['success' => true, 'message' => 'تم إضافة الشقة بنجاح.', 'property' => $property]);
        } else {
            sendJson(['success' => false, 'message' => 'حدث خطأ أثناء الإضافة.'], 500);
        }
    }

    private static function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            sendJson(['success' => false, 'message' => 'معرف الشقة غير صالح.'], 400);
        }

        $data = self::validatePropertyData($_POST);
        
        $success = Property::update($id, $data);
        if ($success) {
            sendJson(['success' => true, 'message' => 'تم تحديث الشقة بنجاح.']);
        } else {
            sendJson(['success' => false, 'message' => 'حدث خطأ أثناء التحديث.'], 500);
        }
    }

    private static function destroy(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            sendJson(['success' => false, 'message' => 'معرف الشقة غير صالح.'], 400);
        }

        $success = Property::delete($id);
        if ($success) {
            sendJson(['success' => true, 'message' => 'تم حذف الشقة بنجاح.']);
        } else {
            sendJson(['success' => false, 'message' => 'حدث خطأ أثناء الحذف.'], 500);
        }
    }

    private static function validatePropertyData(array $input): array
    {
        $errors = [];
        $title = trim($input['title'] ?? '');
        if ($title === '') $errors['title'] = 'عنوان الشقة مطلوب.';

        $rooms = (int)($input['rooms'] ?? 0);
        if ($rooms <= 0) $errors['rooms'] = 'عدد الغرف غير صالح.';

        $size_m2 = (float)($input['size_m2'] ?? 0);
        if ($size_m2 <= 0) $errors['size_m2'] = 'المساحة غير صالحة.';

        $floor = (int)($input['floor'] ?? 0);

        $price = (float)($input['price_per_month'] ?? 0);
        if ($price <= 0) $errors['price_per_month'] = 'السعر غير صالح.';

        if (!empty($errors)) {
            sendJson(['success' => false, 'errors' => $errors], 422);
            exit;
        }

        $amenities = [];
        if (!empty($input['amenities'])) {
            // It could be an array if sent as amenities[] or comma separated string
            if (is_array($input['amenities'])) {
                $amenities = $input['amenities'];
            } else {
                $amenities = array_map('trim', explode(',', $input['amenities']));
            }
        }

        return [
            'title' => $title,
            'description' => trim($input['description'] ?? ''),
            'location' => trim($input['location'] ?? 'غير محددة'),
            'image_url' => trim($input['image_url'] ?? ''),
            'type' => in_array($input['type'] ?? '', ['residential', 'commercial']) ? $input['type'] : 'residential',
            'rooms' => $rooms,
            'size_m2' => $size_m2,
            'floor' => $floor,
            'price_per_month' => $price,
            'status' => in_array($input['status'] ?? '', ['available', 'reserved', 'unavailable']) ? $input['status'] : 'available',
            'amenities' => $amenities
        ];
    }
}
