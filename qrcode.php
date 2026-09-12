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

// Um QR Code gerado a partir de "localhost" ou "127.0.0.1" só funciona neste
// computador — para imprimir e usar em outros celulares, é preciso acessar
// esta página pelo IP fixo da máquina na rede (ex.: 192.168.x.x).
$enderecoLocal = (bool) preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:|$)/i', $host);

$tituloPagina = 'QR Code de Autocadastro';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1>QR Code de autocadastro</h1>
    <a href="index.php" class="botao botao-link">&larr; Voltar para a lista</a>
</div>

<?php if ($enderecoLocal): ?>
    <div class="alerta alerta-erro">
        <strong>Este QR Code só vai funcionar neste computador.</strong><br>
        Você está acessando por "<?= htmlspecialchars($host) ?>", que só existe localmente. Para gerar um QR
        Code que funcione nos celulares da igreja, acesse esta página pelo <strong>IP fixo desta máquina na
        rede</strong> — por exemplo <code>http://192.168.1.50:8000/qrcode.php</code> — em vez de localhost.
        Veja a seção "IP fixo para o QR Code sempre funcionar" no README do projeto.
    </div>
<?php endif; ?>

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

    <div class="acoes-qrcode">
        <button type="button" class="botao botao-primario" onclick="baixarPng()">Baixar QR Code (PNG)</button>
        <button type="button" class="botao botao-secundario" onclick="window.print()">Imprimir</button>
    </div>

    <p class="nota-qrcode">
        <strong>Importante:</strong> para o QR Code impresso continuar funcionando sempre, o endereço
        (<code><?= htmlspecialchars($host) ?></code>) e a porta desta máquina precisam ficar fixos — veja
        "IP fixo para o QR Code sempre funcionar" no README do projeto. Se o endereço mudar, é só voltar
        nesta página e baixar/imprimir um novo QR Code.
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

    function baixarPng() {
        const img = document.querySelector('#qrcode img');
        if (!img) return;
        const link = document.createElement('a');
        link.download = 'qrcode-cadastro-membros.png';
        link.href = img.src;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
