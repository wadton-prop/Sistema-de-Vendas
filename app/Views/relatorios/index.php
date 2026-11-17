<?php
/** @var App\Models\Venda[] $vendas */
/** @var array $filtros */
/** @var array $resumo */
$maiorDia = $porDia ? max(array_map(fn ($d) => (float) $d['total'], $porDia)) : 0;
$paramsCsv = array_filter($filtros, fn ($v) => $v !== '' && $v !== null);
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Relatório de vendas</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('relatorios/csv', $paramsCsv) ?>" class="btn btn-outline-success">Exportar CSV</a>
        <a href="<?= url('carrinho/index') ?>" class="btn btn-success">+ Nova venda</a>
    </div>
</div>

<!-- Filtros -->
<form method="GET" class="card mb-3 no-print">
    <div class="card-body row g-2 align-items-end">
        <input type="hidden" name="r" value="relatorios/index">
        <div class="col-6 col-md-2">
            <label for="data_inicio" class="form-label small">De</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= e($filtros['data_inicio']) ?>" class="form-control">
        </div>
        <div class="col-6 col-md-2">
            <label for="data_fim" class="form-label small">Até</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= e($filtros['data_fim']) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="cliente_id" class="form-label small">Cliente</label>
            <select id="cliente_id" name="cliente_id" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= (int) $c->id ?>" <?= (string) $filtros['cliente_id'] === (string) $c->id ? 'selected' : '' ?>><?= e($c->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="forma_pagamento" class="form-label small">Pagamento</label>
            <select id="forma_pagamento" name="forma_pagamento" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($formasPagamento as $k => $rotulo): ?>
                    <option value="<?= e($k) ?>" <?= $filtros['forma_pagamento'] === $k ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-1">
            <label for="status" class="form-label small">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($statusLista as $k => $rotulo): ?>
                    <option value="<?= e($k) ?>" <?= $filtros['status'] === $k ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-primary flex-grow-1">Filtrar</button>
            <a href="<?= url('relatorios/index') ?>" class="btn btn-outline-secondary">Limpar</a>
        </div>
    </div>
</form>

<!-- Resumo (somente vendas concluídas) -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
        <div class="small text-body-secondary">Vendas concluídas</div>
        <div class="fs-4 fw-semibold"><?= (int) $resumo['quantidade'] ?></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
        <div class="small text-body-secondary">Faturamento</div>
        <div class="fs-4 fw-semibold"><?= moeda($resumo['faturamento']) ?></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
        <div class="small text-body-secondary">Ticket médio</div>
        <div class="fs-4 fw-semibold"><?= moeda($resumo['ticket_medio']) ?></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
        <div class="small text-body-secondary">Descontos concedidos</div>
        <div class="fs-4 fw-semibold"><?= moeda($resumo['descontos']) ?></div>
    </div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Faturamento por dia</h2>
            <?php if (!$porDia): ?>
                <p class="text-body-secondary small mb-0">Sem vendas no período.</p>
            <?php endif; ?>
            <?php foreach ($porDia as $d): ?>
                <div class="d-flex align-items-center gap-2 small mb-1">
                    <span class="text-nowrap" style="width: 80px"><?= e(dataBr($d['dia'], false)) ?></span>
                    <div class="flex-grow-1">
                        <div class="barra" style="width: <?= $maiorDia > 0 ? max(2, round((float) $d['total'] / $maiorDia * 100)) : 0 ?>%"></div>
                    </div>
                    <span class="text-nowrap text-end" style="width: 110px"><?= moeda($d['total']) ?></span>
                </div>
            <?php endforeach; ?>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Produtos mais vendidos</h2>
            <?php if (!$maisVendidos): ?>
                <p class="text-body-secondary small mb-0">Sem vendas no período.</p>
            <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Produto</th><th class="text-end">Qtd</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($maisVendidos as $mv): ?>
                        <tr>
                            <td><?= e($mv['nome_produto']) ?></td>
                            <td class="text-end"><?= (int) $mv['quantidade'] ?></td>
                            <td class="text-end"><?= moeda($mv['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<!-- Lista de vendas -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th class="d-none d-md-table-cell">Pagamento</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                    <th class="text-end no-print">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$vendas): ?>
                <tr><td colspan="7" class="text-center text-body-secondary py-4">Nenhuma venda encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($vendas as $v): ?>
                <tr class="<?= $v->concluida() ? '' : 'text-body-secondary' ?>">
                    <td><?= (int) $v->id ?></td>
                    <td class="text-nowrap"><?= e(dataBr($v->dataVenda)) ?></td>
                    <td><?= e($v->clienteNome ?? 'Consumidor final') ?></td>
                    <td class="d-none d-md-table-cell"><?= e($v->formaPagamentoLabel()) ?></td>
                    <td>
                        <span class="badge <?= $v->concluida() ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e($v->statusLabel()) ?></span>
                    </td>
                    <td class="text-end"><?= moeda($v->total) ?></td>
                    <td class="text-end text-nowrap no-print">
                        <a href="<?= url('relatorios/ver', ['id' => $v->id]) ?>" class="btn btn-outline-primary btn-sm">Ver</a>
                        <a href="<?= url('relatorios/editar', ['id' => $v->id]) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form method="POST" action="<?= url('relatorios/excluir') ?>" class="d-inline"
                              onsubmit="return confirm('Excluir a venda #<?= (int) $v->id ?>?<?= $v->concluida() ? ' Os produtos voltarão ao estoque.' : '' ?>');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $v->id ?>">
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
