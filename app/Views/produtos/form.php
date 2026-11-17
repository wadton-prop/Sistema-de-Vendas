<?php
/** @var App\Models\Produto $produto */
/** @var string[] $erros */
$editando = $produto->id !== null;
?>
<h1 class="h3 mb-3"><?= e($titulo) ?></h1>

<?php if ($erros): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card"><div class="card-body">
<form method="POST" action="<?= url($editando ? 'produtos/atualizar' : 'produtos/salvar') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= (int) $produto->id ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="cod" class="form-label">COD Produto *</label>
            <input type="text" class="form-control" id="cod" name="cod" value="<?= e($produto->cod) ?>" required maxlength="50">
        </div>
        <div class="col-md-8">
            <label for="nome" class="form-label">Nome do produto *</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= e($produto->nome) ?>" required maxlength="150">
        </div>
        <div class="col-md-6">
            <label for="categoria" class="form-label">Categoria</label>
            <input type="text" class="form-control" id="categoria" name="categoria" value="<?= e($produto->categoria) ?>" maxlength="100">
        </div>
        <div class="col-md-6">
            <label for="validade" class="form-label">Validade</label>
            <input type="date" class="form-control" id="validade" name="validade" value="<?= e($produto->validade ? substr($produto->validade, 0, 10) : '') ?>">
        </div>
        <div class="col-md-4">
            <label for="quantidade" class="form-label">Quantidade em estoque *</label>
            <input type="number" min="0" class="form-control" id="quantidade" name="quantidade" value="<?= (int) $produto->quantidade ?>" required>
        </div>
        <div class="col-md-4">
            <label for="preco_unitario" class="form-label">Preço unitário *</label>
            <input type="number" step="0.01" min="0.01" class="form-control" id="preco_unitario" name="preco_unitario" value="<?= e($produto->precoUnitario ?: '') ?>" required>
        </div>
        <div class="col-md-4">
            <label for="preco_pacote" class="form-label">Preço pacote</label>
            <input type="number" step="0.01" min="0" class="form-control" id="preco_pacote" name="preco_pacote" value="<?= e($produto->precoPacote) ?>">
        </div>
        <div class="col-12">
            <label for="imagem" class="form-label">Imagem do produto <?= $editando ? '(deixe vazio para manter a atual)' : '' ?></label>
            <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
            <?php if ($editando && $produto->imagem): ?>
                <img src="imagens/<?= e(rawurlencode($produto->imagem)) ?>" alt="" class="thumb mt-2">
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="<?= url('produtos/index') ?>" class="btn btn-secondary">Voltar</a>
        <button type="submit" class="btn btn-success"><?= $editando ? 'Salvar alterações' : 'Cadastrar' ?></button>
    </div>
</form>
</div></div>
