<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Venda;
use App\Repositories\ClienteRepository;
use App\Repositories\ProdutoRepository;
use App\Services\CarrinhoService;
use App\Services\Desconto\DescontoFactory;
use App\Services\VendaService;
use Throwable;

/**
 * CRUD do Carrinho de Vendas (tela 2 da atividade).
 *   index      -> mostra busca de produtos + carrinho (Read)
 *   adicionar  -> coloca produto no carrinho (Create)
 *   atualizar  -> muda a quantidade de um item (Update)
 *   remover    -> tira um item / limpar -> esvazia (Delete)
 *   opcoes     -> define cliente e desconto (Strategy)
 *   finalizar  -> grava a venda (VendaService / Facade)
 */
class CarrinhoController extends Controller
{
    private CarrinhoService $carrinho;
    private ProdutoRepository $produtos;

    public function __construct()
    {
        $this->carrinho = new CarrinhoService();
        $this->produtos = new ProdutoRepository();
    }

    public function index(): void
    {
        $busca = (string) $this->input('busca', '');
        $estrategia = $this->carrinho->estrategiaDesconto();

        $this->view('carrinho/index', [
            'titulo'          => 'Carrinho de vendas',
            'busca'           => $busca,
            'resultados'      => $busca !== '' ? $this->produtos->listar($busca) : [],
            'itens'           => $this->carrinho->itens(),
            'subtotal'        => $this->carrinho->subtotal(),
            'desconto'        => $this->carrinho->valorDesconto(),
            'descricaoDesc'   => $estrategia->descricao(),
            'dadosDesconto'   => $this->carrinho->dadosDesconto(),
            'total'           => $this->carrinho->total(),
            'clienteId'       => $this->carrinho->clienteId(),
            'clientes'        => (new ClienteRepository())->listar(),
            'tiposDesconto'   => DescontoFactory::TIPOS,
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
        ]);
    }

    public function adicionar(): void
    {
        $this->exigirPost('carrinho/index');
        $produto = $this->produtos->buscarPorId($this->inputInt('produto_id'));
        if (!$produto) {
            Flash::erro('Produto não encontrado.');
            $this->redirecionar('carrinho/index');
        }

        try {
            $this->carrinho->adicionar($produto, max(1, $this->inputInt('quantidade', 1)));
            Flash::sucesso("\"{$produto->nome}\" adicionado ao carrinho.");
        } catch (Throwable $e) {
            Flash::erro($e->getMessage());
        }
        $this->redirecionar('carrinho/index', array_filter(['busca' => (string) $this->input('busca', '')]));
    }

    public function atualizar(): void
    {
        $this->exigirPost('carrinho/index');
        $produto = $this->produtos->buscarPorId($this->inputInt('produto_id'));
        try {
            if (!$produto) {
                $this->carrinho->remover($this->inputInt('produto_id'));
                throw new \RuntimeException('Esse produto não existe mais e foi retirado do carrinho.');
            }
            $this->carrinho->atualizar($produto, $this->inputInt('quantidade'));
            Flash::sucesso('Quantidade atualizada.');
        } catch (Throwable $e) {
            Flash::erro($e->getMessage());
        }
        $this->redirecionar('carrinho/index');
    }

    public function remover(): void
    {
        $this->exigirPost('carrinho/index');
        $this->carrinho->remover($this->inputInt('produto_id'));
        Flash::sucesso('Item removido do carrinho.');
        $this->redirecionar('carrinho/index');
    }

    public function limpar(): void
    {
        $this->exigirPost('carrinho/index');
        $this->carrinho->limpar();
        Flash::sucesso('Carrinho esvaziado.');
        $this->redirecionar('carrinho/index');
    }

    public function opcoes(): void
    {
        $this->exigirPost('carrinho/index');
        try {
            $clienteId = $this->inputInt('cliente_id');
            $this->carrinho->definirCliente($clienteId > 0 ? $clienteId : null);

            $tipo = (string) $this->input('desconto_tipo', 'nenhum');
            $valor = (float) str_replace(',', '.', (string) $this->input('desconto_valor', '0'));
            $this->carrinho->definirDesconto(array_key_exists($tipo, DescontoFactory::TIPOS) ? $tipo : 'nenhum', $valor);
            Flash::sucesso('Cliente e desconto aplicados.');
        } catch (Throwable $e) {
            Flash::erro($e->getMessage());
        }
        $this->redirecionar('carrinho/index');
    }

    public function finalizar(): void
    {
        $this->exigirPost('carrinho/index');
        try {
            $vendaId = (new VendaService())->finalizar(
                $this->carrinho,
                (string) $this->input('forma_pagamento', ''),
                (string) $this->input('observacao', '')
            );
            Flash::sucesso("Venda #{$vendaId} finalizada com sucesso!");
            $this->redirecionar('relatorios/ver', ['id' => $vendaId]);
        } catch (Throwable $e) {
            Flash::erro('Não foi possível finalizar a venda: ' . $e->getMessage());
            $this->redirecionar('carrinho/index');
        }
    }
}
