<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

$busca  = trim($_GET['busca'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = 'SELECT id, nome, telefone, funcao, status, data_cadastro FROM membros WHERE 1=1';
$parametros = [];

if ($busca !== '') {
    $sql .= ' AND nome LIKE :busca';
    $parametros['busca'] = '%' . $busca . '%';
}

if ($status !== '' && in_array($status, ['Ativo', 'Inativo'], true)) {
    $sql .= ' AND status = :status';
    $parametros['status'] = $status;
}

$sql .= ' ORDER BY nome ASC';

$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$membros = $consulta->fetchAll();

$totalAtivos = $pdo->query("SELECT COUNT(*) FROM membros WHERE status = 'Ativo'")->fetchColumn();
$totalGeral  = $pdo->query('SELECT COUNT(*) FROM membros')->fetchColumn();

$tituloPagina = 'Membros';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1>Membros cadastrados</h1>
    <div class="resumo-cards">
        <div class="card-resumo">
            <span class="numero"><?= (int) $totalGeral ?></span>
            <span class="rotulo">Total de membros</span>
        </div>
        <div class="card-resumo card-resumo-ativo">
            <span class="numero"><?= (int) $totalAtivos ?></span>
            <span class="rotulo">Ativos</span>
        </div>
    </div>
</div>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alerta alerta-sucesso">
        <?php
        $mensagens = [
            'cadastrado' => 'Membro cadastrado com sucesso!',
            'atualizado' => 'Dados do membro atualizados com sucesso!',
            'excluido'   => 'Membro removido com sucesso!',
        ];
        echo htmlspecialchars($mensagens[$_GET['sucesso']] ?? 'Operação realizada com sucesso!');
        ?>
    </div>
<?php endif; ?>

<form class="barra-filtros" method="get" action="index.php">
    <input type="text" name="busca" placeholder="Buscar por nome..." value="<?= htmlspecialchars($busca) ?>">
    <select name="status">
        <option value="">Todos os status</option>
        <option value="Ativo" <?= $status === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
        <option value="Inativo" <?= $status === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
    </select>
    <button type="submit" class="botao botao-secundario">Filtrar</button>
    <?php if ($busca !== '' || $status !== ''): ?>
        <a href="index.php" class="botao botao-link">Limpar</a>
    <?php endif; ?>
</form>

<?php if (count($membros) === 0): ?>
    <div class="estado-vazio">
        <p>Nenhum membro encontrado.</p>
        <a href="cadastrar.php" class="botao botao-primario">Cadastrar o primeiro membro</a>
    </div>
<?php else: ?>
    <div class="tabela-wrapper">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Função</th>
                    <th>Status</th>
                    <th>Cadastrado em</th>
                    <th class="coluna-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($membros as $membro): ?>
                <tr>
                    <td data-rotulo="Nome"><?= exibir($membro['nome']) ?></td>
                    <td data-rotulo="Telefone"><?= exibir($membro['telefone']) ?></td>
                    <td data-rotulo="Função"><?= exibir($membro['funcao']) ?></td>
                    <td data-rotulo="Status">
                        <span class="selo selo-<?= strtolower($membro['status']) ?>"><?= exibir($membro['status']) ?></span>
                    </td>
                    <td data-rotulo="Cadastrado em"><?= formatarDataBr($membro['data_cadastro']) ?></td>
                    <td data-rotulo="Ações" class="coluna-acoes">
                        <a href="visualizar.php?id=<?= (int) $membro['id'] ?>" class="acao acao-ver" title="Visualizar">Ver</a>
                        <a href="editar.php?id=<?= (int) $membro['id'] ?>" class="acao acao-editar" title="Editar">Editar</a>
                        <form action="excluir.php" method="post" class="form-excluir" onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($membro['nome'])) ?>');">
                            <input type="hidden" name="id" value="<?= (int) $membro['id'] ?>">
                            <button type="submit" class="acao acao-excluir">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
