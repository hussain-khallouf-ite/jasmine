<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/database.php';

class AdminReportController
{
    public static function handle(string $method): void
    {
        if (!isAuthenticated() || currentUser()['role'] !== 'admin') {
            sendJson(['success' => false, 'message' => 'غير مصرح لك بالوصول.'], 403);
        }

        if ($method === 'GET') {
            self::getReportData();
        } else {
            sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function getReportData(): void
    {
        $pdo = getPDO();

        // Revenue by type
        $stmt = $pdo->query("
            SELECT type, SUM(total_amount) as revenue 
            FROM bookings 
            WHERE status IN ('confirmed', 'completed') 
            GROUP BY type
        ");
        $revenueByType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Booking counts by type
        $stmt = $pdo->query("
            SELECT type, COUNT(*) as count 
            FROM bookings 
            GROUP BY type
        ");
        $countsByType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent bookings
        $stmt = $pdo->query("
            SELECT b.*, p.title as property_title, u.name as user_name 
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            JOIN users u ON b.user_id = u.id
            ORDER BY b.created_at DESC
            LIMIT 10
        ");
        $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Property status overview
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count 
            FROM properties 
            GROUP BY status
        ");
        $propertyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJson([
            'success' => true,
            'data' => [
                'revenue' => $revenueByType,
                'bookings_count' => $countsByType,
                'recent_bookings' => $recentBookings,
                'property_stats' => $propertyStats
            ]
        ]);
    }
}
