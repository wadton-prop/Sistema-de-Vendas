<?php

declare(strict_types=1);

namespace App\Models;

/**
 * MODEL: Produto
 * Mesmos campos da tabela "produtos" do PDV original do professor.
 */
class Produto
{
    public ?int $id = null;
    public string $cod = '';
    public string $nome = '';
    public ?string $categoria = null;
    public ?string $validade = null;
    public int $quantidade = 0;
    public float $precoUnitario = 0.0;
    public float $precoPacote = 0.0;
    public ?string $imagem = null;

    public static function fromArray(array $d): self
    {
        $p = new self();
        $p->id            = isset($d['id']) && $d['id'] !== '' ? (int) $d['id'] : null;
        $p->cod           = trim((string) ($d['cod'] ?? ''));
        $p->nome          = trim((string) ($d['nome'] ?? ''));
        $p->categoria     = trim((string) ($d['categoria'] ?? '')) ?: null;
        $p->validade      = trim((string) ($d['validade'] ?? '')) ?: null;
        $p->quantidade    = (int) ($d['quantidade'] ?? 0);
        $p->precoUnitario = (float) ($d['preco_unitario'] ?? 0);
        $p->precoPacote   = (float) ($d['preco_pacote'] ?? 0);
        $p->imagem        = $d['imagem'] ?? null;
        return $p;
    }

    /** @return string[] */
    public function validar(): array
    {
        $erros = [];
        if ($this->cod === '') {
            $erros[] = 'Informe o código do produto.';
        }
        if ($this->nome === '') {
            $erros[] = 'Informe o nome do produto.';
        }
        if ($this->quantidade < 0) {
            $erros[] = 'A quantidade não pode ser negativa.';
        }
        if ($this->precoUnitario <= 0) {
            $erros[] = 'O preço unitário deve ser maior que zero.';
        }
        if ($this->precoPacote < 0) {
            $erros[] = 'O preço do pacote não pode ser negativo.';
        }
        return $erros;
    }
}
