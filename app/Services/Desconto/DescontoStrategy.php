<?php

declare(strict_types=1);

namespace App\Services\Desconto;

/**
 * DESIGN PATTERN: Strategy
 *
 * Interface comum para todas as formas de calcular desconto no carrinho.
 * O carrinho só chama $estrategia->calcular($subtotal) e não precisa saber
 * se é percentual, valor fixo ou nenhum desconto.
 * Para criar um desconto novo (ex.: "leve 3 pague 2") basta criar
 * outra classe que implemente esta interface — sem mexer no carrinho.
 */
interface DescontoStrategy
{
    /** Retorna o VALOR do desconto em reais (nunca maior que o subtotal). */
    public function calcular(float $subtotal): float;

    /** Texto exibido na tela, ex.: "10% de desconto". */
    public function descricao(): string;
}
