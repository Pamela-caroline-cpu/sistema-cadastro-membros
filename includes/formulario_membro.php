<?php
// Parcial reutilizado por cadastrar.php e editar.php
// Espera as variáveis: $membro (array associativo com os campos), $erros (array de mensagens)
require_once __DIR__ . '/funcoes.php';
$membro = $membro ?? [];
$erros  = $erros ?? [];

function valor(array $membro, string $campo): string
{
    return htmlspecialchars($membro[$campo] ?? '');
}
?>

<?php if (!empty($erros)): ?>
    <div class="alerta alerta-erro">
        <strong>Corrija os campos abaixo:</strong>
        <ul>
            <?php foreach ($erros as $erro): ?>
                <li><?= htmlspecialchars($erro) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<fieldset>
    <legend>Dados pessoais</legend>
    <div class="grade-form">
        <div class="campo campo-largo">
            <label for="nome">Nome completo *</label>
            <input type="text" id="nome" name="nome" maxlength="150" required value="<?= valor($membro, 'nome') ?>">
        </div>
        <div class="campo">
            <label for="data_nascimento">Data de nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?= valor($membro, 'data_nascimento') ?>">
        </div>
        <div class="campo">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= valor($membro, 'cpf') ?>">
        </div>
        <div class="campo">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" maxlength="20" placeholder="(00) 00000-0000" value="<?= valor($membro, 'telefone') ?>">
        </div>
        <div class="campo campo-largo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= valor($membro, 'email') ?>">
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Endereço</legend>
    <div class="grade-form">
        <div class="campo campo-largo">
            <label for="endereco">Endereço (rua e número)</label>
            <input type="text" id="endereco" name="endereco" maxlength="200" value="<?= valor($membro, 'endereco') ?>">
        </div>
        <div class="campo">
            <label for="bairro">Bairro</label>
            <input type="text" id="bairro" name="bairro" maxlength="100" value="<?= valor($membro, 'bairro') ?>">
        </div>
        <div class="campo">
            <label for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade" maxlength="100" value="<?= valor($membro, 'cidade') ?: 'Dois Vizinhos' ?>">
        </div>
        <div class="campo campo-pequeno">
            <label for="estado">UF</label>
            <input type="text" id="estado" name="estado" maxlength="2" value="<?= valor($membro, 'estado') ?: 'PR' ?>">
        </div>
        <div class="campo">
            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" maxlength="10" placeholder="00000-000" value="<?= valor($membro, 'cep') ?>">
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Vínculo com a igreja</legend>
    <div class="grade-form">
        <div class="campo">
            <label for="data_batismo">Data de batismo</label>
            <input type="date" id="data_batismo" name="data_batismo" value="<?= valor($membro, 'data_batismo') ?>">
        </div>
        <div class="campo">
            <label for="funcao">Função / Cargo</label>
            <select id="funcao" name="funcao">
                <?php
                $funcaoAtual = $membro['funcao'] ?? 'Membro';
                foreach (funcoes_disponiveis() as $opcao):
                ?>
                    <option value="<?= $opcao ?>" <?= $funcaoAtual === $opcao ? 'selected' : '' ?>><?= $opcao ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="status">Status</label>
            <select id="status" name="status">
                <?php $statusAtual = $membro['status'] ?? 'Ativo'; ?>
                <option value="Ativo" <?= $statusAtual === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="Inativo" <?= $statusAtual === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="campo campo-largo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"><?= valor($membro, 'observacoes') ?></textarea>
        </div>
    </div>
</fieldset>

<?php
$modoEdicao = isset($membro['id']);
$funcaoAtual = $membro['funcao'] ?? 'Membro';
$jaTemSenha = $modoEdicao && !empty($membro['senha']);
?>
<fieldset id="fieldset-acesso" <?= $funcaoAtual === 'Membro' ? 'style="display:none"' : '' ?>>
    <legend>Acesso ao sistema</legend>
    <div class="grade-form">
        <div class="campo campo-largo">
            <label for="senha">
                Senha de acesso (opcional)
                <?= $modoEdicao ? '— deixe em branco para manter a senha atual' : '' ?>
            </label>
            <input
                type="password"
                id="senha"
                name="senha"
                minlength="6"
                autocomplete="new-password"
                placeholder="<?= $modoEdicao ? 'Nova senha (opcional)' : 'Defina uma senha para dar acesso de login' ?>"
            >
            <small class="ajuda-campo">
                Só é preciso definir uma senha se esta pessoa vai <strong>entrar no sistema</strong> como
                administrador. Dá para deixar em branco — o cadastro é salvo normalmente, só sem acesso de login.
                <?= ($modoEdicao && $jaTemSenha) ? ' Este membro já tem uma senha cadastrada.' : '' ?>
            </small>
        </div>
    </div>
</fieldset>
