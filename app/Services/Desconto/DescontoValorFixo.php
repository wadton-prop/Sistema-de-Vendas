<?php

declare(strict_types=1);

namespace App\Services\Desconto;

use InvalidArgumentException;

class DescontoValorFixo implements DescontoStrategy
{
    private float $valor;

    public function __construct(float $valor)
    {
        if ($valor < 0) {
            throw new InvalidArgumentException('O valor do desconto não pode ser negativo.');
        }
        $this->valor = $valor;
    }

    public function calcular(float $subtotal): float
    {
        // O desconto nunca pode deixar o total negativo.
        return round(min($this->valor, $subtotal), 2);
    }

    public function descricao(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.') . ' de desconto';
    }
}
