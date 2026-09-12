<?php
// controle de sessão dos admins (função != Membro)

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
