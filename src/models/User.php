<?php

require_once __DIR__ . '/../helpers/database.php';

class User
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function create(array $data): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (:name, :email, :phone, :password_hash, :role, :status)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'customer',
            'status' => $data['status'] ?? 'active',
        ]);

        $id = (int)$pdo->lastInsertId();
        return self::findById($id);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getPDO();
        $fields = ['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? ''];
        $set = 'name = :name, email = :email, phone = :phone';

        if (!empty($data['password_hash'])) {
            $set .= ', password_hash = :password_hash';
            $fields['password_hash'] = $data['password_hash'];
        }

        $fields['id'] = $id;
        $sql = "UPDATE users SET {$set} WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($fields);
    }

    public static function getAll(): array
    {
        $pdo = getPDO();
        $stmt = $pdo->query('SELECT id, name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
