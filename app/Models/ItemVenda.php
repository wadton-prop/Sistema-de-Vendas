<?php

declare(strict_types=1);

namespace App\Models;

/**
 * MODEL: ItemVenda
 * Guarda nome e preço do produto NO MOMENTO da venda, para o relatório
 * continuar correto mesmo se o produto mudar de preço ou for excluído.
 */
class ItemVenda
{
    public ?int $id = null;
    public ?int $vendaId = null;
    public ?int $produtoId = null;
    public string $nomeProduto = '';
    public int $quantidade = 0;
    public float $precoUnitario = 0.0;
    public float $subtotal = 0.0;

    public static function fromArray(array $d): self
    {
        $i = new self();
        $i->id            = isset($d['id']) ? (int) $d['id'] : null;
        $i->vendaId       = isset($d['venda_id']) ? (int) $d['venda_id'] : null;
        $i->produtoId     = !empty($d['produto_id']) ? (int) $d['produto_id'] : null;
        $i->nomeProduto   = (string) ($d['nome_produto'] ?? '');
        $i->quantidade    = (int) ($d['quantidade'] ?? 0);
        $i->precoUnitario = (float) ($d['preco_unitario'] ?? 0);
        $i->subtotal      = (float) ($d['subtotal'] ?? ($i->quantidade * $i->precoUnitario));
        return $i;
    }
}
