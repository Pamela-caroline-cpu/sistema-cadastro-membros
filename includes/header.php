<?php if (!isset($tituloPagina)) { $tituloPagina = 'Sistema de Cadastro de Membros'; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($tituloPagina) ?> | Primeira Igreja Quadrangular</title>
<link rel="icon" href="<?= isset($caminhoBase) ? $caminhoBase : '' ?>assets/img/logo.jpg">
<link rel="stylesheet" href="<?= isset($caminhoBase) ? $caminhoBase : '' ?>assets/css/style.css">
</head>
<body>
<header class="topo">
    <div class="topo-conteudo">
        <a href="index.php" class="marca">
            <span class="marca-icone"><img src="<?= isset($caminhoBase) ? $caminhoBase : '' ?>assets/img/logo.jpg" alt="Logo da igreja"></span>
            <span>
                Sistema de Cadastro de Membros
                <small>Primeira Igreja Quadrangular &mdash; Dois Vizinhos/PR</small>
            </span>
        </a>
        <nav class="menu">
            <a href="index.php">Membros</a>
            <a href="qrcode.php">QR Code</a>
            <?php $admin = administrador_logado(); if ($admin): ?>
                <span class="usuario-logado">Olá, <?= htmlspecialchars(explode(' ', $admin['nome'])[0]) ?></span>
                <a href="logout.php">Sair</a>
            <?php endif; ?>
            <a href="cadastrar.php" class="botao-menu">+ Novo Membro</a>
        </nav>
    </div>
    <div class="faixa-marca"><span class="f-vermelho"></span><span class="f-amarelo"></span><span class="f-azul"></span><span class="f-roxo"></span></div>
</header>
<main class="conteudo">
