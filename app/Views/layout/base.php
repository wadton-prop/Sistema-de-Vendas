<?php
/** @var string $conteudo  HTML da view renderizada (injetado pela classe View) */
/** @var string $titulo */
$rotaAtual = (string) ($_GET['r'] ?? 'home');
$itensCarrinho = (int) array_sum(array_column($_SESSION['carrinho']['itens'] ?? [], 'quantidade'));
$menu = [
    'home'       => 'Início',
    'clientes'   => 'Clientes',
    'produtos'   => 'Produtos',
    'carrinho'   => 'Carrinho',
    'relatorios' => 'Relatórios',
];
$ativo = explode('/', $rotaAtual)[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'PDV') ?> · PDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('home') ?>">PDV</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($menu as $chave => $rotulo): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $ativo === $chave ? 'active' : '' ?>"
                           href="<?= url($chave === 'home' ? 'home' : "{$chave}/index") ?>">
                            <?= e($rotulo) ?>
                            <?php if ($chave === 'carrinho' && $itensCarrinho > 0): ?>
                                <span class="badge rounded-pill text-bg-warning"><?= $itensCarrinho ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?php foreach (App\Core\Flash::consumir() as $msg): ?>
        <div class="alert alert-<?= e($msg['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($msg['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endforeach; ?>

    <?= $conteudo ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
