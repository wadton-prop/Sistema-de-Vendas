<?php
/** @var App\Models\Produto[] $resultados */
/** @var array $itens */
/** @var App\Models\Cliente[] $clientes */
?>
<h1 class="h3 mb-3">Carrinho de vendas</h1>

<div class="row g-4">
    <!-- COLUNA ESQUERDA: busca de produtos (Create do carrinho) -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5">Adicionar produto</h2>
                <form method="GET" class="input-group mb-3">
                    <input type="hidden" name="r" value="carrinho/index">
                    <input type="search" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Nome ou código do produto" autofocus>
                    <button class="btn btn-primary">Buscar</button>
                </form>

                <?php if ($busca !== '' && !$resultados): ?>
                    <p class="text-body-secondary mb-0">Nenhum produto encontrado para "<?= e($busca) ?>".</p>
                <?php endif; ?>

                <?php foreach ($resultados as $p): ?>
                    <div class="d-flex align-items-center gap-2 border-top py-2">
                        <?php if ($p->imagem): ?>
                            <img src="imagens/<?= e(rawurlencode($p->imagem)) ?>" alt="" class="thumb">
                        <?php else: ?>
                            <div class="thumb"></div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= e($p->nome) ?></div>
                            <div class="small text-body-secondary">
                                COD <?= e($p->cod) ?> · <?= moeda($p->precoUnitario) ?> · estoque <?= $p->quantidade ?>
                            </div>
                        </div>
                        <?php if ($p->quantidade > 0): ?>
                            <form method="POST" action="<?= url('carrinho/adicionar') ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="produto_id" value="<?= (int) $p->id ?>">
                                <input type="hidden" name="busca" value="<?= e($busca) ?>">
                                <input type="number" name="quantidade" value="1" min="1" max="<?= $p->quantidade ?>" class="form-control form-control-sm qtd-input" aria-label="Quantidade">
                                <button class="btn btn-success btn-sm">Adicionar</button>
                            </form>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Sem estoque</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- COLUNA DIREITA: itens do carrinho (Read / Update / Delete) -->
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h5 mb-0">Itens</h2>
                    <?php if ($itens): ?>
                        <form method="POST" action="<?= url('carrinho/limpar') ?>" onsubmit="return confirm('Esvaziar o carrinho?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Esvaziar</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (!$itens): ?>
                    <p class="text-body-secondary mb-0">O carrinho está vazio. Busque um produto ao lado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Unit.</th>
                                    <th>Qtd</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><?= e($item['nome']) ?><div class="small text-body-secondary">COD <?= e($item['cod']) ?></div></td>
                                    <td class="text-end"><?= moeda($item['preco_unitario']) ?></td>
                                    <td>
                                        <form method="POST" action="<?= url('carrinho/atualizar') ?>" class="d-flex gap-1">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="produto_id" value="<?= (int) $item['produto_id'] ?>">
                                            <input type="number" name="quantidade" value="<?= (int) $item['quantidade'] ?>" min="0" class="form-control form-control-sm qtd-input" aria-label="Quantidade">
                                            <button class="btn btn-outline-primary btn-sm" title="Atualizar quantidade">OK</button>
                                        </form>
                                    </td>
                                    <td class="text-end"><?= moeda($item['preco_unitario'] * $item['quantidade']) ?></td>
                                    <td class="text-end">
                                        <form method="POST" action="<?= url('carrinho/remover') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="produto_id" value="<?= (int) $item['produto_id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" title="Remover">&times;</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($itens): ?>
        <!-- Cliente + desconto (Strategy) -->
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5">Cliente e desconto</h2>
                <form method="POST" action="<?= url('carrinho/opcoes') ?>" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-md-5">
                        <label for="cliente_id" class="form-label small">Cliente</label>
                        <select name="cliente_id" id="cliente_id" class="form-select">
                            <option value="0">Consumidor final</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int) $c->id ?>" <?= $clienteId === $c->id ? 'selected' : '' ?>>
                                    <?= e($c->nome) ?> (<?= e($c->cpf) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="desconto_tipo" class="form-label small">Desconto</label>
                        <select name="desconto_tipo" id="desconto_tipo" class="form-select">
                            <?php foreach ($tiposDesconto as $tipo => $rotulo): ?>
                                <option value="<?= e($tipo) ?>" <?= $dadosDesconto['tipo'] === $tipo ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="desconto_valor" class="form-label small">Valor</label>
                        <input type="number" step="0.01" min="0" name="desconto_valor" id="desconto_valor" value="<?= e($dadosDesconto['valor']) ?>" class="form-control">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-outline-primary">Aplicar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Totais + finalizar (VendaService / Facade) -->
        <div class="card">
            <div class="card-body">
                <dl class="row totais mb-3">
                    <dt class="col-6">Subtotal</dt>
                    <dd class="col-6"><?= moeda($subtotal) ?></dd>
                    <dt class="col-6">Desconto <span class="small">(<?= e($descricaoDesc) ?>)</span></dt>
                    <dd class="col-6 text-danger">- <?= moeda($desconto) ?></dd>
                    <dt class="col-6 fs-5 text-body">Total</dt>
                    <dd class="col-6 fs-4 fw-bold"><?= moeda($total) ?></dd>
                </dl>

                <form method="POST" action="<?= url('carrinho/finalizar') ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <div class="col-md-5">
                        <label for="forma_pagamento" class="form-label small">Forma de pagamento *</label>
                        <select name="forma_pagamento" id="forma_pagamento" class="form-select" required>
                            <?php foreach ($formasPagamento as $chave => $rotulo): ?>
                                <option value="<?= e($chave) ?>"><?= e($rotulo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label for="observacao" class="form-label small">Observação</label>
                        <input type="text" name="observacao" id="observacao" class="form-control" maxlength="255">
                    </div>
                    <div class="col-12 d-grid mt-3">
                        <button class="btn btn-success btn-lg">Finalizar venda</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
