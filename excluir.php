<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $comando = $pdo->prepare('DELETE FROM membros WHERE id = :id');
    $comando->execute(['id' => $id]);
}

header('Location: index.php?sucesso=excluido');
exit;
