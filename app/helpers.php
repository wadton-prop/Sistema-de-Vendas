<?php
/**
 * Funções auxiliares usadas nas Views.
 */

declare(strict_types=1);

/** Escapa texto para HTML (evita XSS). */
function e($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Monta a URL de uma rota: url('clientes/editar', ['id' => 3]) */
function url(string $rota, array $params = []): string
{
    $query = array_merge(['r' => $rota], $params);
    return 'index.php?' . http_build_query($query);
}

/** Formata valor em reais: 1234.5 -> R$ 1.234,50 */
function moeda($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/** Formata data/hora do banco para o padrão brasileiro. */
function dataBr(?string $data, bool $comHora = true): string
{
    if (empty($data)) {
        return '';
    }
    $ts = strtotime($data);
    return $ts ? date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $ts) : $data;
}

/** Campo oculto com o token CSRF, para usar dentro dos formulários POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(App\Core\Csrf::token()) . '">';
}
