<?php

require_once __DIR__ . '/../helpers/database.php';

class Property
{
    public static function getAll(array $options = []): array
    {
        $pdo = getPDO();
        $limit = isset($options['limit']) ? (int)$options['limit'] : 12;
        $statusFilter = $options['status'] ?? 'available';
        $typeFilter = $options['type'] ?? null;
        $roomsFilter = $options['rooms'] ?? null;

        $sql = 'SELECT id, title, description, location, image_url, type, rooms, size_m2, floor, price_per_month, status, amenities_json, created_at FROM properties WHERE status = :status';
        $params = [':status' => $statusFilter];

        if ($typeFilter) {
            $sql .= ' AND type = :type';
            $params[':type'] = $typeFilter;
        }

        if ($roomsFilter) {
            $sql .= ' AND rooms >= :rooms';
            $params[':rooms'] = (int)$roomsFilter;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT :limit';
        $params[':limit'] = $limit;

        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
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

    public static function getAvailable(int $limit = 12): array
    {
        return self::getAll(['limit' => $limit, 'status' => 'available']);
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
