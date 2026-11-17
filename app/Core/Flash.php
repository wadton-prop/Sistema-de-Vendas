<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Mensagens de uma única exibição ("Cliente salvo com sucesso!").
 * Ficam na sessão até a próxima página e depois somem.
 * Usado junto com o padrão Post/Redirect/Get nos controllers.
 */
final class Flash
{
    public static function set(string $tipo, string $mensagem): void
    {
        $_SESSION['_flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    public static function sucesso(string $mensagem): void
    {
        self::set('success', $mensagem);
    }

    public static function erro(string $mensagem): void
    {
        self::set('danger', $mensagem);
    }

    /** @return array<int, array{tipo: string, mensagem: string}> */
    public static function consumir(): array
    {
        $mensagens = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $mensagens;
    }
}
