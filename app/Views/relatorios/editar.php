<?php /** @var App\Models\Venda $venda */ ?>
<h1 class="h3 mb-3"><?= e($titulo) ?></h1>

<div class="alert alert-info">
    Itens e valores não podem ser alterados depois da venda. Para corrigir itens, cancele ou exclua
    esta venda (o estoque é devolvido) e faça uma nova pelo carrinho.
</div>

<div class="card"><div class="card-body">
<form method="POST" action="<?= url('relatorios/atualizar') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $venda->id ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label for="cliente_id" class="form-label">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-select">
                <option value="0">Consumidor final</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= (int) $c->id ?>" <?= $venda->clienteId === $c->id ? 'selected' : '' ?>><?= e($c->nome) ?> (<?= e($c->cpf) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="forma_pagamento" class="form-label">Pagamento</label>
            <select name="forma_pagamento" id="forma_pagamento" class="form-select">
                <?php foreach ($formasPagamento as $k => $rotulo): ?>
                    <option value="<?= e($k) ?>" <?= $venda->formaPagamento === $k ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <?php foreach ($statusLista as $k => $rotulo): ?>
                    <option value="<?= e($k) ?>" <?= $venda->status === $k ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Cancelar devolve os produtos ao estoque.</div>
        </div>
        <div class="col-12">
            <label for="observacao" class="form-label">Observação</label>
            <input type="text" name="observacao" id="observacao" value="<?= e($venda->observacao) ?>" class="form-control" maxlength="255">
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="<?= url('relatorios/ver', ['id' => $venda->id]) ?>" class="btn btn-secondary">Voltar</a>
        <button class="btn btn-success">Salvar alterações</button>
    </div>
</form>
</div></div>
