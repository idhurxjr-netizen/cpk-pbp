<?php
declare(strict_types=1);

require_once __DIR__ . '/../../libs/php-jwt/src/JWT.php';
require_once __DIR__ . '/../../libs/php-jwt/src/Key.php';
require_once __DIR__ . '/../../libs/php-jwt/src/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/../../libs/php-jwt/src/ExpiredException.php';
require_once __DIR__ . '/../../libs/php-jwt/src/SignatureInvalidException.php';
require_once __DIR__ . '/../../libs/php-jwt/src/BeforeValidException.php';

final class Jwt
{
    public static function encode(array $payload, string $secret): string
    {
        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }

    public static function decode(string $token, string $secret): array
    {
        $decoded = \Firebase\JWT\JWT::decode(
            $token,
            new \Firebase\JWT\Key($secret, 'HS256')
        );

        return (array) $decoded;
    }
}