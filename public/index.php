<?php
/**
 * FRONT CONTROLLER — único ponto de entrada da aplicação.
 * Toda URL é index.php?r=<controller>/<acao>
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\CarrinhoController;
use App\Controllers\ClienteController;
use App\Controllers\HomeController;
use App\Controllers\ProdutoController;
use App\Controllers\RelatorioController;
use App\Core\Router;
use App\Core\View;

$router = new Router();

$router->registrar('home', HomeController::class, 'index');

// 1 - Cadastro de clientes
$router->registrar('clientes/index',     ClienteController::class, 'index');
$router->registrar('clientes/criar',     ClienteController::class, 'criar');
$router->registrar('clientes/salvar',    ClienteController::class, 'salvar');
$router->registrar('clientes/editar',    ClienteController::class, 'editar');
$router->registrar('clientes/atualizar', ClienteController::class, 'atualizar');
$router->registrar('clientes/excluir',   ClienteController::class, 'excluir');

// 2 - Carrinho de vendas
$router->registrar('carrinho/index',     CarrinhoController::class, 'index');
$router->registrar('carrinho/adicionar', CarrinhoController::class, 'adicionar');
$router->registrar('carrinho/atualizar', CarrinhoController::class, 'atualizar');
$router->registrar('carrinho/remover',   CarrinhoController::class, 'remover');
$router->registrar('carrinho/limpar',    CarrinhoController::class, 'limpar');
$router->registrar('carrinho/opcoes',    CarrinhoController::class, 'opcoes');
$router->registrar('carrinho/finalizar', CarrinhoController::class, 'finalizar');

// 3 - Relatórios de vendas
$router->registrar('relatorios/index',     RelatorioController::class, 'index');
$router->registrar('relatorios/ver',       RelatorioController::class, 'ver');
$router->registrar('relatorios/editar',    RelatorioController::class, 'editar');
$router->registrar('relatorios/atualizar', RelatorioController::class, 'atualizar');
$router->registrar('relatorios/excluir',   RelatorioController::class, 'excluir');
$router->registrar('relatorios/csv',       RelatorioController::class, 'csv');

// Produtos (PDV original, reorganizado em MVC)
$router->registrar('produtos/index',     ProdutoController::class, 'index');
$router->registrar('produtos/criar',     ProdutoController::class, 'criar');
$router->registrar('produtos/salvar',    ProdutoController::class, 'salvar');
$router->registrar('produtos/editar',    ProdutoController::class, 'editar');
$router->registrar('produtos/atualizar', ProdutoController::class, 'atualizar');
$router->registrar('produtos/excluir',   ProdutoController::class, 'excluir');

try {
    $router->despachar((string) ($_GET['r'] ?? 'home'));
} catch (Throwable $e) {
    http_response_code(500);
    error_log((string) $e);
    View::render('layout/erro', ['titulo' => 'Erro', 'erro' => $e]);
}
