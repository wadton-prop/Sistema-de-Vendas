<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Produto;
use App\Services\Desconto\DescontoFactory;
use App\Services\Desconto\DescontoStrategy;
use RuntimeException;

/**
 * Carrinho de vendas guardado na SESSÃO do usuário.
 * Implementa o CRUD do carrinho:
 *   Create -> adicionar()   Read -> itens()/subtotal()/total()
 *   Update -> atualizar()   Delete -> remover()/limpar()
 *
 * O desconto usa o padrão Strategy (ver App\Services\Desconto).
 */
class CarrinhoService
{
    private const CHAVE = 'carrinho';

    public function __construct()
    {
        if (!isset($_SESSION[self::CHAVE])) {
            $this->limpar();
        }
    }

    public function adicionar(Produto $produto, int $quantidade): void
    {
        if ($quantidade < 1) {
            throw new RuntimeException('A quantidade deve ser pelo menos 1.');
        }
        $atual = $_SESSION[self::CHAVE]['itens'][$produto->id]['quantidade'] ?? 0;
        $nova = $atual + $quantidade;

        if ($nova > $produto->quantidade) {
            throw new RuntimeException("Estoque insuficiente de \"{$produto->nome}\". Disponível: {$produto->quantidade}.");
        }

        $_SESSION[self::CHAVE]['itens'][$produto->id] = [
            'produto_id'     => $produto->id,
            'cod'            => $produto->cod,
            'nome'           => $produto->nome,
            'imagem'         => $produto->imagem,
            'preco_unitario' => $produto->precoUnitario,
            'quantidade'     => $nova,
        ];
    }

    public function atualizar(Produto $produto, int $quantidade): void
    {
        if (!$this->contem((int) $produto->id)) {
            throw new RuntimeException('Este produto não está no carrinho.');
        }
        if ($quantidade < 1) {
            $this->remover((int) $produto->id);
            return;
        }
        if ($quantidade > $produto->quantidade) {
            throw new RuntimeException("Estoque insuficiente de \"{$produto->nome}\". Disponível: {$produto->quantidade}.");
        }
        $_SESSION[self::CHAVE]['itens'][$produto->id]['quantidade'] = $quantidade;
        $_SESSION[self::CHAVE]['itens'][$produto->id]['preco_unitario'] = $produto->precoUnitario;
    }

    public function remover(int $produtoId): void
    {
        unset($_SESSION[self::CHAVE]['itens'][$produtoId]);
    }

    public function limpar(): void
    {
        $_SESSION[self::CHAVE] = [
            'itens'      => [],
            'cliente_id' => null,
            'desconto'   => ['tipo' => 'nenhum', 'valor' => 0.0],
        ];
    }

    public function contem(int $produtoId): bool
    {
        return isset($_SESSION[self::CHAVE]['itens'][$produtoId]);
    }

    /** @return array<int, array{produto_id:int, cod:string, nome:string, imagem:?string, preco_unitario:float, quantidade:int}> */
    public function itens(): array
    {
        return $_SESSION[self::CHAVE]['itens'];
    }

    public function vazio(): bool
    {
        return empty($_SESSION[self::CHAVE]['itens']);
    }

    public function quantidadeItens(): int
    {
        return (int) array_sum(array_column($this->itens(), 'quantidade'));
    }

    public function subtotal(): float
    {
        $soma = 0.0;
        foreach ($this->itens() as $item) {
            $soma += $item['preco_unitario'] * $item['quantidade'];
        }
        return round($soma, 2);
    }

    public function definirCliente(?int $clienteId): void
    {
        $_SESSION[self::CHAVE]['cliente_id'] = $clienteId ?: null;
    }

    public function clienteId(): ?int
    {
        return $_SESSION[self::CHAVE]['cliente_id'];
    }

    /** Valida criando a Strategy (lança exceção se o valor for inválido) e guarda tipo/valor. */
    public function definirDesconto(string $tipo, float $valor): void
    {
        DescontoFactory::criar($tipo, $valor);
        $_SESSION[self::CHAVE]['desconto'] = ['tipo' => $tipo, 'valor' => $valor];
    }

    public function dadosDesconto(): array
    {
        return $_SESSION[self::CHAVE]['desconto'];
    }

    public function estrategiaDesconto(): DescontoStrategy
    {
        $d = $this->dadosDesconto();
        return DescontoFactory::criar($d['tipo'], (float) $d['valor']);
    }

    public function valorDesconto(): float
    {
        return $this->estrategiaDesconto()->calcular($this->subtotal());
    }

    public function total(): float
    {
        return round($this->subtotal() - $this->valorDesconto(), 2);
    }
}
