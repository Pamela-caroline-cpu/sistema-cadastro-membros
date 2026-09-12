<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (administrador_logado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
if (!preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $redirect)) {
    $redirect = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        $consulta = $pdo->prepare('SELECT id, nome, funcao, senha FROM membros WHERE email = :email LIMIT 1');
        $consulta->execute(['email' => $email]);
        $membro = $consulta->fetch();

        if (
            $membro
            && eh_funcao_administrativa($membro['funcao'])
            && $membro['senha']
            && password_verify($senha, $membro['senha'])
        ) {
            $_SESSION['admin'] = [
                'id'     => $membro['id'],
                'nome'   => $membro['nome'],
                'funcao' => $membro['funcao'],
            ];
            header('Location: ' . $redirect);
            exit;
        }

        $erro = 'E-mail ou senha inválidos, ou este usuário não tem acesso administrativo.';
    }
}

$tituloPagina = 'Entrar';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrar | Primeira Igreja Quadrangular</title>
<link rel="icon" href="assets/img/logo.jpg">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="pagina-login">
    <div class="cartao-login">
        <img src="assets/img/logo.jpg" alt="Logo da igreja" class="logo-login">
        <h1>Área administrativa</h1>
        <p class="subtitulo-login">Primeira Igreja Quadrangular &mdash; Dois Vizinhos/PR</p>

        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="formulario-login">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="botao botao-primario botao-largo">Entrar</button>
        </form>

        <p class="ajuda-login">Acesso restrito à liderança/administração. Membros não precisam de login — use o <a href="cadastro-publico.php">formulário de autocadastro</a>.</p>
    </div>
</div>
</body>
</html>
