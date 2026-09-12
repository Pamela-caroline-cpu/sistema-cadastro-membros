<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$consulta = $pdo->prepare('SELECT * FROM membros WHERE id = :id');
$consulta->execute(['id' => $id]);
$membro = $consulta->fetch();

if (!$membro) {
    header('Location: index.php');
    exit;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['nome', 'data_nascimento', 'cpf', 'telefone', 'email', 'endereco', 'bairro', 'cidade', 'estado', 'cep', 'data_batismo', 'funcao', 'status', 'observacoes'];
    foreach ($campos as $campo) {
        $membro[$campo] = trim($_POST[$campo] ?? '');
    }

    if ($membro['nome'] === '') {
        $erros[] = 'O nome completo é obrigatório.';
    }
    if ($membro['email'] !== '' && !filter_var($membro['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido ou deixe o campo em branco.';
    }

    // senha é opcional - só quem tem função != Membro e senha preenchida loga
    $funcaoAdministrativa = eh_funcao_administrativa($membro['funcao'] ?: 'Membro');
    $novaSenha = trim($_POST['senha'] ?? '');
    if ($funcaoAdministrativa) {
        if ($novaSenha !== '' && strlen($novaSenha) < 6) {
            $erros[] = 'A senha de acesso deve ter pelo menos 6 caracteres.';
        }
    }

    if (empty($erros)) {
        $senhaParaSalvar = $funcaoAdministrativa
            ? ($novaSenha !== '' ? password_hash($novaSenha, PASSWORD_DEFAULT) : $membro['senha'])
            : null; // voltou a ser Membro, tira a senha

        $sql = 'UPDATE membros SET
                    nome = :nome, data_nascimento = :data_nascimento, cpf = :cpf, telefone = :telefone,
                    email = :email, endereco = :endereco, bairro = :bairro, cidade = :cidade,
                    estado = :estado, cep = :cep, data_batismo = :data_batismo, funcao = :funcao,
                    status = :status, senha = :senha, observacoes = :observacoes
                WHERE id = :id';

        try {
            $comando = $pdo->prepare($sql);
            $comando->execute([
                'nome'            => $membro['nome'],
                'data_nascimento' => $membro['data_nascimento'] ?: null,
                'cpf'             => $membro['cpf'] ?: null,
                'telefone'        => $membro['telefone'] ?: null,
                'email'           => $membro['email'] ?: null,
                'endereco'        => $membro['endereco'] ?: null,
                'bairro'          => $membro['bairro'] ?: null,
                'cidade'          => $membro['cidade'] ?: null,
                'estado'          => $membro['estado'] ?: null,
                'cep'             => $membro['cep'] ?: null,
                'data_batismo'    => $membro['data_batismo'] ?: null,
                'funcao'          => $membro['funcao'] ?: 'Membro',
                'status'          => $membro['status'] ?: 'Ativo',
                'senha'           => $senhaParaSalvar,
                'observacoes'     => $membro['observacoes'] ?: null,
                'id'              => $id,
            ]);

            header('Location: index.php?sucesso=atualizado');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erros[] = 'Já existe outro membro cadastrado com este CPF.';
            } else {
                $erros[] = 'Erro ao atualizar o cadastro: ' . $e->getMessage();
            }
        }
    }
}

$tituloPagina = 'Editar Membro';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1>Editar membro</h1>
    <a href="index.php" class="botao botao-link">&larr; Voltar para a lista</a>
</div>

<form method="post" action="editar.php?id=<?= $id ?>" class="formulario-membro" novalidate>
    <input type="hidden" name="id" value="<?= $id ?>">
    <?php require __DIR__ . '/includes/formulario_membro.php'; ?>
    <div class="acoes-formulario">
        <a href="index.php" class="botao botao-secundario">Cancelar</a>
        <button type="submit" class="botao botao-primario">Salvar alterações</button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
