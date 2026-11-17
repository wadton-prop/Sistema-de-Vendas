<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * DESIGN PATTERN: Template View (Two Step View)
 *
 * 1º passo: renderiza a view específica (ex.: clientes/index.php) em um buffer.
 * 2º passo: injeta esse conteúdo dentro do layout padrão (menu, rodapé, CSS).
 * Assim o HTML comum não fica repetido em todas as telas.
 */
final class View
{
    public static function render(string $view, array $dados = [], string $layout = 'layout/base'): void
    {
        $conteudo = self::capturar($view, $dados);
        echo self::capturar($layout, array_merge($dados, ['conteudo' => $conteudo]));
    }

    private static function capturar(string $view, array $dados): string
    {
        $arquivo = APP_PATH . '/Views/' . $view . '.php';
        if (!is_file($arquivo)) {
            throw new RuntimeException("View não encontrada: {$view}");
        }
        extract($dados, EXTR_SKIP);
        ob_start();
        try {
            require $arquivo;
        } catch (\Throwable $e) {
            ob_end_clean(); // descarta o HTML parcial antes de mostrar a página de erro
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
