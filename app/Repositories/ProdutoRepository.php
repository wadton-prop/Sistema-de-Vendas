<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Produto;

class ProdutoRepository extends Repository
{
    /** Busca por nome OU código (no original a busca era só por nome). @return Produto[] */
    public function listar(string $busca = ''): array
    {
        if ($busca === '') {
            $stmt = $this->pdo->query('SELECT * FROM produtos ORDER BY nome');
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM produtos
                 WHERE LOWER(nome) LIKE LOWER(:nome) OR cod LIKE :cod
                 ORDER BY nome'
            );
            $stmt->execute([':nome' => "%{$busca}%", ':cod' => "%{$busca}%"]);
        }
        return array_map([Produto::class, 'fromArray'], $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Produto
    {
        $stmt = $this->pdo->prepare('SELECT * FROM produtos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? Produto::fromArray($linha) : null;
    }

    public function inserir(Produto $p): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produtos (cod, nome, categoria, validade, quantidade, preco_unitario, preco_pacote, imagem)
             VALUES (:cod, :nome, :categoria, :validade, :quantidade, :preco_unitario, :preco_pacote, :imagem)'
        );
        $stmt->execute($this->parametros($p));
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(Produto $p): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produtos
             SET cod = :cod, nome = :nome, categoria = :categoria, validade = :validade,
                 quantidade = :quantidade, preco_unitario = :preco_unitario,
                 preco_pacote = :preco_pacote, imagem = :imagem
             WHERE id = :id'
        );
        $stmt->execute($this->parametros($p) + [':id' => $p->id]);
    }

    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Baixa o estoque SOMENTE se houver quantidade suficiente
     * (a condição fica no próprio UPDATE, então não dá para vender o que não tem).
     */
    public function baixarEstoque(int $id, int $quantidade): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produtos SET quantidade = quantidade - :qtd
             WHERE id = :id AND quantidade >= :minimo'
        );
        $stmt->execute([':qtd' => $quantidade, ':id' => $id, ':minimo' => $quantidade]);
        return $stmt->rowCount() === 1;
    }

    public function devolverEstoque(int $id, int $quantidade): void
    {
        $stmt = $this->pdo->prepare('UPDATE produtos SET quantidade = quantidade + :qtd WHERE id = :id');
        $stmt->execute([':qtd' => $quantidade, ':id' => $id]);
    }

    public function contar(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    }

    public function contarEstoqueBaixo(int $limite = 5): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM produtos WHERE quantidade <= :limite');
        $stmt->execute([':limite' => $limite]);
        return (int) $stmt->fetchColumn();
    }

    private function parametros(Produto $p): array
    {
        return [
            ':cod'            => $p->cod,
            ':nome'           => $p->nome,
            ':categoria'      => $p->categoria,
            ':validade'       => $p->validade,
            ':quantidade'     => $p->quantidade,
            ':preco_unitario' => $p->precoUnitario,
            ':preco_pacote'   => $p->precoPacote,
            ':imagem'         => $p->imagem,
        ];
    }
}
