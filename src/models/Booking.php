<?php

require_once __DIR__ . '/../helpers/database.php';

class Booking
{
    public static function create(array $data): ?array
    {
        $pdo = getPDO();
        $sql = 'INSERT INTO bookings (user_id, property_id, type, contract_type, start_date, occupants, total_amount, contract_id, status) 
                VALUES (:user_id, :property_id, :type, :contract_type, :start_date, :occupants, :total_amount, :contract_id, :status)';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => (int)$data['user_id'],
            'property_id' => (int)$data['property_id'],
            'type' => $data['type'],
            'contract_type' => $data['contract_type'] ?? null,
            'start_date' => $data['start_date'],
            'occupants' => isset($data['occupants']) ? (int)$data['occupants'] : null,
            'total_amount' => (float)$data['total_amount'],
            'contract_id' => $data['contract_id'] ?? null,
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

    public static function checkAvailability(int $propertyId, string $type, string $startDate = null, string $contractType = null): bool
    {
        $pdo = getPDO();
        
        if ($type === 'sale') {
            $sql = "SELECT COUNT(*) FROM bookings 
                    WHERE property_id = :property_id 
                    AND (
                        status IN ('confirmed', 'completed') 
                        OR (status = 'pending' AND created_at > NOW() - INTERVAL 1 DAY)
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['property_id' => $propertyId]);
            return (int)$stmt->fetchColumn() === 0;
        }

        // For rent
        $months = $contractType === 'annual' ? 12 : 1;
        $endDate = date('Y-m-d', strtotime("+$months months", strtotime($startDate)));

        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE property_id = :property_id 
                AND (
                    (type = 'sale') 
                    OR 
                    (type = 'rent' AND start_date < :req_end_date AND DATE_ADD(start_date, INTERVAL IF(contract_type = 'annual', 12, 1) MONTH) > :req_start_date)
                )
                AND (
                    status IN ('confirmed', 'completed') 
                    OR (status = 'pending' AND created_at > NOW() - INTERVAL 1 DAY)
                )";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'property_id' => $propertyId,
            'req_start_date' => $startDate,
            'req_end_date' => $endDate
        ]);
        
        return (int)$stmt->fetchColumn() === 0;
    }
}
