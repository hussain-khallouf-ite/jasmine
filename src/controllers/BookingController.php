<?php

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

class BookingController
{
    public function create()
    {
        requireAuth();
        $user = currentUser();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['property_id'], $data['start_date'], $data['end_date'], $data['occupants'])) {
            sendResponse(['error' => 'البيانات غير مكتملة'], 400);
        }

        $propertyId = (int)$data['property_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $occupants = (int)$data['occupants'];

        if (strtotime($startDate) >= strtotime($endDate)) {
            sendResponse(['error' => 'تاريخ المغادرة يجب أن يكون بعد تاريخ الوصول'], 400);
        }

        if (strtotime($startDate) < strtotime('today')) {
            sendResponse(['error' => 'لا يمكن الحجز في تاريخ ماضي'], 400);
        }

        $property = Property::findById($propertyId);
        if (!$property) {
            sendResponse(['error' => 'الشقة غير موجودة'], 404);
        }

        if (!Booking::checkAvailability($propertyId, $startDate, $endDate)) {
            sendResponse(['error' => 'الشقة غير متاحة في التواريخ المحددة (قد يكون هناك حجز مؤقت يرجى المحاولة بعد 24 ساعة)'], 409);
        }

        // Calculate total amount based on months
        $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
        $pricePerDay = $property['price_per_month'] / 30;
        $totalAmount = $days * $pricePerDay;

        $booking = Booking::create([
            'user_id' => $user['id'],
            'property_id' => $propertyId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'occupants' => $occupants,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        if ($booking) {
            sendResponse(['message' => 'تم مبدئياً حجز الشقة بنجاح. يرجى إتمام الدفع.', 'booking' => $booking], 201);
        } else {
            sendResponse(['error' => 'حدث خطأ أثناء الحجز'], 500);
        }
    }

    public function listUserBookings()
    {
        requireAuth();
        $user = currentUser();
        $bookings = Booking::findByUserId($user['id']);
        sendResponse($bookings);
    }

    public function confirm()
    {
        requireAuth();
        $user = currentUser();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['booking_id'])) {
            sendResponse(['error' => 'البيانات غير مكتملة'], 400);
        }

        $booking = Booking::findById((int)$data['booking_id']);
        if (!$booking || $booking['user_id'] !== $user['id']) {
            sendResponse(['error' => 'الحجز غير موجود'], 404);
        }

        if ($booking['status'] !== 'pending') {
            sendResponse(['error' => 'لا يمكن تأكيد هذا الحجز'], 400);
        }

        if (Booking::updateStatus($booking['id'], 'confirmed')) {
            sendResponse(['message' => 'تم تأكيد الحجز بنجاح (محاكاة الدفع)']);
        } else {
            sendResponse(['error' => 'حدث خطأ أثناء التحديث'], 500);
        }
    }

    public function cancel()
    {
        requireAuth();
        $user = currentUser();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['booking_id'])) {
            sendResponse(['error' => 'البيانات غير مكتملة'], 400);
        }

        $booking = Booking::findById((int)$data['booking_id']);
        if (!$booking || $booking['user_id'] !== $user['id']) {
            sendResponse(['error' => 'الحجز غير موجود'], 404);
        }

        if ($booking['status'] === 'completed') {
            sendResponse(['error' => 'لا يمكن إلغاء حجز مكتمل'], 400);
        }

        if (Booking::updateStatus($booking['id'], 'cancelled')) {
            sendResponse(['message' => 'تم إلغاء الحجز بنجاح']);
        } else {
            sendResponse(['error' => 'حدث خطأ أثناء التحديث'], 500);
        }
    }
    
    public function getDetails()
    {
        $user = requireAuth();
        $bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$bookingId) {
             sendResponse(['error' => 'رقم الحجز مطلوب'], 400);
        }
        
        $booking = Booking::findById($bookingId);
        if (!$booking || $booking['user_id'] !== $user['id']) {
            sendResponse(['error' => 'الحجز غير موجود'], 404);
        }
        
        sendResponse($booking);
    }
}
