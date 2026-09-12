<#
    Iniciar Sistema.exe
    Sobe o banco de dados (MariaDB) e o servidor do sistema (PHP), e abre o
    navegador direto na tela de login. Feito para o responsável pela igreja
    só dar dois cliques e o sistema já iniciar por completo.
#>

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

# Descobre a pasta do projeto, tanto rodando como script quanto compilado em .exe
if ($PSScriptRoot) {
    $pastaProjeto = $PSScriptRoot
} else {
    $pastaProjeto = Split-Path -Parent ([System.Reflection.Assembly]::GetEntryAssembly().Location)
}

if (-not (Test-Path (Join-Path $pastaProjeto 'index.php'))) {
    Mostrar-Erro "Não encontrei os arquivos do sistema (index.php) na pasta:`n$pastaProjeto`n`nMantenha este executável dentro da pasta do projeto (sistema-cadastro-membros)."
    exit 1
}

# --- 0) Primeira vez nesta máquina? Roda o instalador (PHP + MariaDB) antes ---
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

# --- 1) Garante que o banco de dados (MariaDB) está rodando ---------------
$servicoBanco = Get-Service -Name 'MariaDB' -ErrorAction SilentlyContinue
if ($servicoBanco -and $servicoBanco.Status -ne 'Running') {
    try {
        Start-Service -Name 'MariaDB' -ErrorAction Stop
        Start-Sleep -Seconds 2
    } catch {
        Mostrar-Aviso "O banco de dados (MariaDB) não pôde ser iniciado automaticamente. Se o sistema não conectar, abra o Painel de Serviços do Windows e inicie o serviço 'MariaDB' manualmente (pode ser necessário executar como Administrador)."
    }
}

# --- 2) Localiza o PHP -------------------------------------------------------
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

# Usa o php.ini vendorizado ao lado do PHP (com pdo_mysql habilitado), se existir
$possivelIni = Join-Path (Split-Path -Parent $phpExe) 'php.ini'
if (Test-Path $possivelIni) {
    $phpIni = $possivelIni
}

# --- 3) Escolhe uma porta livre, começando em 8000 --------------------------
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
while (-not (Porta-Livre $porta) -and $porta -lt 8020) {
    $porta++
}

# --- 4) Sobe o servidor PHP numa janela visível (fechar a janela = parar o sistema) ---
$argsPhp = @('-S', "0.0.0.0:$porta")
if ($phpIni) { $argsPhp = @('-c', $phpIni) + $argsPhp }

$tituloJanela = 'Sistema de Cadastro de Membros - Servidor (NAO FECHE enquanto estiver usando o sistema)'
$comandoCmd = "title $tituloJanela && echo Sistema rodando em http://localhost:$porta/ && echo Feche esta janela para PARAR o sistema. && echo. && `"$phpExe`" " + ($argsPhp -join ' ')

Start-Process -FilePath 'cmd.exe' -ArgumentList "/k $comandoCmd" -WorkingDirectory $pastaProjeto

# --- 5) Espera o servidor subir e abre o navegador ---------------------------
Start-Sleep -Seconds 2
Start-Process "http://localhost:$porta/login.php"
