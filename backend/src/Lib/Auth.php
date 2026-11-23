<?php
declare(strict_types=1);

final class Auth
{
    public static function user(PDO $pdo, array $config): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            Response::error('Token tidak ditemukan.', 401);
        }

        try {
            $payload = Jwt::decode(trim($matches[1]), (string) $config['jwt_secret']);
        } catch (Throwable $e) {
            Response::error('Token tidak valid atau sudah kedaluwarsa.', 401);
        }

        $userId = $payload['sub'] ?? '';

        if (!is_string($userId) || $userId === '') {
            Response::error('Token tidak valid.', 401);
        }

        $user = (new User($pdo))->findById($userId);

        if ($user === null) {
            Response::error('Pengguna tidak ditemukan.', 401);
        }

        return $user;
    }
}