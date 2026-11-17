<?php /** @var Throwable $erro */ ?>
<div class="alert alert-danger">
    <h4 class="alert-heading">Ocorreu um erro</h4>
    <p class="mb-2"><?= e($erro->getMessage()) ?></p>
    <?php if ($erro instanceof PDOException): ?>
        <hr>
        <p class="mb-0 small">
            Verifique o arquivo <code>.env</code> (DB_DRIVER, host, usuário e senha) e se as tabelas
            foram criadas com o script da pasta <code>database/</code>. Veja o POP no README.
        </p>
    <?php endif; ?>
</div>
<a href="<?= url('home') ?>" class="btn btn-secondary">Voltar ao início</a>
