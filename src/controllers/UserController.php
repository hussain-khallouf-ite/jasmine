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
                sendJson(['success' => false, 'message' => 'طريقة الطلب غير مدعومة.'], 405);
        }
    }

    private static function getProfile(): void
    {
        $user = currentUser();
        if (!$user) {
            sendJson(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        sendJson(['success' => true, 'user' => $user]);
    }

    private static function updateProfile(): void
    {
        $user = currentUser();
        if (!$user) {
            sendJson(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'الاسم مطلوب.';
        }

        if ($email === '') {
            $errors['email'] = 'البريد الإلكتروني مطلوب.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'أدخل عنوان بريد إلكتروني صالح.';
        }

        $existingUser = User::findByEmail($email);
        if ($existingUser && $existingUser['id'] !== $user['id']) {
            $errors['email'] = 'هذا البريد الإلكتروني مستخدم بالفعل بواسطة حساب آخر.';
        }

        if ($password !== '' && strlen($password) < 8) {
            $errors['password'] = 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.';
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
            sendJson(['success' => false, 'message' => 'تعذر تحديث الملف الشخصي.'], 500);
        }

        $updatedUser = User::findById($user['id']);
        if (!$updatedUser) {
            sendJson(['success' => false, 'message' => 'تعذر تحديث بيانات المستخدم.'], 500);
        }

        loginUser($updatedUser);
        sendJson(['success' => true, 'message' => 'تم تحديث الملف الشخصي بنجاح.', 'user' => currentUser()]);
    }
}
