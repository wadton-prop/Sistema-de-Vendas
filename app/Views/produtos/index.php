<?php /** @var App\Models\Produto[] $produtos */ ?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Produtos</h1>
    <a href="<?= url('produtos/criar') ?>" class="btn btn-success">+ Novo produto</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="r" value="produtos/index">
    <div class="col">
        <input type="search" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Buscar por nome ou código">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary">Buscar</button>
        <?php if ($busca !== ''): ?>
            <a href="<?= url('produtos/index') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Imagem</th>
                    <th>COD</th>
                    <th>Nome</th>
                    <th class="d-none d-md-table-cell">Categoria</th>
                    <th class="text-end">Preço</th>
                    <th class="text-end">Estoque</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$produtos): ?>
                <tr><td colspan="7" class="text-center text-body-secondary py-4">Nenhum produto encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td>
                        <?php if ($p->imagem): ?>
                            <img src="imagens/<?= e(rawurlencode($p->imagem)) ?>" alt="<?= e($p->nome) ?>" class="thumb">
                        <?php else: ?>
                            <div class="thumb"></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p->cod) ?></td>
                    <td><?= e($p->nome) ?></td>
                    <td class="d-none d-md-table-cell"><?= e($p->categoria) ?></td>
                    <td class="text-end"><?= moeda($p->precoUnitario) ?></td>
                    <td class="text-end">
                        <span class="badge <?= $p->quantidade <= 5 ? 'text-bg-danger' : 'text-bg-secondary' ?>"><?= $p->quantidade ?></span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('produtos/editar', ['id' => $p->id]) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form method="POST" action="<?= url('produtos/excluir') ?>" class="d-inline"
                              onsubmit="return confirm('Excluir o produto <?= e(addslashes($p->nome)) ?>?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p->id ?>">
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
