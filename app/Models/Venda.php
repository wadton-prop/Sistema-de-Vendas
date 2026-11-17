<?php

declare(strict_types=1);

namespace App\Models;

/**
 * MODEL: Venda (cabeçalho da venda) + seus itens (ItemVenda).
 */
class Venda
{
    public const FORMAS_PAGAMENTO = [
        'dinheiro' => 'Dinheiro',
        'pix'      => 'PIX',
        'debito'   => 'Cartão de débito',
        'credito'  => 'Cartão de crédito',
    ];

    public const STATUS = [
        'concluida' => 'Concluída',
        'cancelada' => 'Cancelada',
    ];

    public ?int $id = null;
    public ?int $clienteId = null;
    public ?string $clienteNome = null;
    public string $dataVenda = '';
    public float $subtotal = 0.0;
    public float $desconto = 0.0;
    public float $total = 0.0;
    public string $formaPagamento = 'dinheiro';
    public string $status = 'concluida';
    public ?string $observacao = null;

    /** @var ItemVenda[] */
    public array $itens = [];

    public static function fromArray(array $d): self
    {
        $v = new self();
        $v->id             = isset($d['id']) ? (int) $d['id'] : null;
        $v->clienteId      = !empty($d['cliente_id']) ? (int) $d['cliente_id'] : null;
        $v->clienteNome    = $d['cliente_nome'] ?? null;
        $v->dataVenda      = (string) ($d['data_venda'] ?? '');
        $v->subtotal       = (float) ($d['subtotal'] ?? 0);
        $v->desconto       = (float) ($d['desconto'] ?? 0);
        $v->total          = (float) ($d['total'] ?? 0);
        $v->formaPagamento = (string) ($d['forma_pagamento'] ?? 'dinheiro');
        $v->status         = (string) ($d['status'] ?? 'concluida');
        $v->observacao     = $d['observacao'] ?? null;
        return $v;
    }

    public function formaPagamentoLabel(): string
    {
        return self::FORMAS_PAGAMENTO[$this->formaPagamento] ?? $this->formaPagamento;
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function concluida(): bool
    {
        return $this->status === 'concluida';
    }
}
