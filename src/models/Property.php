<?php

require_once __DIR__ . '/../helpers/database.php';

class Property
{
    public static function getAll(array $options = []): array
    {
        $pdo = getPDO();
        $limit = isset($options['limit']) ? (int)$options['limit'] : 12;
        $statusFilter = $options['status'] ?? 'available';

        $stmt = $pdo->prepare(
            'SELECT id, title, description, location, image_url, type, rooms, size_m2, floor, price_per_month, status, amenities_json, created_at FROM properties WHERE status = :status ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($property) {
            if (!empty($property['amenities_json'])) {
                $decoded = json_decode($property['amenities_json'], true);
                $property['amenities'] = is_array($decoded) ? $decoded : [];
            } else {
                $property['amenities'] = [];
            }

            unset($property['amenities_json']);
            return $property;
        }, $properties);
    }

    public static function findById(int $id): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM properties WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        return $property ?: null;
    }
}
