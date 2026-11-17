<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cliente;

class ClienteRepository extends Repository
{
    /** @return Cliente[] */
    public function listar(string $busca = ''): array
    {
        if ($busca === '') {
            $stmt = $this->pdo->query('SELECT * FROM clientes ORDER BY nome');
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM clientes
                 WHERE LOWER(nome) LIKE LOWER(:nome) OR cpf LIKE :cpf
                 ORDER BY nome'
            );
            $stmt->execute([':nome' => "%{$busca}%", ':cpf' => "%{$busca}%"]);
        }
        return array_map([Cliente::class, 'fromArray'], $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Cliente
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? Cliente::fromArray($linha) : null;
    }

    /** Verifica se já existe outro cliente com o mesmo CPF. */
    public function cpfEmUso(string $cpf, ?int $ignorarId = null): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM clientes WHERE cpf = :cpf');
        $stmt->execute([':cpf' => $cpf]);
        $id = $stmt->fetchColumn();
        return $id !== false && (int) $id !== $ignorarId;
    }

    public function inserir(Cliente $c): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes (nome, cpf, email, telefone, endereco, criado_em)
             VALUES (:nome, :cpf, :email, :telefone, :endereco, :criado_em)'
        );
        $stmt->execute([
            ':nome'      => $c->nome,
            ':cpf'       => $c->cpf,
            ':email'     => $c->email,
            ':telefone'  => $c->telefone,
            ':endereco'  => $c->endereco,
            ':criado_em' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(Cliente $c): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clientes
             SET nome = :nome, cpf = :cpf, email = :email, telefone = :telefone, endereco = :endereco
             WHERE id = :id'
        );
        $stmt->execute([
            ':nome'     => $c->nome,
            ':cpf'      => $c->cpf,
            ':email'    => $c->email,
            ':telefone' => $c->telefone,
            ':endereco' => $c->endereco,
            ':id'       => $c->id,
        ]);
    }

    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function quantidadeVendas(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM vendas WHERE cliente_id = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function contar(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
    }
}
