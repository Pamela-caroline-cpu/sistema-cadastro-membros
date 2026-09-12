<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

// Monta a URL pública de autocadastro a partir do endereço que está sendo usado
// para acessar o sistema agora — funciona tanto em localhost/teste quanto no
// domínio real, sem precisar editar nada quando o sistema for publicado.
$esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$pasta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$urlPublica = "{$esquema}://{$host}{$pasta}/cadastro-publico.php";

$tituloPagina = 'QR Code de Autocadastro';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1>QR Code de autocadastro</h1>
    <a href="index.php" class="botao botao-link">&larr; Voltar para a lista</a>
</div>

<div class="cartao-qrcode">
    <p class="texto-qrcode">
        Imprima ou exiba este QR Code na igreja. Ao escanear, a pessoa abre diretamente a tela de
        autocadastro — sem precisar de login — e o cadastro é enviado direto para o banco de dados do sistema.
    </p>

    <div id="qrcode" class="caixa-qrcode"></div>

    <div class="campo campo-link-publico">
        <label for="link-publico">Link da página de autocadastro</label>
        <div class="grupo-copiar">
            <input type="text" id="link-publico" readonly value="<?= htmlspecialchars($urlPublica) ?>">
            <button type="button" class="botao botao-secundario" onclick="copiarLink()">Copiar</button>
        </div>
        <span id="copiado" class="aviso-copiado" hidden>Link copiado!</span>
    </div>

    <button type="button" class="botao botao-primario botao-largo" onclick="window.print()">Imprimir QR Code</button>

    <p class="nota-qrcode">
        Se o endereço do sistema mudar (por exemplo, ao publicar em um servidor/domínio novo), esta página
        gera o QR Code automaticamente para o endereço certo — não é preciso gerar um novo em outro lugar.
    </p>
</div>

<script src="assets/js/qrcode.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($urlPublica) ?>,
        width: 220,
        height: 220,
        colorDark: '#004b71',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });

    function copiarLink() {
        const campo = document.getElementById('link-publico');
        campo.select();
        campo.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(campo.value).then(function () {
            const aviso = document.getElementById('copiado');
            aviso.hidden = false;
            setTimeout(function () { aviso.hidden = true; }, 2000);
        });
    }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
