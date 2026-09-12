<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

$id = (int) ($_GET['id'] ?? 0);

$consulta = $pdo->prepare('SELECT * FROM membros WHERE id = :id');
$consulta->execute(['id' => $id]);
$membro = $consulta->fetch();

if (!$membro) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Detalhes do Membro';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1><?= exibir($membro['nome']) ?></h1>
    <a href="index.php" class="botao botao-link">&larr; Voltar para a lista</a>
</div>

<div class="ficha-membro">
    <div class="ficha-secao">
        <h2>Dados pessoais</h2>
        <dl>
            <dt>Nome completo</dt><dd><?= exibir($membro['nome']) ?></dd>
            <dt>Data de nascimento</dt><dd><?= formatarDataBr($membro['data_nascimento']) ?></dd>
            <dt>CPF</dt><dd><?= exibir($membro['cpf']) ?></dd>
            <dt>Telefone</dt><dd><?= exibir($membro['telefone']) ?></dd>
            <dt>E-mail</dt><dd><?= exibir($membro['email']) ?></dd>
        </dl>
    </div>
    <div class="ficha-secao">
        <h2>Endereço</h2>
        <dl>
            <dt>Endereço</dt><dd><?= exibir($membro['endereco']) ?></dd>
            <dt>Bairro</dt><dd><?= exibir($membro['bairro']) ?></dd>
            <dt>Cidade/UF</dt><dd><?= exibir($membro['cidade']) ?><?= $membro['estado'] ? '/' . exibir($membro['estado']) : '' ?></dd>
            <dt>CEP</dt><dd><?= exibir($membro['cep']) ?></dd>
        </dl>
    </div>
    <div class="ficha-secao">
        <h2>Vínculo com a igreja</h2>
        <dl>
            <dt>Data de batismo</dt><dd><?= formatarDataBr($membro['data_batismo']) ?></dd>
            <dt>Função / Cargo</dt><dd><?= exibir($membro['funcao']) ?></dd>
            <dt>Status</dt><dd><span class="selo selo-<?= strtolower($membro['status']) ?>"><?= exibir($membro['status']) ?></span></dd>
            <dt>Cadastrado em</dt><dd><?= formatarDataBr($membro['data_cadastro']) ?></dd>
            <dt>Última atualização</dt><dd><?= formatarDataBr($membro['data_atualizacao']) ?></dd>
        </dl>
    </div>
    <?php if (!empty($membro['observacoes'])): ?>
    <div class="ficha-secao ficha-secao-larga">
        <h2>Observações</h2>
        <p><?= nl2br(exibir($membro['observacoes'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<div class="acoes-formulario">
    <a href="editar.php?id=<?= (int) $membro['id'] ?>" class="botao botao-primario">Editar membro</a>
    <form action="excluir.php" method="post" class="form-excluir" onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($membro['nome'])) ?>');">
        <input type="hidden" name="id" value="<?= (int) $membro['id'] ?>">
        <button type="submit" class="botao botao-perigo">Excluir membro</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
