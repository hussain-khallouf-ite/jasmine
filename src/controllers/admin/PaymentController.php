<?php

require_once __DIR__ . '/../../models/Payment.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

class AdminPaymentController
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
            } elseif ($action === 'pending_bookings') {
                self::getPendingBookings();
            }
        } elseif ($method === 'POST') {
            if ($action === 'store') {
                self::store();
            }
        } else {
            sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function list(): void
    {
        $payments = Payment::getAll();
        sendJson(['success' => true, 'payments' => $payments]);
    }

    private static function getPendingBookings(): void
    {
        $pdo = getPDO();
        // Get bookings that are pending and not fully paid
        // For simplicity, we just get bookings with status 'pending'
        $sql = 'SELECT b.*, u.name as customer_name, p.title as property_title
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN properties p ON b.property_id = p.id
                WHERE b.status = "pending"
                ORDER BY b.created_at DESC';
        
        $bookings = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        sendJson(['success' => true, 'bookings' => $bookings]);
    }

    private static function store(): void
    {
        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        $booking_id = (int)($input['booking_id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        $method = $input['method'] ?? 'cash';
        $notes = $input['notes'] ?? '';

        if ($booking_id <= 0 || $amount <= 0) {
            sendJson(['success' => false, 'message' => 'بيانات الدفع غير مكتملة.'], 400);
        }

        $booking = Booking::findById($booking_id);
        if (!$booking) {
            sendJson(['success' => false, 'message' => 'الحجز غير موجود.'], 404);
        }

        $transaction_ref = 'CASH-' . time() . '-' . rand(100, 999);
        
        $paymentData = [
            'booking_id' => $booking_id,
            'method' => $method,
            'amount' => $amount,
            'transaction_ref' => $transaction_ref,
            'notes' => $notes,
            'recorded_by' => currentUser()['id'],
            'status' => 'completed'
        ];

        if (Payment::create($paymentData)) {
            // Update booking status to confirmed if full amount is paid
            // In this simple version, we assume any payment confirmed by admin makes it confirmed
            Booking::updateStatus($booking_id, 'confirmed');
            
            sendJson(['success' => true, 'message' => 'تم تسجيل الدفعة بنجاح وتأكيد الحجز.']);
        } else {
            sendJson(['success' => false, 'message' => 'حدث خطأ أثناء تسجيل الدفعة.'], 500);
        }
    }
}
