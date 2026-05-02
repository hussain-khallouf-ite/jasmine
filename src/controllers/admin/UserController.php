<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/User.php';

class AdminUserController
{
    public static function handle(string $action, string $method): void
    {
        // Require admin authentication for any action in this controller
        if (!isAuthenticated() || currentUser()['role'] !== 'admin') {
            sendJson(['success' => false, 'message' => 'غير مصرح لك بالوصول.'], 403);
        }

        switch ($action) {
            case 'index':
                if ($method === 'GET') {
                    self::index();
                } else {
                    sendJson(['success' => false, 'message' => 'يُسمح فقط بطلبات GET لهذه العملية.'], 405);
                }
                break;
            case 'updateStatus':
                if ($method === 'POST') {
                    self::updateStatus();
                } else {
                    sendJson(['success' => false, 'message' => 'يُسمح فقط بطلبات POST لهذه العملية.'], 405);
                }
                break;
            default:
                sendJson(['success' => false, 'message' => 'إجراء غير معروف.'], 400);
        }
    }

    private static function index(): void
    {
        $users = User::getAll();
        sendJson(['success' => true, 'users' => $users]);
    }

    private static function updateStatus(): void
    {
        $userId = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($userId <= 0 || !in_array($status, ['active', 'inactive'])) {
            sendJson(['success' => false, 'message' => 'بيانات غير صالحة.'], 400);
        }

        $currentUser = currentUser();
        if ($currentUser['id'] == $userId) {
            sendJson(['success' => false, 'message' => 'لا يمكنك تغيير حالة حسابك الخاص.'], 403);
        }

        $success = User::updateStatus($userId, $status);

        if ($success) {
            sendJson(['success' => true, 'message' => 'تم تحديث حالة المستخدم بنجاح.']);
        } else {
            sendJson(['success' => false, 'message' => 'فشل في تحديث حالة المستخدم.'], 500);
        }
    }
}
