<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ItemVenda;
use App\Models\Venda;
use App\Repositories\ClienteRepository;
use App\Repositories\ProdutoRepository;
use App\Repositories\VendaRepository;
use RuntimeException;
use Throwable;

/**
 * DESIGN PATTERN: Facade / Service Layer
 *
 * Finalizar uma venda envolve várias etapas e três repositórios:
 *   1. conferir o cliente;  2. conferir preço e estoque de cada produto;
 *   3. baixar o estoque;    4. gravar a venda e os itens;   5. limpar o carrinho.
 * O Controller chama só $vendaService->finalizar(...) — uma "fachada" simples
 * para todo esse processo, que roda dentro de uma TRANSAÇÃO (ou tudo dá certo,
 * ou nada é gravado).
 */
class VendaService
{
    private VendaRepository $vendas;
    private ProdutoRepository $produtos;
    private ClienteRepository $clientes;

    public function __construct(
        ?VendaRepository $vendas = null,
        ?ProdutoRepository $produtos = null,
        ?ClienteRepository $clientes = null
    ) {
        $this->vendas   = $vendas ?? new VendaRepository();
        $this->produtos = $produtos ?? new ProdutoRepository();
        $this->clientes = $clientes ?? new ClienteRepository();
    }

    public function finalizar(CarrinhoService $carrinho, string $formaPagamento, ?string $observacao = null): int
    {
        if ($carrinho->vazio()) {
            throw new RuntimeException('O carrinho está vazio.');
        }
        if (!array_key_exists($formaPagamento, Venda::FORMAS_PAGAMENTO)) {
            throw new RuntimeException('Selecione uma forma de pagamento válida.');
        }

        $clienteId = $carrinho->clienteId();
        if ($clienteId !== null && $this->clientes->buscarPorId($clienteId) === null) {
            throw new RuntimeException('O cliente selecionado não existe mais.');
        }

        $pdo = $this->vendas->pdo();
        $pdo->beginTransaction();
        try {
            $venda = new Venda();
            $venda->clienteId      = $clienteId;
            $venda->dataVenda      = date('Y-m-d H:i:s');
            $venda->formaPagamento = $formaPagamento;
            $venda->status         = 'concluida';
            $venda->observacao     = $observacao !== '' ? $observacao : null;

            $subtotal = 0.0;
            foreach ($carrinho->itens() as $linha) {
                // Relê o produto do banco: o preço/estoque pode ter mudado desde que entrou no carrinho.
                $produto = $this->produtos->buscarPorId((int) $linha['produto_id']);
                if ($produto === null) {
                    throw new RuntimeException("O produto \"{$linha['nome']}\" não existe mais.");
                }
                if (!$this->produtos->baixarEstoque((int) $produto->id, (int) $linha['quantidade'])) {
                    throw new RuntimeException("Estoque insuficiente de \"{$produto->nome}\". Disponível: {$produto->quantidade}.");
                }

                $item = new ItemVenda();
                $item->produtoId     = $produto->id;
                $item->nomeProduto   = $produto->nome;
                $item->quantidade    = (int) $linha['quantidade'];
                $item->precoUnitario = $produto->precoUnitario;
                $item->subtotal      = round($produto->precoUnitario * $item->quantidade, 2);
                $venda->itens[] = $item;
                $subtotal += $item->subtotal;
            }

            $venda->subtotal = round($subtotal, 2);
            $venda->desconto = $carrinho->estrategiaDesconto()->calcular($venda->subtotal);
            $venda->total    = round($venda->subtotal - $venda->desconto, 2);

            $vendaId = $this->vendas->inserir($venda);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $carrinho->limpar();
        return $vendaId;
    }

    /**
     * Edita os dados de uma venda (cliente, pagamento, status, observação).
     * Cancelar devolve os produtos ao estoque; reativar baixa de novo.
     */
    public function atualizar(Venda $venda, ?int $clienteId, string $formaPagamento, string $status, ?string $observacao): void
    {
        if (!array_key_exists($formaPagamento, Venda::FORMAS_PAGAMENTO)) {
            throw new RuntimeException('Forma de pagamento inválida.');
        }
        if (!array_key_exists($status, Venda::STATUS)) {
            throw new RuntimeException('Status inválido.');
        }
        if ($clienteId !== null && $this->clientes->buscarPorId($clienteId) === null) {
            throw new RuntimeException('Cliente não encontrado.');
        }

        $pdo = $this->vendas->pdo();
        $pdo->beginTransaction();
        try {
            if ($venda->status === 'concluida' && $status === 'cancelada') {
                $this->devolverItens($venda);
            } elseif ($venda->status === 'cancelada' && $status === 'concluida') {
                foreach ($venda->itens as $item) {
                    if ($item->produtoId !== null && !$this->produtos->baixarEstoque($item->produtoId, $item->quantidade)) {
                        throw new RuntimeException("Não há estoque de \"{$item->nomeProduto}\" para reativar a venda.");
                    }
                }
            }

            $venda->clienteId      = $clienteId;
            $venda->formaPagamento = $formaPagamento;
            $venda->status         = $status;
            $venda->observacao     = ($observacao ?? '') !== '' ? $observacao : null;
            $this->vendas->atualizar($venda);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Exclui a venda; se ela estava concluída, os produtos voltam para o estoque. */
    public function excluir(Venda $venda): void
    {
        $pdo = $this->vendas->pdo();
        $pdo->beginTransaction();
        try {
            if ($venda->concluida()) {
                $this->devolverItens($venda);
            }
            $this->vendas->excluir((int) $venda->id);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function devolverItens(Venda $venda): void
    {
        foreach ($venda->itens as $item) {
            if ($item->produtoId !== null) {
                $this->produtos->devolverEstoque($item->produtoId, $item->quantidade);
            }
        }
    }
}
