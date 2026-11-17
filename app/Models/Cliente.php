<?php

declare(strict_types=1);

namespace App\Models;

/**
 * MODEL: Cliente
 * Representa um cliente e conhece as próprias regras de validação.
 * Não sabe nada de banco de dados (isso é papel do ClienteRepository).
 */
class Cliente
{
    public ?int $id = null;
    public string $nome = '';
    public string $cpf = '';
    public ?string $email = null;
    public ?string $telefone = null;
    public ?string $endereco = null;
    public ?string $criadoEm = null;

    public static function fromArray(array $d): self
    {
        $c = new self();
        $c->id       = isset($d['id']) && $d['id'] !== '' ? (int) $d['id'] : null;
        $c->nome     = trim((string) ($d['nome'] ?? ''));
        $c->cpf      = self::formatarCpf((string) ($d['cpf'] ?? ''));
        $c->email    = self::nuloSeVazio($d['email'] ?? null);
        $c->telefone = self::nuloSeVazio($d['telefone'] ?? null);
        $c->endereco = self::nuloSeVazio($d['endereco'] ?? null);
        $c->criadoEm = $d['criado_em'] ?? null;
        return $c;
    }

    /** @return string[] lista de erros (vazia = válido) */
    public function validar(): array
    {
        $erros = [];
        if (!preg_match('/^.{3,}$/u', $this->nome)) {
            $erros[] = 'Informe o nome do cliente (mínimo 3 letras).';
        }
        if (!self::cpfValido($this->cpf)) {
            $erros[] = 'CPF inválido.';
        }
        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail inválido.';
        }
        return $erros;
    }

    /** Deixa o CPF no formato 000.000.000-00 */
    public static function formatarCpf(string $cpf): string
    {
        $num = preg_replace('/\D/', '', $cpf);
        if (strlen($num) !== 11) {
            return trim($cpf);
        }
        return substr($num, 0, 3) . '.' . substr($num, 3, 3) . '.' . substr($num, 6, 3) . '-' . substr($num, 9, 2);
    }

    /** Valida os dígitos verificadores do CPF. */
    public static function cpfValido(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }
        return true;
    }

    private static function nuloSeVazio($valor): ?string
    {
        $valor = trim((string) ($valor ?? ''));
        return $valor === '' ? null : $valor;
    }
}
