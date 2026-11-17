<?php /** @var App\Models\Cliente[] $clientes */ ?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Clientes</h1>
    <a href="<?= url('clientes/criar') ?>" class="btn btn-success">+ Novo cliente</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="r" value="clientes/index">
    <div class="col">
        <input type="search" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Buscar por nome ou CPF">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary">Buscar</button>
        <?php if ($busca !== ''): ?>
            <a href="<?= url('clientes/index') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th class="d-none d-md-table-cell">E-mail</th>
                    <th class="d-none d-md-table-cell">Telefone</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$clientes): ?>
                <tr><td colspan="5" class="text-center text-body-secondary py-4">Nenhum cliente encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= e($c->nome) ?></td>
                    <td><?= e($c->cpf) ?></td>
                    <td class="d-none d-md-table-cell"><?= e($c->email) ?></td>
                    <td class="d-none d-md-table-cell"><?= e($c->telefone) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('relatorios/index', ['cliente_id' => $c->id]) ?>" class="btn btn-outline-secondary btn-sm">Compras</a>
                        <a href="<?= url('clientes/editar', ['id' => $c->id]) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form method="POST" action="<?= url('clientes/excluir') ?>" class="d-inline"
                              onsubmit="return confirm('Excluir o cliente <?= e(addslashes($c->nome)) ?>?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $c->id ?>">
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
