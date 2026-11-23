<?php
declare(strict_types=1);

final class Ulid
{
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    public static function generate(): string
    {
        $time = (int) floor(microtime(true) * 1000);
        $timePart = '';

        for ($i = 0; $i < 10; $i++) {
            $timePart = self::ALPHABET[$time % 32] . $timePart;
            $time = intdiv($time, 32);
        }

        $random = random_bytes(10);
        $bits = '';

        for ($i = 0; $i < 10; $i++) {
            $bits .= str_pad(decbin(ord($random[$i])), 8, '0', STR_PAD_LEFT);
        }

        $randomPart = '';

        for ($i = 0; $i < 80; $i += 5) {
            $randomPart .= self::ALPHABET[bindec(substr($bits, $i, 5))];
        }

        return $timePart . $randomPart;
    }
}