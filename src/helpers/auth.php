<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/database.php';

function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function currentUser(): ?array
{
    ensureSession();
    return $_SESSION['user'] ?? null;
}

function isAuthenticated(): bool
{
    return currentUser() !== null;
}

function requireAuth(): void
{
    if (!isAuthenticated()) {
        sendJson(['success' => false, 'message' => 'Authentication required.'], 401);
    }
}

function loginUser(array $user): void
{
    ensureSession();
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
}

function logoutUser(): void
{
    ensureSession();
    session_unset();
    session_destroy();
}

function sanitizeInput(?string $value): string
{
    return trim((string) $value);
}

function isValidEmail(string $value): bool
{
    return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
}
