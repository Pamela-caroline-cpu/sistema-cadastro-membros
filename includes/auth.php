<?php
// Controle de acesso: só quem tem função diferente de "Membro" (liderança/administração)
// consegue autenticar e usar o painel administrativo. Membros comuns não têm senha
// e usam apenas a página pública de autocadastro (cadastro-publico.php).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function administrador_logado(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function exigir_login(): void
{
    if (!administrador_logado()) {
        $destino = basename($_SERVER['SCRIPT_NAME']);
        header('Location: login.php?redirect=' . urlencode($destino));
        exit;
    }
}

function eh_funcao_administrativa(string $funcao): bool
{
    return trim($funcao) !== 'Membro';
}
