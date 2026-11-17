<?php

declare(strict_types=1);

namespace App\Services\Desconto;

use InvalidArgumentException;

class DescontoPercentual implements DescontoStrategy
{
    private float $percentual;

    public function __construct(float $percentual)
    {
        if ($percentual < 0 || $percentual > 100) {
            throw new InvalidArgumentException('O percentual de desconto deve estar entre 0 e 100.');
        }
        $this->percentual = $percentual;
    }

    public function calcular(float $subtotal): float
    {
        return round($subtotal * $this->percentual / 100, 2);
    }

    public function descricao(): string
    {
        return rtrim(rtrim(number_format($this->percentual, 2, ',', ''), '0'), ',') . '% de desconto';
    }
}
