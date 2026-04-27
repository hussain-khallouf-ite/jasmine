<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public static function handle(string $action, string $method): void
    {
        if ($method !== 'POST') {
            sendJson(['success' => false, 'message' => 'يُسمح فقط بطلبات POST.'], 405);
        }

        switch ($action) {
            case 'login':
                self::login();
                break;
            case 'register':
                self::register();
                break;
            case 'logout':
                self::logout();
                break;
            default:
                sendJson(['success' => false, 'message' => 'إجراء غير معروف.'], 400);
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

        loginUser($user);
        sendJson(['success' => true, 'message' => 'تم تسجيل الدخول بنجاح.', 'user' => currentUser()]);
    }

    private static function register(): void
    {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirm'] ?? '';
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'الاسم مطلوب.';
        }

        if ($email === '') {
            $errors['email'] = 'البريد الإلكتروني مطلوب.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'أدخل عنوان بريد إلكتروني صالح.';
        }

        if ($password === '') {
            $errors['password'] = 'كلمة المرور مطلوبة.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.';
        }

        if ($confirmPassword === '') {
            $errors['password_confirm'] = 'يرجى تأكيد كلمة المرور.';
        } elseif ($password !== $confirmPassword) {
            $errors['password_confirm'] = 'كلمات المرور غير متطابقة.';
        }

        if (User::findByEmail($email)) {
            $errors['email'] = 'البريد الإلكتروني مسجل بالفعل.';
        }

        if (!empty($errors)) {
            sendJson(['success' => false, 'errors' => $errors], 422);
        }

        $newUser = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        if (!$newUser) {
            sendJson(['success' => false, 'message' => 'تعذر إنشاء الحساب. يرجى المحاولة مرة أخرى لاحقاً.'], 500);
        }

        loginUser($newUser);
        sendJson(['success' => true, 'message' => 'تم التسجيل بنجاح.', 'user' => currentUser()]);
    }

    private static function logout(): void
    {
        logoutUser();
        sendJson(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح.']);
    }
}
