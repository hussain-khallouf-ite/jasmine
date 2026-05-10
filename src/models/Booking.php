<?php

require_once __DIR__ . '/../helpers/database.php';

class Booking
{
    public static function create(array $data): ?array
    {
        $pdo = getPDO();
        $sql = 'INSERT INTO bookings (user_id, property_id, start_date, end_date, occupants, total_amount, status) 
                VALUES (:user_id, :property_id, :start_date, :end_date, :occupants, :total_amount, :status)';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => (int)$data['user_id'],
            'property_id' => (int)$data['property_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'occupants' => (int)$data['occupants'],
            'total_amount' => (float)$data['total_amount'],
            'status' => $data['status'] ?? 'pending'
        ]);

        $id = (int)$pdo->lastInsertId();
        return self::findById($id);
    }

    public static function findById(int $id): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('
            SELECT b.*, p.title as property_title, p.image_url as property_image 
            FROM bookings b 
            JOIN properties p ON b.property_id = p.id 
            WHERE b.id = :id LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $booking ?: null;
    }

    public static function findByUserId(int $userId): array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('
            SELECT b.*, p.title as property_title, p.image_url as property_image, p.location as property_location 
            FROM bookings b 
            JOIN properties p ON b.property_id = p.id 
            WHERE b.user_id = :user_id 
            ORDER BY b.created_at DESC
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE bookings SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function checkAvailability(int $propertyId, string $startDate, string $endDate): bool
    {
        $pdo = getPDO();
        // A property is unavailable if there's a booking that overlaps with the requested dates
        // Overlap condition: existing.start_date < requested.end_date AND existing.end_date > requested.start_date
        // Status must be 'confirmed', 'completed', OR ('pending' AND created within the last 24 hours)
        
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE property_id = :property_id 
                AND start_date < :end_date 
                AND end_date > :start_date
                AND (
                    status IN ('confirmed', 'completed') 
                    OR (status = 'pending' AND created_at > NOW() - INTERVAL 1 DAY)
                )";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'property_id' => $propertyId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $count = (int)$stmt->fetchColumn();
        return $count === 0; // Available if count is 0
    }
}
