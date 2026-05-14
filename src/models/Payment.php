<?php

require_once __DIR__ . '/../helpers/database.php';

class Payment
{
    public static function create(array $data): bool
    {
        $pdo = getPDO();
        $sql = 'INSERT INTO payments (booking_id, method, amount, transaction_ref, notes, recorded_by, status) 
                VALUES (:booking_id, :method, :amount, :transaction_ref, :notes, :recorded_by, :status)';
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'booking_id' => $data['booking_id'],
            'method' => $data['method'],
            'amount' => $data['amount'],
            'transaction_ref' => $data['transaction_ref'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $data['recorded_by'] ?? null,
            'status' => $data['status'] ?? 'completed'
        ]);
    }

    public static function getAll(): array
    {
        $pdo = getPDO();
        $sql = 'SELECT p.*, b.contract_id, b.type as booking_type, u.name as customer_name, admin.name as admin_name, prop.title as property_title
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON b.user_id = u.id
                JOIN properties prop ON b.property_id = prop.id
                LEFT JOIN users admin ON p.recorded_by = admin.id
                ORDER BY p.created_at DESC';
        
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
