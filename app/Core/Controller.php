<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe base de todos os Controllers.
 * Concentra o que é comum: renderizar view, redirecionar,
 * ler dados da requisição e validar o token CSRF.
 */
abstract class Controller
{
    protected function view(string $view, array $dados = []): void
    {
        View::render($view, $dados);
    }

    protected function redirecionar(string $rota, array $params = []): void
    {
        header('Location: ' . url($rota, $params));
        exit;
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /** Exige POST com token CSRF válido; caso contrário volta para a rota informada. */
    protected function exigirPost(string $rotaVolta): void
    {
        if (!$this->isPost() || !Csrf::valido($_POST['_csrf'] ?? null)) {
            Flash::erro('Requisição inválida. Recarregue a página e tente novamente.');
            $this->redirecionar($rotaVolta);
        }
    }

    protected function input(string $chave, $padrao = null)
    {
        $valor = $_POST[$chave] ?? $_GET[$chave] ?? $padrao;
        return is_string($valor) ? trim($valor) : $valor;
    }

    protected function inputInt(string $chave, int $padrao = 0): int
    {
        return (int) $this->input($chave, $padrao);
    }
}
