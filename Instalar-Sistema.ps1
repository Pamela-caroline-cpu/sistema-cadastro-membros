<#
    Instalar Sistema.exe
    Prepara esta máquina do zero para rodar o Sistema de Cadastro de Membros:
    instala PHP e MariaDB (se ainda não estiverem instalados), configura a
    extensão do banco, registra e liga o serviço do banco, e importa o
    banco de dados inicial. Precisa ser executado como Administrador
    (a própria instalação pede isso automaticamente).

    Normalmente você não precisa abrir este arquivo diretamente: o
    "Iniciar Sistema.exe" chama ele sozinho na primeira vez que é usado.
#>

# O executável compilado (ps2exe) trata erros não-fatais como fatais por padrão
# (ex.: avisos que o mariadb.exe manda para stderr). Isso força o comportamento
# normal do PowerShell, onde esses avisos não interrompem o script.
$ErrorActionPreference = 'Continue'

# Descobre a pasta do projeto (funciona tanto como script quanto compilado em .exe)
if ($PSScriptRoot) {
    $pastaProjeto = $PSScriptRoot
} else {
    $pastaProjeto = Split-Path -Parent ([System.Reflection.Assembly]::GetEntryAssembly().Location)
}

$arquivoLog = Join-Path $pastaProjeto 'instalar.log'

function Info([string]$msg) {
    $linha = "$(Get-Date -Format 'HH:mm:ss') ==> $msg"
    Write-Host $linha -ForegroundColor Cyan
    Add-Content -Path $arquivoLog -Value $linha
}

function Mostrar-Erro([string]$mensagem) {
    Add-Content -Path $arquivoLog -Value "$(Get-Date -Format 'HH:mm:ss') [ERRO] $mensagem"
    Write-Host ''
    Write-Host "ERRO: $mensagem" -ForegroundColor Red
    Write-Host ''
    Write-Host 'Esta janela fecha sozinha em alguns segundos...' -ForegroundColor DarkGray
    Start-Sleep -Seconds 8
}

function Mostrar-Info([string]$mensagem) {
    Add-Content -Path $arquivoLog -Value "$(Get-Date -Format 'HH:mm:ss') [OK] $mensagem"
    Write-Host ''
    Write-Host $mensagem -ForegroundColor Green
    Start-Sleep -Seconds 2
}

function Porta-Livre([int]$porta) {
    try {
        $listener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Loopback, $porta)
        $listener.Start(); $listener.Stop()
        return $true
    } catch { return $false }
}

Set-Content -Path $arquivoLog -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') Iniciando instalação. Pasta do projeto: $pastaProjeto"

