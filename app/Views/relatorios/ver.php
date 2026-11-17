<?php /** @var App\Models\Venda $venda */ ?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3 no-print">
    <a href="<?= url('relatorios/index') ?>" class="btn btn-secondary">&larr; Relatório</a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary">Imprimir</button>
        <a href="<?= url('relatorios/editar', ['id' => $venda->id]) ?>" class="btn btn-warning">Editar</a>
        <form method="POST" action="<?= url('relatorios/excluir') ?>"
              onsubmit="return confirm('Excluir a venda #<?= (int) $venda->id ?>?<?= $venda->concluida() ? ' Os produtos voltarão ao estoque.' : '' ?>');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $venda->id ?>">
            <button class="btn btn-danger">Excluir</button>
        </form>
    </div>
</div>

<div class="card mx-auto" style="max-width: 720px">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h1 class="h4 mb-1">Venda #<?= (int) $venda->id ?></h1>
                <div class="text-body-secondary"><?= e(dataBr($venda->dataVenda)) ?></div>
            </div>
            <span class="badge fs-6 <?= $venda->concluida() ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e($venda->statusLabel()) ?></span>
        </div>

        <dl class="row mb-3">
            <dt class="col-sm-4">Cliente</dt>
            <dd class="col-sm-8"><?= e($venda->clienteNome ?? 'Consumidor final') ?></dd>
            <dt class="col-sm-4">Pagamento</dt>
            <dd class="col-sm-8"><?= e($venda->formaPagamentoLabel()) ?></dd>
            <?php if ($venda->observacao): ?>
                <dt class="col-sm-4">Observação</dt>
                <dd class="col-sm-8"><?= e($venda->observacao) ?></dd>
            <?php endif; ?>
        </dl>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>Produto</th><th class="text-end">Qtd</th><th class="text-end">Unit.</th><th class="text-end">Subtotal</th></tr>
                </thead>
                <tbody>
                <?php foreach ($venda->itens as $item): ?>
                    <tr>
                        <td><?= e($item->nomeProduto) ?></td>
                        <td class="text-end"><?= $item->quantidade ?></td>
                        <td class="text-end"><?= moeda($item->precoUnitario) ?></td>
                        <td class="text-end"><?= moeda($item->subtotal) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <dl class="row totais mb-0 ms-auto" style="max-width: 320px">
            <dt class="col-6">Subtotal</dt><dd class="col-6"><?= moeda($venda->subtotal) ?></dd>
            <dt class="col-6">Desconto</dt><dd class="col-6 text-danger">- <?= moeda($venda->desconto) ?></dd>
            <dt class="col-6 fs-5 text-body">Total</dt><dd class="col-6 fs-5 fw-bold"><?= moeda($venda->total) ?></dd>
        </dl>
    </div>
</div>
