<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public static function handle(string $action, string $method): void
    {
        if ($method !== 'POST') {
            sendJson(['success' => false, 'message' => 'Only POST requests are allowed.'], 405);
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
                sendJson(['success' => false, 'message' => 'Unknown action.'], 400);
        }
    }

    private static function login(): void
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if (!empty($errors)) {
            sendJson(['success' => false, 'errors' => $errors], 422);
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            sendJson(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if ($user['status'] !== 'active') {
            sendJson(['success' => false, 'message' => 'Your account is not active.'], 403);
        }

        loginUser($user);
        sendJson(['success' => true, 'message' => 'Login successful.', 'user' => currentUser()]);
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
            $errors['name'] = 'Name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if ($confirmPassword === '') {
            $errors['password_confirm'] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (User::findByEmail($email)) {
            $errors['email'] = 'Email is already registered.';
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
            sendJson(['success' => false, 'message' => 'Unable to create account. Please try again later.'], 500);
        }

        loginUser($newUser);
        sendJson(['success' => true, 'message' => 'Registration successful.', 'user' => currentUser()]);
    }

    private static function logout(): void
    {
        logoutUser();
        sendJson(['success' => true, 'message' => 'Logged out successfully.']);
    }
}
