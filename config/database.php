<?php
// Configuração de conexão com o banco de dados (PDO + MySQL)
// Ajuste $usuario/$senha caso seu MySQL local exija credenciais diferentes.

$host    = 'localhost';
$porta   = '3307'; // MariaDB local roda nesta porta (3306 já está em uso pelo MySQL80 desta máquina)
$banco   = 'igreja_membros';
$usuario = 'root';
$senha   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset={$charset}";

$opcoes = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados. Verifique se o MySQL está em execução e se o banco "igreja_membros" foi criado (veja database/schema.sql). Detalhe: ' . $e->getMessage());
}
