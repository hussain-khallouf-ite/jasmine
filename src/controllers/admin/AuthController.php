<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/User.php';

class AdminAuthController
{
    public static function handle(string $action, string $method): void
    {
        if ($method !== 'POST' && $action !== 'check') {
            sendJson(['success' => false, 'message' => 'يُسمح فقط بطلبات POST.'], 405);
        }

        switch ($action) {
            case 'login':
                self::login();
                break;
            case 'check':
                self::check();
                break;
            case 'logout':
                self::logout();
                break;
            default:
                sendJson(['success' => false, 'message' => 'إجراء غير معروف.'], 400);
        }
    }

    private static function check(): void
    {
        if (isAuthenticated()) {
            $user = currentUser();
            if ($user['role'] === 'admin') {
                sendJson(['success' => true, 'user' => $user]);
            } else {
                sendJson(['success' => false, 'message' => 'غير مصرح لك بالوصول.'], 403);
            }
        } else {
            sendJson(['success' => false]);
        }
    }

    private static function login(): void
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'البريد الإلكتروني مطلوب.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'أدخل عنوان بريد إلكتروني صالح.';
        }

        if ($password === '') {
            $errors['password'] = 'كلمة المرور مطلوبة.';
        }

        if (!empty($errors)) {
            sendJson(['success' => false, 'errors' => $errors], 422);
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            sendJson(['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صالحة.'], 401);
        }

        if ($user['status'] !== 'active') {
            sendJson(['success' => false, 'message' => 'حسابك غير نشط.'], 403);
        }

        if ($user['role'] !== 'admin') {
            sendJson(['success' => false, 'message' => 'غير مصرح لك بالدخول للوحة التحكم.'], 403);
        }

        loginUser($user);
        sendJson(['success' => true, 'message' => 'تم تسجيل الدخول بنجاح.', 'user' => currentUser()]);
    }

    private static function logout(): void
    {
        logoutUser();
        sendJson(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح.']);
    }
}
