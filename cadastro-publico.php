<?php
// tela pública, sem login - pra onde o QR code aponta
// se escolher função != Membro, a própria pessoa pode definir a senha dela aqui
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/auth.php';

$membro = [
    'nome' => '', 'data_nascimento' => '', 'cpf' => '', 'telefone' => '', 'email' => '',
    'endereco' => '', 'bairro' => '', 'cidade' => 'Dois Vizinhos', 'estado' => 'PR', 'cep' => '',
    'data_batismo' => '', 'funcao' => 'Membro', 'senha' => '',
];
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($membro as $campo => $valorPadrao) {
        $membro[$campo] = trim($_POST[$campo] ?? '');
    }

    if (!in_array($membro['funcao'], funcoes_disponiveis(), true)) {
        $membro['funcao'] = 'Membro';
    }

    if ($membro['nome'] === '') {
        $erros[] = 'Informe seu nome completo.';
    }
    if ($membro['email'] !== '' && !filter_var($membro['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido ou deixe o campo em branco.';
    }

    $funcaoAdministrativa = eh_funcao_administrativa($membro['funcao']);
    if ($funcaoAdministrativa && $membro['senha'] !== '' && strlen($membro['senha']) < 6) {
        $erros[] = 'A senha de acesso deve ter pelo menos 6 caracteres.';
    }

    if (empty($erros)) {
        $sql = 'INSERT INTO membros
                    (nome, data_nascimento, cpf, telefone, email, endereco, bairro, cidade, estado, cep, data_batismo, funcao, status, senha)
                VALUES
                    (:nome, :data_nascimento, :cpf, :telefone, :email, :endereco, :bairro, :cidade, :estado, :cep, :data_batismo, :funcao, :status, :senha)';

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
                'funcao'          => $membro['funcao'],
                'status'          => 'Ativo',
                'senha'           => ($funcaoAdministrativa && $membro['senha'] !== '') ? password_hash($membro['senha'], PASSWORD_DEFAULT) : null,
            ]);
            $sucesso = true;
        } catch (PDOException $e) {
            $erros[] = ($e->getCode() === '23000')
                ? 'Já existe um cadastro com este CPF.'
                : 'Não foi possível enviar seu cadastro agora. Tente novamente em instantes.';
        }
    }
}

function valor_publico(array $membro, string $campo): string
{
    return htmlspecialchars($membro[$campo] ?? '');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Autocadastro de Membro | Primeira Igreja Quadrangular</title>
<link rel="icon" href="assets/img/logo.jpg">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topo">
    <div class="topo-conteudo">
        <span class="marca">
            <span class="marca-icone"><img src="assets/img/logo.jpg" alt="Logo da igreja"></span>
            <span>
                Cadastro de Membro
                <small>Primeira Igreja Quadrangular &mdash; Dois Vizinhos/PR</small>
            </span>
        </span>
        <a href="login.php" class="botao-entrar">Entrar</a>
    </div>
    <div class="faixa-marca"><span class="f-vermelho"></span><span class="f-amarelo"></span><span class="f-azul"></span><span class="f-roxo"></span></div>
</header>

<main class="conteudo conteudo-publico">
<?php if ($sucesso): ?>
    <div class="cartao-sucesso">
        <div class="icone-sucesso">✓</div>
        <h1>Cadastro enviado!</h1>
        <p>Seja bem-vindo(a), <strong><?= htmlspecialchars($membro['nome']) ?></strong>! Seu cadastro foi recebido
        com sucesso pela secretaria da igreja.</p>
        <a href="cadastro-publico.php" class="botao botao-primario botao-largo">Fazer novo cadastro</a>
    </div>
<?php else: ?>
    <h1>Seja bem-vindo(a)!</h1>
    <p class="texto-publico">Preencha seus dados para se cadastrar como membro da nossa igreja.</p>

    <?php if (!empty($erros)): ?>
        <div class="alerta alerta-erro">
            <ul><?php foreach ($erros as $erro): ?><li><?= htmlspecialchars($erro) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="cadastro-publico.php" class="formulario-membro" novalidate>
        <fieldset>
            <legend>Seus dados</legend>
            <div class="grade-form">
                <div class="campo campo-largo">
                    <label for="nome">Nome completo *</label>
                    <input type="text" id="nome" name="nome" maxlength="150" required value="<?= valor_publico($membro, 'nome') ?>">
                </div>
                <div class="campo">
                    <label for="data_nascimento">Data de nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?= valor_publico($membro, 'data_nascimento') ?>">
                </div>
                <div class="campo">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= valor_publico($membro, 'cpf') ?>">
                </div>
                <div class="campo">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" maxlength="20" placeholder="(00) 00000-0000" value="<?= valor_publico($membro, 'telefone') ?>">
                </div>
                <div class="campo campo-largo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" maxlength="150" value="<?= valor_publico($membro, 'email') ?>">
                </div>
                <div class="campo campo-largo">
                    <label for="funcao">A qual grupo você pertence?</label>
                    <select id="funcao" name="funcao">
                        <?php foreach (funcoes_disponiveis() as $opcao): ?>
                            <option value="<?= $opcao ?>" <?= $membro['funcao'] === $opcao ? 'selected' : '' ?>><?= $opcao ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo campo-largo">
                    <label for="endereco">Endereço (rua e número)</label>
                    <input type="text" id="endereco" name="endereco" maxlength="200" value="<?= valor_publico($membro, 'endereco') ?>">
                </div>
                <div class="campo">
                    <label for="bairro">Bairro</label>
                    <input type="text" id="bairro" name="bairro" maxlength="100" value="<?= valor_publico($membro, 'bairro') ?>">
                </div>
                <div class="campo">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" maxlength="100" value="<?= valor_publico($membro, 'cidade') ?>">
                </div>
                <div class="campo campo-pequeno">
                    <label for="estado">UF</label>
                    <input type="text" id="estado" name="estado" maxlength="2" value="<?= valor_publico($membro, 'estado') ?>">
                </div>
                <div class="campo">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" maxlength="10" placeholder="00000-000" value="<?= valor_publico($membro, 'cep') ?>">
                </div>
                <div class="campo">
                    <label for="data_batismo">Data de batismo (se houver)</label>
                    <input type="date" id="data_batismo" name="data_batismo" value="<?= valor_publico($membro, 'data_batismo') ?>">
                </div>
            </div>
        </fieldset>

        <fieldset id="fieldset-acesso" <?= $membro['funcao'] === 'Membro' ? 'style="display:none"' : '' ?>>
            <legend>Acesso ao sistema</legend>
            <div class="grade-form">
                <div class="campo campo-largo">
                    <label for="senha">Senha de acesso (opcional)</label>
                    <input type="password" id="senha" name="senha" minlength="6" autocomplete="new-password"
                        placeholder="Defina uma senha para acessar a área administrativa">
                    <small class="ajuda-campo">
                        Só preencha se você faz parte da liderança/administração e vai usar o sistema. Com essa
                        senha e o e-mail informado acima, você já consegue entrar em <code>login.php</code>.
                    </small>
                </div>
            </div>
        </fieldset>

        <button type="submit" class="botao botao-primario botao-largo">Enviar cadastro</button>
    </form>
<?php endif; ?>
</main>

<footer class="rodape">
    <p>Sistema de Cadastro de Membros &copy; <?= date('Y') ?> &mdash; Atividade Extensionista II | ADS - UNINTER</p>
</footer>
<script src="assets/js/script.js"></script>
</body>
</html>