try {
    if (-not (Test-Path (Join-Path $pastaProjeto 'index.php'))) {
        Mostrar-Erro "Não encontrei os arquivos do sistema (index.php) na pasta:`n$pastaProjeto`n`nMantenha este instalador dentro da pasta do projeto (sistema-cadastro-membros)."
        exit 1
    }

    if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
        Mostrar-Erro "O 'winget' (App Installer da Microsoft) não foi encontrado nesta máquina.`n`nInstale o 'App Installer' pela Microsoft Store e execute este instalador novamente."
        exit 1
    }

    # --- 1) PHP -----------------------------------------------------------------
    Info 'Verificando o PHP...'
    $phpExe = (Get-Command php.exe -ErrorAction SilentlyContinue).Source
    if (-not $phpExe) {
        $pastaWinget = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Filter 'PHP.PHP.*' -Directory -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending | Select-Object -First 1
        if ($pastaWinget -and (Test-Path (Join-Path $pastaWinget.FullName 'php.exe'))) {
            $phpExe = Join-Path $pastaWinget.FullName 'php.exe'
        }
    }

    if (-not $phpExe) {
        Info 'PHP não encontrado. Instalando (PHP 8.4)...'
        winget install --id PHP.PHP.8.4 --accept-package-agreements --accept-source-agreements -e *>> $arquivoLog
        $pastaWinget = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Filter 'PHP.PHP.*' -Directory -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending | Select-Object -First 1
        if ($pastaWinget -and (Test-Path (Join-Path $pastaWinget.FullName 'php.exe'))) {
            $phpExe = Join-Path $pastaWinget.FullName 'php.exe'
        }
    }

    if (-not $phpExe) {
        Mostrar-Erro "Não consegui instalar ou localizar o PHP automaticamente.`n`nInstale manualmente com 'winget install PHP.PHP.8.4' e execute este instalador de novo."
        exit 1
    }
    Info "PHP encontrado: $phpExe"

    $pastaPhp = Split-Path -Parent $phpExe
    $phpIni = Join-Path $pastaPhp 'php.ini'
    if (-not (Test-Path $phpIni)) {
        Info 'Configurando o php.ini (habilitando pdo_mysql)...'
        $modelo = Join-Path $pastaPhp 'php.ini-development'
        if (Test-Path $modelo) {
            Copy-Item $modelo $phpIni
            (Get-Content $phpIni) `
                -replace '^;extension_dir = "ext"', 'extension_dir = "ext"' `
                -replace '^;extension=pdo_mysql', 'extension=pdo_mysql' `
                -replace '^;extension=mysqli', 'extension=mysqli' |
                Set-Content $phpIni
        }
    }

    # --- 2) MariaDB ---------------------------------------------------------------
    Info 'Verificando o banco de dados (MariaDB)...'
    $pastaMaria = Get-ChildItem 'C:\Program Files' -Filter 'MariaDB*' -Directory -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending | Select-Object -First 1

    if (-not $pastaMaria) {
        Info 'MariaDB não encontrado. Instalando...'
        winget install --id MariaDB.Server --accept-package-agreements --accept-source-agreements -e *>> $arquivoLog
        $pastaMaria = Get-ChildItem 'C:\Program Files' -Filter 'MariaDB*' -Directory -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending | Select-Object -First 1
    }

    if (-not $pastaMaria) {
        Mostrar-Erro "Não consegui instalar ou localizar o MariaDB automaticamente.`n`nInstale manualmente com 'winget install MariaDB.Server' e execute este instalador de novo."
        exit 1
    }
    Info "MariaDB encontrado em: $($pastaMaria.FullName)"

    $mariadbdExe = Join-Path $pastaMaria.FullName 'bin\mariadbd.exe'
    $mariadbExe = Join-Path $pastaMaria.FullName 'bin\mariadb.exe'
    $myIni = Join-Path $pastaMaria.FullName 'data\my.ini'

    $servico = Get-Service -Name 'MariaDB' -ErrorAction SilentlyContinue

    if ($servico) {
        # O serviço já existe: usa a porta que JÁ está configurada, sem mexer em nada
        # (recalcular a porta aqui seria errado — o próprio banco já rodando ocuparia
        # a porta testada, dando a falsa impressão de que ela está em uso por outra coisa)
        $portaBanco = 3306
        if (Test-Path $myIni) {
            $m = Select-String -Path $myIni -Pattern '^port\s*=\s*(\d+)' | Select-Object -First 1
            if ($m) { $portaBanco = [int]$m.Matches[0].Groups[1].Value }
        }
        if ($servico.Status -ne 'Running') {
            Info 'Iniciando o banco de dados...'
            Start-Service -Name 'MariaDB'
            Start-Sleep -Seconds 2
        } else {
            Info "Banco de dados já está rodando (porta $portaBanco)."
        }
    } else {
        # Primeira instalação nesta máquina: escolhe uma porta livre e registra o serviço
        if (Porta-Livre 3306) {
            $portaBanco = 3306
        } else {
            $portaBanco = 3307
            while (-not (Porta-Livre $portaBanco) -and $portaBanco -lt 3320) { $portaBanco++ }
        }

        if (Test-Path $myIni) {
            (Get-Content $myIni) -replace '^port\s*=.*', "port=$portaBanco" | Set-Content $myIni
        }

        Info "Registrando o serviço do banco de dados (porta $portaBanco)..."
        & $mariadbdExe --install MariaDB --defaults-file="$myIni" *>> $arquivoLog
        Start-Sleep -Seconds 1
        Start-Service -Name 'MariaDB' -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 2
    }

    # --- 3) Importa o banco de dados (só se ainda não existir) --------------------
    $schema = Join-Path $pastaProjeto 'database\schema.sql'
    if (Test-Path $schema) {
        $bancoExiste = & $mariadbExe -u root -P $portaBanco -h 127.0.0.1 -N -e "SHOW DATABASES LIKE 'igreja_membros';" 2>$null
        if (-not $bancoExiste) {
            Info 'Importando o banco de dados inicial...'
            Get-Content $schema -Raw | & $mariadbExe -u root -P $portaBanco -h 127.0.0.1 2>$null
        } else {
            Info 'Banco de dados já existe — mantendo os dados atuais.'
        }
    }

    # --- 4) Confere se config/database.php aponta para a porta certa; corrige se não ---
    $configPhp = Join-Path $pastaProjeto 'config\database.php'
    if (Test-Path $configPhp) {
        $conteudoAtual = Get-Content $configPhp -Raw
        if ($conteudoAtual -notmatch "\`$porta\s*=\s*'$portaBanco'") {
            Info "Ajustando config/database.php para a porta $portaBanco..."
            ($conteudoAtual -replace "\`$porta\s*=\s*'\d+';.*", "`$porta   = '$portaBanco'; // definido automaticamente pelo instalador") |
                Set-Content $configPhp -NoNewline
        }
    }

    # --- 5) Cria um atalho na Área de Trabalho para "Iniciar Sistema.exe" ----------
    try {
        $iniciarExe = Join-Path $pastaProjeto 'Iniciar Sistema.exe'
        $areaTrabalho = [Environment]::GetFolderPath('Desktop')
        $atalho = Join-Path $areaTrabalho 'Sistema de Cadastro de Membros.lnk'
        if ((Test-Path $iniciarExe) -and -not (Test-Path $atalho)) {
            Info 'Criando atalho na Área de Trabalho...'
            $wsh = New-Object -ComObject WScript.Shell
            $lnk = $wsh.CreateShortcut($atalho)
            $lnk.TargetPath = $iniciarExe
            $lnk.WorkingDirectory = $pastaProjeto
            $lnk.IconLocation = "$iniciarExe,0"
            $lnk.Description = 'Abre o Sistema de Cadastro de Membros'
            $lnk.Save()
        }
    } catch {
        Add-Content -Path $arquivoLog -Value "$(Get-Date -Format 'HH:mm:ss') [AVISO] Não consegui criar o atalho na Área de Trabalho: $($_.Exception.Message)"
    }

    # --- 6) Marca a instalação como concluída --------------------------------------
    Info 'Gravando marcador de instalação concluída...'
    Set-Content -Path (Join-Path $pastaProjeto '.instalado') -Value (Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

    Mostrar-Info "Instalação concluída!`n`nPHP: $phpExe`nBanco de dados: porta $portaBanco`n`nUm atalho foi criado na Área de Trabalho.`nO sistema vai iniciar agora."
} catch {
    Add-Content -Path $arquivoLog -Value "$(Get-Date -Format 'HH:mm:ss') [EXCECAO] $($_.Exception.Message)`n$($_.ScriptStackTrace)"
    Mostrar-Erro "Ocorreu um erro durante a instalação:`n`n$($_.Exception.Message)`n`nDetalhes salvos em: $arquivoLog"
    exit 1
}
