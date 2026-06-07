<?php

require_once __DIR__ . '/../helpers/database.php';

class Building
{
    public static function getAll(array $options = []): array
    {
        $pdo = getPDO();
        $limit = isset($options['limit']) ? (int)$options['limit'] : 100;

        $sql = 'SELECT id, name, description, floors, image_url, location, total_area_m2, created_at FROM buildings';
        $params = [];

        $sql .= ' ORDER BY name ASC LIMIT :limit';
        $params[':limit'] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM buildings WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $building = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $building ?: null;
    }

    public static function getProperties(int $buildingId): array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM properties WHERE building_id = :building_id ORDER BY floor ASC, id ASC');
        $stmt->execute(['building_id' => $buildingId]);
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

    public static function create(array $data): ?array
    {
        $pdo = getPDO();
        $sql = 'INSERT INTO buildings (name, description, floors, image_url, location, total_area_m2) 
                VALUES (:name, :description, :floors, :image_url, :location, :total_area_m2)';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'floors' => (int)$data['floors'],
            'image_url' => $data['image_url'] ?? null,
            'location' => $data['location'] ?? 'غير محددة',
            'total_area_m2' => isset($data['total_area_m2']) ? (float)$data['total_area_m2'] : null
        ]);

        $id = (int)$pdo->lastInsertId();
        return self::findById($id);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getPDO();
        $sql = 'UPDATE buildings SET 
                name = :name, description = :description, floors = :floors, 
                image_url = :image_url, location = :location, total_area_m2 = :total_area_m2 
                WHERE id = :id';
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'floors' => (int)$data['floors'],
            'image_url' => $data['image_url'] ?? null,
            'location' => $data['location'] ?? 'غير محددة',
            'total_area_m2' => isset($data['total_area_m2']) ? (float)$data['total_area_m2'] : null
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = getPDO();
        
        // Since we defined foreign key fk_properties_building ON DELETE SET NULL, 
        // properties will have their building_id set to NULL automatically.
        $stmt = $pdo->prepare('DELETE FROM buildings WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
