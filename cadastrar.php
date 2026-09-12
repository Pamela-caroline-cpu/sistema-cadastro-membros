<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

$membro = [
    'nome' => '', 'data_nascimento' => '', 'cpf' => '', 'telefone' => '', 'email' => '',
    'endereco' => '', 'bairro' => '', 'cidade' => '', 'estado' => '', 'cep' => '',
    'data_batismo' => '', 'funcao' => 'Membro', 'status' => 'Ativo', 'senha' => '', 'observacoes' => '',
];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($membro as $campo => $valorPadrao) {
        $membro[$campo] = trim($_POST[$campo] ?? '');
    }

    if ($membro['nome'] === '') {
        $erros[] = 'O nome completo é obrigatório.';
    }
    if ($membro['email'] !== '' && !filter_var($membro['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido ou deixe o campo em branco.';
    }

    // A senha é sempre opcional: só quem tem função diferente de "Membro" E senha definida
    // consegue fazer login. Dá para cadastrar alguém com função de liderança sem senha —
    // ela só não terá acesso ao sistema até um administrador definir uma senha depois.
    $funcaoAdministrativa = eh_funcao_administrativa($membro['funcao'] ?: 'Membro');
    if ($funcaoAdministrativa && $membro['senha'] !== '' && strlen($membro['senha']) < 6) {
        $erros[] = 'A senha de acesso deve ter pelo menos 6 caracteres.';
    }

    if (empty($erros)) {
        $sql = 'INSERT INTO membros
                    (nome, data_nascimento, cpf, telefone, email, endereco, bairro, cidade, estado, cep, data_batismo, funcao, status, senha, observacoes)
                VALUES
                    (:nome, :data_nascimento, :cpf, :telefone, :email, :endereco, :bairro, :cidade, :estado, :cep, :data_batismo, :funcao, :status, :senha, :observacoes)';

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
                'senha'           => ($funcaoAdministrativa && $membro['senha'] !== '') ? password_hash($membro['senha'], PASSWORD_DEFAULT) : null,
                'observacoes'     => $membro['observacoes'] ?: null,
            ]);

            header('Location: index.php?sucesso=cadastrado');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erros[] = 'Já existe um membro cadastrado com este CPF.';
            } else {
                $erros[] = 'Erro ao salvar o cadastro: ' . $e->getMessage();
            }
        }
    }
}

$tituloPagina = 'Novo Membro';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1>Cadastrar novo membro</h1>
    <a href="index.php" class="botao botao-link">&larr; Voltar para a lista</a>
</div>

<form method="post" action="cadastrar.php" class="formulario-membro" novalidate>
    <?php require __DIR__ . '/includes/formulario_membro.php'; ?>
    <div class="acoes-formulario">
        <a href="index.php" class="botao botao-secundario">Cancelar</a>
        <button type="submit" class="botao botao-primario">Salvar membro</button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
