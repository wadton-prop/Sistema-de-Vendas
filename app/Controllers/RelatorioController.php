<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Venda;
use App\Repositories\ClienteRepository;
use App\Repositories\VendaRepository;
use App\Services\VendaService;
use Throwable;

/**
 * CRUD de Relatórios de Vendas (tela 3 da atividade).
 *   index     -> lista com filtros + totais (Read)
 *   ver       -> detalhe/cupom de uma venda (Read)
 *   editar    -> formulário | atualizar -> grava pagamento/status/obs (Update)
 *   excluir   -> apaga a venda e devolve o estoque (Delete)
 *   csv       -> exporta o relatório filtrado
 * (As vendas são CRIADAS pelo carrinho -> CarrinhoController::finalizar.)
 */
class RelatorioController extends Controller
{
    private VendaRepository $repo;

    public function __construct()
    {
        $this->repo = new VendaRepository();
    }

    public function index(): void
    {
        $filtros = $this->filtros();
        $this->view('relatorios/index', [
            'titulo'          => 'Relatório de vendas',
            'filtros'         => $filtros,
            'vendas'          => $this->repo->listar($filtros),
            'resumo'          => $this->repo->resumo($filtros),
            'maisVendidos'    => $this->repo->maisVendidos($filtros),
            'porDia'          => $this->repo->porDia($filtros),
            'clientes'        => (new ClienteRepository())->listar(),
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
            'statusLista'     => Venda::STATUS,
        ]);
    }

    public function ver(): void
    {
        $venda = $this->buscarOuVoltar();
        $this->view('relatorios/ver', ['titulo' => "Venda #{$venda->id}", 'venda' => $venda]);
    }

    public function editar(): void
    {
        $venda = $this->buscarOuVoltar();
        $this->view('relatorios/editar', [
            'titulo'          => "Editar venda #{$venda->id}",
            'venda'           => $venda,
            'clientes'        => (new ClienteRepository())->listar(),
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
            'statusLista'     => Venda::STATUS,
        ]);
    }

    public function atualizar(): void
    {
        $this->exigirPost('relatorios/index');
        $venda = $this->buscarOuVoltar();
        $clienteId = $this->inputInt('cliente_id');

        try {
            (new VendaService())->atualizar(
                $venda,
                $clienteId > 0 ? $clienteId : null,
                (string) $this->input('forma_pagamento', ''),
                (string) $this->input('status', ''),
                (string) $this->input('observacao', '')
            );
            Flash::sucesso("Venda #{$venda->id} atualizada.");
            $this->redirecionar('relatorios/ver', ['id' => $venda->id]);
        } catch (Throwable $e) {
            Flash::erro('Não foi possível atualizar: ' . $e->getMessage());
            $this->redirecionar('relatorios/editar', ['id' => $venda->id]);
        }
    }

    public function excluir(): void
    {
        $this->exigirPost('relatorios/index');
        $venda = $this->buscarOuVoltar();
        try {
            (new VendaService())->excluir($venda);
            Flash::sucesso("Venda #{$venda->id} excluída" . ($venda->concluida() ? ' e produtos devolvidos ao estoque.' : '.'));
        } catch (Throwable $e) {
            Flash::erro('Não foi possível excluir: ' . $e->getMessage());
        }
        $this->redirecionar('relatorios/index');
    }

    public function csv(): void
    {
        $vendas = $this->repo->listar($this->filtros());

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio-vendas-' . date('Ymd-His') . '.csv"');

        $saida = fopen('php://output', 'w');
        fwrite($saida, "\xEF\xBB\xBF"); // BOM para o Excel abrir os acentos corretamente
        fputcsv($saida, ['Venda', 'Data', 'Cliente', 'Pagamento', 'Status', 'Subtotal', 'Desconto', 'Total'], ';');
        foreach ($vendas as $v) {
            fputcsv($saida, [
                $v->id,
                dataBr($v->dataVenda),
                $v->clienteNome ?? 'Consumidor final',
                $v->formaPagamentoLabel(),
                $v->statusLabel(),
                number_format($v->subtotal, 2, ',', ''),
                number_format($v->desconto, 2, ',', ''),
                number_format($v->total, 2, ',', ''),
            ], ';');
        }
        fclose($saida);
        exit;
    }

    private function filtros(): array
    {
        $data = fn (string $k) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->input($k, '')) ? (string) $this->input($k) : '';
        $status = (string) $this->input('status', '');
        $forma = (string) $this->input('forma_pagamento', '');

        return [
            'data_inicio'     => $data('data_inicio'),
            'data_fim'        => $data('data_fim'),
            'cliente_id'      => $this->inputInt('cliente_id') ?: '',
            'status'          => array_key_exists($status, Venda::STATUS) ? $status : '',
            'forma_pagamento' => array_key_exists($forma, Venda::FORMAS_PAGAMENTO) ? $forma : '',
        ];
    }

    private function buscarOuVoltar(): Venda
    {
        $venda = $this->repo->buscarPorId($this->inputInt('id'));
        if (!$venda) {
            Flash::erro('Venda não encontrada.');
            $this->redirecionar('relatorios/index');
        }
        return $venda;
    }
}
