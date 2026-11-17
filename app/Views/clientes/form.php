<?php
/** @var App\Models\Cliente $cliente */
/** @var string[] $erros */
$editando = $cliente->id !== null;
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
<form method="POST" action="<?= url($editando ? 'clientes/atualizar' : 'clientes/salvar') ?>">
    <?= csrf_field() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= (int) $cliente->id ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-8">
            <label for="nome" class="form-label">Nome completo *</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= e($cliente->nome) ?>" required minlength="3" maxlength="150">
        </div>
        <div class="col-md-4">
            <label for="cpf" class="form-label">CPF *</label>
            <input type="text" class="form-control" id="cpf" name="cpf" value="<?= e($cliente->cpf) ?>" required maxlength="14" placeholder="000.000.000-00">
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= e($cliente->email) ?>" maxlength="150">
        </div>
        <div class="col-md-6">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" value="<?= e($cliente->telefone) ?>" maxlength="20" placeholder="(65) 99999-9999">
        </div>
        <div class="col-12">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text" class="form-control" id="endereco" name="endereco" value="<?= e($cliente->endereco) ?>" maxlength="255">
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="<?= url('clientes/index') ?>" class="btn btn-secondary">Voltar</a>
        <button type="submit" class="btn btn-success"><?= $editando ? 'Salvar alterações' : 'Cadastrar' ?></button>
    </div>
</form>
</div></div>
