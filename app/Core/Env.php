<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lê o arquivo .env (CHAVE=valor) e disponibiliza os valores para a aplicação.
 * Assim as senhas ficam fora do código-fonte e fora do GitHub.
 */
final class Env
{
    private static array $valores = [];

    public static function carregar(string $caminho): void
    {
        if (!is_file($caminho)) {
            return;
        }
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
                continue;
            }
            [$chave, $valor] = explode('=', $linha, 2);
            self::$valores[trim($chave)] = trim(trim($valor), "\"'");
        }
    }

    public static function get(string $chave, ?string $padrao = null): ?string
    {
        if (array_key_exists($chave, self::$valores) && self::$valores[$chave] !== '') {
            return self::$valores[$chave];
        }
        $doAmbiente = getenv($chave);
        return ($doAmbiente !== false && $doAmbiente !== '') ? $doAmbiente : $padrao;
    }
}
