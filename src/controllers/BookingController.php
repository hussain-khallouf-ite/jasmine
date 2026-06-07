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

        if (!$data || !isset($data['property_id'])) {
            sendResponse(['error' => 'البيانات غير مكتملة'], 400);
        }

        $propertyId = (int)$data['property_id'];
        $property = Property::findById($propertyId);
        if (!$property) {
            sendResponse(['error'
             => 'الشقة غير موجودة'], 404);
        }

        $type = $property['listing_type'];
        $contractId = 'CON-' . date('Y') . '-' . rand(1000, 9999);

        if ($type === 'rent') {
            if (!isset($data['start_date'], $data['contract_type'], $data['occupants'])) {
                sendResponse(['error' => 'البيانات غير مكتملة لحجز الإيجار'], 400);
            }
            $startDate = $data['start_date'];
            $contractType = $data['contract_type'];
            $occupants = (int)$data['occupants'];

            if (strtotime($startDate) < strtotime('today')) {
                sendResponse(['error' => 'لا يمكن الحجز في تاريخ ماضي'], 400);
            }

            if (!Booking::checkAvailability($propertyId, $type, $startDate, $contractType)) {
                sendResponse(['error' => 'الشقة غير متاحة في التواريخ المحددة (قد يكون هناك حجز مؤقت يرجى المحاولة بعد 24 ساعة)'], 409);
            }

            $totalAmount = $contractType === 'annual' ? $property['price'] * 12 : $property['price'];

            $bookingData = [
                'user_id' => $user['id'],
                'property_id' => $propertyId,
                'type' => 'rent',
                'contract_type' => $contractType,
                'start_date' => $startDate,
                'occupants' => $occupants,
                'total_amount' => $totalAmount,
                'contract_id' => $contractId,
                'status' => 'pending'
            ];
        } else {
            // Sale
            if (!Booking::checkAvailability($propertyId, $type)) {
                sendResponse(['error' => 'الشقة محجوزة مسبقاً للشراء'], 409);
            }

            $bookingData = [
                'user_id' => $user['id'],
                'property_id' => $propertyId,
                'type' => 'sale',
                'contract_type' => null,
                'start_date' => date('Y-m-d'),
                'occupants' => null,
                'total_amount' => $property['price'],
                'contract_id' => $contractId,
                'status' => 'pending'
            ];
        }

        $booking = Booking::create($bookingData);

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
        requireAuth();
        $user = currentUser();
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
