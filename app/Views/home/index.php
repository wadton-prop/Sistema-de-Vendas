<h1 class="h3 mb-4">Ponto de Venda</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat h-100"><div class="card-body">
            <div class="text-body-secondary small">Vendas hoje</div>
            <div class="fs-3 fw-semibold"><?= (int) $resumoHoje['quantidade'] ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat h-100"><div class="card-body">
            <div class="text-body-secondary small">Faturamento hoje</div>
            <div class="fs-3 fw-semibold"><?= moeda($resumoHoje['faturamento']) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat h-100"><div class="card-body">
            <div class="text-body-secondary small">Clientes</div>
            <div class="fs-3 fw-semibold"><?= (int) $totalClientes ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat h-100"><div class="card-body">
            <div class="text-body-secondary small">Produtos (<?= (int) $estoqueBaixo ?> com estoque baixo)</div>
            <div class="fs-3 fw-semibold"><?= (int) $totalProdutos ?></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <a class="card atalho h-100 text-decoration-none" href="<?= url('clientes/index') ?>">
            <div class="card-body">
                <h2 class="h5">1. Cadastro de clientes</h2>
                <p class="text-body-secondary mb-0">Cadastrar, buscar, editar e excluir clientes.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a class="card atalho h-100 text-decoration-none" href="<?= url('carrinho/index') ?>">
            <div class="card-body">
                <h2 class="h5">2. Carrinho de vendas</h2>
                <p class="text-body-secondary mb-0">Adicionar produtos, aplicar desconto e finalizar a venda.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a class="card atalho h-100 text-decoration-none" href="<?= url('relatorios/index') ?>">
            <div class="card-body">
                <h2 class="h5">3. Relatórios de vendas</h2>
                <p class="text-body-secondary mb-0">Filtrar por período, ver totais, editar, cancelar e exportar CSV.</p>
            </div>
        </a>
    </div>
</div>
