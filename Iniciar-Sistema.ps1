# liga o mariadb (se precisar), sobe o php -S e abre o navegador no login

Add-Type -AssemblyName System.Windows.Forms

function Mostrar-Erro([string]$mensagem) {
    [System.Windows.Forms.MessageBox]::Show(
        $mensagem,
        'Sistema de Cadastro de Membros',
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
}

function Mostrar-Aviso([string]$mensagem) {
    [System.Windows.Forms.MessageBox]::Show(
        $mensagem,
        'Sistema de Cadastro de Membros',
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Warning
    ) | Out-Null
}

# pega a pasta do projeto (funciona tanto rodando o .ps1 quanto o .exe compilado)
if ($PSScriptRoot) {
    $pastaProjeto = $PSScriptRoot
} else {
    $pastaProjeto = Split-Path -Parent ([System.Reflection.Assembly]::GetEntryAssembly().Location)
}

if (-not (Test-Path (Join-Path $pastaProjeto 'index.php'))) {
    Mostrar-Erro "Não encontrei os arquivos do sistema (index.php) na pasta:`n$pastaProjeto`n`nMantenha este executável dentro da pasta do projeto (sistema-cadastro-membros)."
    exit 1
}

# primeira vez na máquina (sem php/mariadb)? chama o instalador antes
$marcaInstalado = Join-Path $pastaProjeto '.instalado'
$servicoJaExiste = Get-Service -Name 'MariaDB' -ErrorAction SilentlyContinue
$phpJaExiste = Get-Command php.exe -ErrorAction SilentlyContinue

if (-not (Test-Path $marcaInstalado) -and (-not $servicoJaExiste -or -not $phpJaExiste)) {
    $instaladorExe = Join-Path $pastaProjeto 'Instalar Sistema.exe'
    $instaladorPs1 = Join-Path $pastaProjeto 'Instalar-Sistema.ps1'

    try {
        if (Test-Path $instaladorExe) {
            Start-Process -FilePath $instaladorExe -Verb RunAs -Wait -ErrorAction Stop
        } elseif (Test-Path $instaladorPs1) {
            Start-Process -FilePath 'powershell.exe' -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$instaladorPs1`"" -Verb RunAs -Wait -ErrorAction Stop
        } else {
            Mostrar-Erro "Este é o primeiro uso nesta máquina, mas não encontrei o instalador ('Instalar Sistema.exe').`n`nMantenha-o na mesma pasta do sistema."
            exit 1
        }
    } catch {
        Mostrar-Erro "A instalação precisa de permissão de Administrador e foi cancelada.`n`nDê dois cliques em 'Iniciar Sistema.exe' de novo e clique em 'Sim' quando o Windows pedir permissão."
        exit 1
    }
}

# confere se o mariadb tá rodando
$servicoBanco = Get-Service -Name 'MariaDB' -ErrorAction SilentlyContinue
if ($servicoBanco -and $servicoBanco.Status -ne 'Running') {
    try {
        Start-Service -Name 'MariaDB' -ErrorAction Stop
        Start-Sleep -Seconds 2
    } catch {
        Mostrar-Aviso "O banco de dados (MariaDB) não pôde ser iniciado automaticamente. Se o sistema não conectar, abra o Painel de Serviços do Windows e inicie o serviço 'MariaDB' manualmente (pode ser necessário executar como Administrador)."
    }
}

# acha o php.exe
$phpExe = $null
$phpIni = $null

$comandoPhp = Get-Command php.exe -ErrorAction SilentlyContinue
if ($comandoPhp) {
    $phpExe = $comandoPhp.Source
}

if (-not $phpExe) {
    $pastaWinget = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Filter 'PHP.PHP.*' -Directory -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending | Select-Object -First 1
    if ($pastaWinget -and (Test-Path (Join-Path $pastaWinget.FullName 'php.exe'))) {
        $phpExe = Join-Path $pastaWinget.FullName 'php.exe'
    }
}

if (-not $phpExe) {
    $candidatos = @('C:\xampp\php\php.exe', 'C:\php\php.exe')
    foreach ($candidato in $candidatos) {
        if (Test-Path $candidato) { $phpExe = $candidato; break }
    }
}

if (-not $phpExe) {
    Mostrar-Erro "Não encontrei o PHP instalado nesta máquina.`n`nInstale o PHP (por exemplo, com 'winget install PHP.PHP.8.4') e tente novamente."
    exit 1
}

# usa o php.ini do lado do php.exe se tiver (já vem com pdo_mysql ligado)
$possivelIni = Join-Path (Split-Path -Parent $phpExe) 'php.ini'
if (Test-Path $possivelIni) {
    $phpIni = $possivelIni
}

# porta fixa em 8000 - se trocasse sozinha o QR code impresso ia parar de funcionar
function Porta-Livre([int]$porta) {
    try {
        $listener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Loopback, $porta)
        $listener.Start()
        $listener.Stop()
        return $true
    } catch {
        return $false
    }
}

$porta = 8000
if (-not (Porta-Livre $porta)) {
    Mostrar-Erro "A porta $porta já está sendo usada por outro programa nesta máquina.`n`nIsso pode acontecer se o sistema já estiver rodando em outra janela (veja se já não há uma janela preta aberta) ou se outro programa está usando essa porta. Feche o que estiver usando a porta $porta e tente de novo.`n`nA porta é sempre fixa em $porta para que o QR Code impresso continue funcionando."
    exit 1
}

# sobe o php -S numa janela (fechar a janela para o servidor)
$argsPhp = @('-S', "0.0.0.0:$porta")
if ($phpIni) { $argsPhp = @('-c', $phpIni) + $argsPhp }

$tituloJanela = 'Sistema de Cadastro de Membros - Servidor (NAO FECHE enquanto estiver usando o sistema)'
$comandoCmd = "title $tituloJanela && echo Sistema rodando em http://localhost:$porta/ && echo Feche esta janela para PARAR o sistema. && echo. && `"$phpExe`" " + ($argsPhp -join ' ')

Start-Process -FilePath 'cmd.exe' -ArgumentList "/k $comandoCmd" -WorkingDirectory $pastaProjeto

# espera subir e abre o navegador
Start-Sleep -Seconds 2
Start-Process "http://localhost:$porta/login.php"
