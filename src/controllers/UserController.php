<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    public static function handle(string $method): void
    {
        requireAuth();

        switch ($method) {
            case 'GET':
                self::getProfile();
                break;
            case 'POST':
                self::updateProfile();
                break;
            default:
                sendJson(['success' => false, 'message' => 'Unsupported request method.'], 405);
        }
    }

    private static function getProfile(): void
    {
        $user = currentUser();
        if (!$user) {
            sendJson(['success' => false, 'message' => 'User not found.'], 404);
        }

        sendJson(['success' => true, 'user' => $user]);
    }

    private static function updateProfile(): void
    {
        $user = currentUser();
        if (!$user) {
            sendJson(['success' => false, 'message' => 'User not found.'], 404);
        }

        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        $existingUser = User::findByEmail($email);
        if ($existingUser && $existingUser['id'] !== $user['id']) {
            $errors['email'] = 'This email is already used by another account.';
        }

        if ($password !== '' && strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if (!empty($errors)) {
            sendJson(['success' => false, 'errors' => $errors], 422);
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ];

        if ($password !== '') {
            $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!User::update($user['id'], $updateData)) {
            sendJson(['success' => false, 'message' => 'Unable to update profile.'], 500);
        }

        $updatedUser = User::findById($user['id']);
        if (!$updatedUser) {
            sendJson(['success' => false, 'message' => 'Unable to refresh user data.'], 500);
        }

        loginUser($updatedUser);
        sendJson(['success' => true, 'message' => 'Profile updated successfully.', 'user' => currentUser()]);
    }
}
