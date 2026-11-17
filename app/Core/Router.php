<?php

declare(strict_types=1);

namespace App\Core;

/**
 * DESIGN PATTERN: Front Controller + Router
 *
 * Toda requisição entra por public/index.php, que chama o Router.
 * O Router olha o parâmetro "r" (ex.: ?r=clientes/editar) e decide
 * qual Controller e qual método (action) vão atender.
 *
 * Usamos ?r= em vez de URLs "bonitas" para funcionar em qualquer
 * hospedagem sem precisar configurar .htaccess / mod_rewrite.
 */
final class Router
{
    /** @var array<string, array{0: class-string, 1: string}> */
    private array $rotas = [];

    public function registrar(string $rota, string $controller, string $acao): void
    {
        $this->rotas[$rota] = [$controller, $acao];
    }

    public function despachar(string $rota): void
    {
        $rota = trim($rota, '/');
        if ($rota === '') {
            $rota = 'home';
        }

        if (!isset($this->rotas[$rota])) {
            http_response_code(404);
            View::render('layout/404', ['titulo' => 'Página não encontrada', 'rota' => $rota]);
            return;
        }

        [$classe, $acao] = $this->rotas[$rota];
        $controller = new $classe();
        $controller->$acao();
    }
}
