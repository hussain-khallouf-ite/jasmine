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

        $sql = 'SELECT id, title, description, location, image_url, type, rooms, size_m2, floor, price_per_month, status, amenities_json, created_at FROM properties';
        $params = [];
        $where = [];

        if ($statusFilter !== 'all') {
            $where[] = 'status = :status';
            $params[':status'] = $statusFilter;
        }

        if ($typeFilter) {
            $where[] = 'type = :type';
            $params[':type'] = $typeFilter;
        }

        if ($roomsFilter) {
            $where[] = 'rooms >= :rooms';
            $params[':rooms'] = (int)$roomsFilter;
        }

        if (count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
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
        
        if ($property) {
            if (!empty($property['amenities_json'])) {
                $decoded = json_decode($property['amenities_json'], true);
                $property['amenities'] = is_array($decoded) ? $decoded : [];
            } else {
                $property['amenities'] = [];
            }
        }
        
        return $property ?: null;
    }

    public static function create(array $data): ?array
    {
        $pdo = getPDO();
        $sql = 'INSERT INTO properties (title, description, location, image_url, type, rooms, size_m2, floor, price_per_month, status, amenities_json) 
                VALUES (:title, :description, :location, :image_url, :type, :rooms, :size_m2, :floor, :price_per_month, :status, :amenities_json)';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'location' => $data['location'] ?? 'غير محددة',
            'image_url' => $data['image_url'] ?? '',
            'type' => $data['type'] ?? 'residential',
            'rooms' => (int)$data['rooms'],
            'size_m2' => (float)$data['size_m2'],
            'floor' => (int)$data['floor'],
            'price_per_month' => (float)$data['price_per_month'],
            'status' => $data['status'] ?? 'available',
            'amenities_json' => isset($data['amenities']) ? json_encode($data['amenities']) : null
        ]);

        $id = (int)$pdo->lastInsertId();
        return self::findById($id);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getPDO();
        $sql = 'UPDATE properties SET 
                title = :title, description = :description, location = :location, 
                image_url = :image_url, type = :type, rooms = :rooms, 
                size_m2 = :size_m2, floor = :floor, price_per_month = :price_per_month, 
                status = :status, amenities_json = :amenities_json 
                WHERE id = :id';
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'location' => $data['location'] ?? 'غير محددة',
            'image_url' => $data['image_url'] ?? '',
            'type' => $data['type'] ?? 'residential',
            'rooms' => (int)$data['rooms'],
            'size_m2' => (float)$data['size_m2'],
            'floor' => (int)$data['floor'],
            'price_per_month' => (float)$data['price_per_month'],
            'status' => $data['status'] ?? 'available',
            'amenities_json' => isset($data['amenities']) ? json_encode($data['amenities']) : null
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM properties WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
