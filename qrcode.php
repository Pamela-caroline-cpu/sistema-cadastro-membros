<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/auth.php';
exigir_login();

// gera o QR sempre com o IP real da máquina na rede, não importa como o
// admin acessou essa página (mesmo por localhost o QR sai certo)
$esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$porta = $_SERVER['SERVER_PORT'] ?? '8000';
$pasta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

$ipLocal = ip_local_da_maquina();
$falhaDeteccao = $ipLocal === null;
$host = $ipLocal ? "{$ipLocal}:{$porta}" : ($_SERVER['HTTP_HOST'] ?? 'localhost');

$urlPublica = "{$esquema}://{$host}{$pasta}/cadastro-publico.php";

$tituloPagina = 'QR Code de Autocadastro';
require __DIR__ . '/includes/header.php';
?>

<div class="cabecalho-pagina">
    <h1>QR Code de autocadastro</h1>
    <a href="index.php" class="botao botao-link">&larr; Voltar para a lista</a>
</div>

<?php if ($falhaDeteccao): ?>
    <div class="alerta alerta-erro">
        <strong>Não consegui detectar o IP desta máquina na rede.</strong><br>
        O QR Code abaixo foi gerado com "<?= htmlspecialchars($host) ?>", que só funciona neste computador.
        Confira se a máquina está conectada à rede (cabo ou Wi-Fi) e recarregue esta página.
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
