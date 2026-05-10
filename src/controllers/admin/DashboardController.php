<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/database.php';

class AdminDashboardController
{
    public static function handle(string $method): void
    {
        if (!isAuthenticated() || currentUser()['role'] !== 'admin') {
            sendJson(['success' => false, 'message' => 'غير مصرح لك بالوصول.'], 403);
        }

        if ($method === 'GET') {
            self::getStats();
        } else {
            sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function getStats(): void
    {
        $pdo = getPDO();

        // Total properties
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM properties');
        $totalProperties = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Active users
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE status = 'active' AND role = 'customer'");
        $activeUsers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // New bookings (Using properties marked as 'reserved' since bookings table is unused)
        $stmt = $pdo->query("SELECT COUNT(*) as reserved FROM properties WHERE status = 'reserved'");
        $reservedProperties = (int)$stmt->fetch(PDO::FETCH_ASSOC)['reserved'];

        sendJson([
            'success' => true,
            'stats' => [
                'total_properties' => $totalProperties,
                'active_users' => $activeUsers,
                'new_bookings' => $reservedProperties
            ]
        ]);
    }
}
