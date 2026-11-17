<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Proteção contra CSRF: todo formulário POST envia um token
 * que precisa bater com o token guardado na sessão.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function valido(?string $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }
}
