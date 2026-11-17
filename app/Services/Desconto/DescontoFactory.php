<?php

declare(strict_types=1);

namespace App\Services\Desconto;

/**
 * DESIGN PATTERN: Factory
 *
 * Recebe o tipo escolhido na tela ("percentual", "valor" ou "nenhum")
 * e devolve a Strategy correspondente. O Controller não dá "new" em
 * nenhuma classe de desconto: só pede para a fábrica.
 */
final class DescontoFactory
{
    public const TIPOS = [
        'nenhum'     => 'Sem desconto',
        'percentual' => 'Percentual (%)',
        'valor'      => 'Valor fixo (R$)',
    ];

    public static function criar(string $tipo, float $valor = 0.0): DescontoStrategy
    {
        switch ($tipo) {
            case 'percentual':
                return new DescontoPercentual($valor);
            case 'valor':
                return new DescontoValorFixo($valor);
            default:
                return new SemDesconto();
        }
    }
}
