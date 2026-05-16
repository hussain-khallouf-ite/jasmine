<?php

require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

class AdminBookingController
{
    public static function handle(string $method): void
    {
        if (!isAuthenticated() || currentUser()['role'] !== 'admin') {
            sendJson(['success' => false, 'message' => 'غير مصرح لك بالوصول.'], 403);
        }

        $action = $_GET['action'] ?? 'index';

        if ($method === 'GET') {
            if ($action === 'index') {
                self::list();
            }
        } elseif ($method === 'POST') {
            if ($action === 'update_status') {
                self::updateStatus();
            } elseif ($action === 'delete') {
                self::delete();
            }
        } else {
            sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function list(): void
    {
        $pdo = getPDO();
        $sql = 'SELECT b.*, u.name as customer_name, u.email as customer_email, p.title as property_title
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN properties p ON b.property_id = p.id
                ORDER BY b.created_at DESC';
        
        $bookings = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        sendJson(['success' => true, 'bookings' => $bookings]);
    }

    private static function updateStatus(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$id || !$status) {
            sendJson(['success' => false, 'message' => 'بيانات غير مكتملة.'], 400);
        }

        if (Booking::updateStatus($id, $status)) {
            sendJson(['success' => true, 'message' => 'تم تحديث حالة الحجز بنجاح.']);
        } else {
            sendJson(['success' => false, 'message' => 'حدث خطأ أثناء التحديث.'], 500);
        }
    }

    private static function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            sendJson(['success' => false, 'message' => 'المعرف غير صالح.'], 400);
        }

        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = :id');
        if ($stmt->execute(['id' => $id])) {
            sendJson(['success' => true, 'message' => 'تم حذف الحجز بنجاح.']);
        } else {
            sendJson(['success' => false, 'message' => 'حدث خطأ أثناء الحذف.'], 500);
        }
    }
}
