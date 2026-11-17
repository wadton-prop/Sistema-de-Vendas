<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ItemVenda;
use App\Models\Venda;

class VendaRepository extends Repository
{
    public function inserir(Venda $v): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vendas (cliente_id, data_venda, subtotal, desconto, total, forma_pagamento, status, observacao)
             VALUES (:cliente_id, :data_venda, :subtotal, :desconto, :total, :forma_pagamento, :status, :observacao)'
        );
        $stmt->execute([
            ':cliente_id'      => $v->clienteId,
            ':data_venda'      => $v->dataVenda,
            ':subtotal'        => $v->subtotal,
            ':desconto'        => $v->desconto,
            ':total'           => $v->total,
            ':forma_pagamento' => $v->formaPagamento,
            ':status'          => $v->status,
            ':observacao'      => $v->observacao,
        ]);
        $vendaId = (int) $this->pdo->lastInsertId();

        $stmtItem = $this->pdo->prepare(
            'INSERT INTO itens_venda (venda_id, produto_id, nome_produto, quantidade, preco_unitario, subtotal)
             VALUES (:venda_id, :produto_id, :nome_produto, :quantidade, :preco_unitario, :subtotal)'
        );
        foreach ($v->itens as $item) {
            $stmtItem->execute([
                ':venda_id'       => $vendaId,
                ':produto_id'     => $item->produtoId,
                ':nome_produto'   => $item->nomeProduto,
                ':quantidade'     => $item->quantidade,
                ':preco_unitario' => $item->precoUnitario,
                ':subtotal'       => $item->subtotal,
            ]);
        }
        return $vendaId;
    }

    public function buscarPorId(int $id): ?Venda
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, c.nome AS cliente_nome
             FROM vendas v LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE v.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        if (!$linha) {
            return null;
        }
        $venda = Venda::fromArray($linha);

        $stmt = $this->pdo->prepare('SELECT * FROM itens_venda WHERE venda_id = :id ORDER BY id');
        $stmt->execute([':id' => $id]);
        $venda->itens = array_map([ItemVenda::class, 'fromArray'], $stmt->fetchAll());
        return $venda;
    }

    /**
     * Lista vendas aplicando os filtros do relatório.
     * @param array{data_inicio?: string, data_fim?: string, cliente_id?: int, status?: string, forma_pagamento?: string} $filtros
     * @return Venda[]
     */
    public function listar(array $filtros): array
    {
        [$where, $params] = $this->montarFiltros($filtros);
        $stmt = $this->pdo->prepare(
            "SELECT v.*, c.nome AS cliente_nome
             FROM vendas v LEFT JOIN clientes c ON c.id = v.cliente_id
             {$where}
             ORDER BY v.data_venda DESC, v.id DESC"
        );
        $stmt->execute($params);
        return array_map([Venda::class, 'fromArray'], $stmt->fetchAll());
    }

    /** Totais do período (só vendas concluídas entram no faturamento). */
    public function resumo(array $filtros): array
    {
        $filtros['status'] = 'concluida';
        [$where, $params] = $this->montarFiltros($filtros);
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS quantidade,
                    COALESCE(SUM(v.total), 0) AS faturamento,
                    COALESCE(SUM(v.desconto), 0) AS descontos
             FROM vendas v {$where}"
        );
        $stmt->execute($params);
        $r = $stmt->fetch();
        $qtd = (int) $r['quantidade'];
        $fat = (float) $r['faturamento'];
        return [
            'quantidade'   => $qtd,
            'faturamento'  => $fat,
            'descontos'    => (float) $r['descontos'],
            'ticket_medio' => $qtd > 0 ? $fat / $qtd : 0.0,
        ];
    }

    /** Produtos mais vendidos no período. */
    public function maisVendidos(array $filtros, int $limite = 5): array
    {
        $filtros['status'] = 'concluida';
        [$where, $params] = $this->montarFiltros($filtros);
        $limite = max(1, $limite);
        $stmt = $this->pdo->prepare(
            "SELECT i.nome_produto, SUM(i.quantidade) AS quantidade, SUM(i.subtotal) AS total
             FROM itens_venda i JOIN vendas v ON v.id = i.venda_id
             {$where}
             GROUP BY i.nome_produto
             ORDER BY SUM(i.quantidade) DESC
             LIMIT {$limite}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Faturamento agrupado por dia. */
    public function porDia(array $filtros): array
    {
        $filtros['status'] = 'concluida';
        [$where, $params] = $this->montarFiltros($filtros);
        $stmt = $this->pdo->prepare(
            "SELECT DATE(v.data_venda) AS dia, COUNT(*) AS quantidade, SUM(v.total) AS total
             FROM vendas v {$where}
             GROUP BY DATE(v.data_venda)
             ORDER BY DATE(v.data_venda)"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function atualizar(Venda $v): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE vendas
             SET cliente_id = :cliente_id, forma_pagamento = :forma_pagamento,
                 status = :status, observacao = :observacao
             WHERE id = :id'
        );
        $stmt->execute([
            ':cliente_id'      => $v->clienteId,
            ':forma_pagamento' => $v->formaPagamento,
            ':status'          => $v->status,
            ':observacao'      => $v->observacao,
            ':id'              => $v->id,
        ]);
    }

    public function excluir(int $id): void
    {
        // Os itens são apagados antes (não dependemos do ON DELETE CASCADE
        // porque no SQLite ele só funciona com PRAGMA foreign_keys ligado).
        $this->pdo->prepare('DELETE FROM itens_venda WHERE venda_id = :id')->execute([':id' => $id]);
        $this->pdo->prepare('DELETE FROM vendas WHERE id = :id')->execute([':id' => $id]);
    }

    /** Monta o WHERE dinamicamente, sempre com parâmetros (sem concatenar valores do usuário). */
    private function montarFiltros(array $f): array
    {
        $condicoes = [];
        $params = [];

        if (!empty($f['data_inicio'])) {
            $condicoes[] = 'v.data_venda >= :data_inicio';
            $params[':data_inicio'] = $f['data_inicio'] . ' 00:00:00';
        }
        if (!empty($f['data_fim'])) {
            $condicoes[] = 'v.data_venda <= :data_fim';
            $params[':data_fim'] = $f['data_fim'] . ' 23:59:59';
        }
        if (!empty($f['cliente_id'])) {
            $condicoes[] = 'v.cliente_id = :cliente_id';
            $params[':cliente_id'] = (int) $f['cliente_id'];
        }
        if (!empty($f['status'])) {
            $condicoes[] = 'v.status = :status';
            $params[':status'] = $f['status'];
        }
        if (!empty($f['forma_pagamento'])) {
            $condicoes[] = 'v.forma_pagamento = :forma_pagamento';
            $params[':forma_pagamento'] = $f['forma_pagamento'];
        }

        $where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';
        return [$where, $params];
    }
}
