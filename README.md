# Sistema de Cadastro de Membros

Sistema web para cadastro e gerenciamento de membros (congregantes) da **Primeira Igreja Quadrangular de Dois Vizinhos - PR**, desenvolvido em **PHP + MySQL** como parte da Atividade Extensionista II (Curso de Análise e Desenvolvimento de Sistemas - UNINTER).

## Como iniciar (jeito mais fácil)

Dê **dois cliques** em **`Iniciar Sistema.exe`**, na raiz desta pasta. Ele sozinho:
1. Confere se o banco de dados (MariaDB) está rodando e liga se estiver desligado;
2. Sobe o servidor do sistema;
3. Abre o navegador já na tela de login.

Uma janela preta (o servidor) vai abrir e **precisa continuar aberta** enquanto o sistema estiver em uso —
é só minimizá-la. Fechar essa janela desliga o sistema.

> Se o Windows mostrar um aviso do SmartScreen ("O Windows protegeu o computador"), é porque o executável
> não tem assinatura digital paga — clique em **"Mais informações" → "Executar assim mesmo"**. É seguro: o
> código-fonte está em `Iniciar-Sistema.ps1`, na mesma pasta, para quem quiser conferir.

Há **dois executáveis** na pasta — eles têm ícones diferentes de propósito, para não confundir:

| Executável | Ícone | Quando usar |
|---|---|---|
| **`Iniciar Sistema.exe`** | Logo da igreja (liso) | Sempre — é o que você usa no dia a dia para abrir o sistema. |
| **`Instalar Sistema.exe`** | Logo da igreja com uma seta preta de download | Só na primeira vez numa máquina nova, e só se necessário — o próprio `Iniciar Sistema.exe` já chama ele sozinho quando detecta que falta algo (veja "Instalador automático" abaixo). |

Para rodar em outro computador (ou sem o executável), veja "Como executar localmente" mais abaixo.

## Instalador automático (primeira execução numa máquina nova)

Numa máquina que ainda não tem PHP nem o banco de dados, `Iniciar Sistema.exe` detecta isso sozinho e chama
`Instalar Sistema.exe` antes de continuar — sem precisar fazer nada além de dar dois cliques em `Iniciar
Sistema.exe` normalmente. O instalador:
1. Instala o PHP, se necessário (via `winget`);
2. Instala e configura o MariaDB, se necessário, registra e liga o serviço do Windows;
3. Importa o banco de dados inicial (só se ele ainda não existir — nunca apaga dados já cadastrados);
4. Ajusta `config/database.php` com a porta certa do banco;
5. Cria um atalho **"Sistema de Cadastro de Membros"** na Área de Trabalho, apontando para `Iniciar
   Sistema.exe` — depois da instalação, não precisa mais abrir a pasta do sistema, é só usar esse atalho.

O Windows vai pedir permissão de Administrador (obrigatório para instalar programas) — é só clicar em "Sim".
Uma janela mostra o progresso e fecha sozinha ao final; os detalhes ficam salvos em `instalar.log`, na mesma
pasta, caso algo dê errado.

## Funcionalidades

- **Acesso administrativo** (login obrigatório) para quem tem função de liderança (qualquer função diferente de "Membro"): cadastrar, consultar, editar e excluir membros.
- **Autocadastro público** (sem login), acessível por um **QR Code**: qualquer pessoa escaneia, escolhe a qual grupo pertence (Membro, Obreiro(a), Diácono, etc.) e se cadastra diretamente pelo celular.
- **Cadastrar** novos membros (dados pessoais, endereço e vínculo com a igreja) — pela área administrativa ou pelo autocadastro público.
- **Consultar** membros com busca por nome e filtro por status (Ativo/Inativo).
- **Editar** os dados de um membro já cadastrado.
- **Excluir** um membro (com confirmação).
- **Visualizar** a ficha completa de um membro.

## Quem tem acesso a quê

| Perfil | Acesso |
|---|---|
| **Administradores** (função diferente de "Membro" **e** com senha definida) | Login em `login.php` → acesso total: listar, cadastrar, editar, excluir e ver o QR Code. |
| **Demais pessoas** (função "Membro", ou qualquer função sem senha definida) | Não têm login. Só usam a página pública `cadastro-publico.php` para se cadastrar (acessada via QR Code). |

Importante: escolher um grupo como "Obreiro(a)" ou "Diácono" no autocadastro público **não dá acesso ao
sistema** sozinho — só quem tem uma **senha definida por um administrador** consegue fazer login (veja "Como
cadastrar um novo administrador" abaixo). A função, por si só, é só uma etiqueta de qual grupo a pessoa faz parte.

**Login de demonstração** (troque antes de usar de verdade — veja "Segurança" abaixo):
- E-mail: `maria.souza@email.com` (ou `joao.ferreira@email.com`, `carlos.santos@email.com`)
- Senha: `quadrangular2026`

## Como cadastrar um novo administrador

Logado no sistema, vá em **"+ Novo Membro"** (ou edite um membro já existente, inclusive alguém que já se
autocadastrou pelo QR Code) e, no campo **"Função / Cargo"**, escolha qualquer opção diferente de "Membro"
(Diácono, Líder de Louvor, Pastor, etc.). Um campo **"Senha de acesso (opcional)"** aparece automaticamente —
defina a senha que essa pessoa vai usar para entrar no sistema (mínimo 6 caracteres) e informe um e-mail, que
é o que ela vai usar para logar. Ao salvar, essa pessoa já pode entrar em `login.php` com esse e-mail e senha.

Se você deixar o campo de senha em branco, o cadastro é salvo normalmente com aquela função/grupo, só que sem
acesso de login — dá pra voltar e definir a senha depois, quando quiser liberar o acesso.

Para **revogar o acesso** de um administrador, edite o cadastro dele e mude a função de volta para "Membro" —
a senha é apagada automaticamente e o login deixa de funcionar.

Ao editar um administrador já existente, o campo de senha aparece em branco: deixe em branco para manter a
senha atual, ou digite uma nova para trocá-la.

## QR Code de autocadastro

Logado como administrador, acesse **"QR Code"** no menu (`qrcode.php`). A página gera automaticamente um QR Code apontando para `cadastro-publico.php` **usando o endereço atual do sistema** (funciona tanto em `localhost` quanto no domínio real, sem precisar editar nada). Dá pra **baixar como imagem PNG** (botão "Baixar QR Code") ou **imprimir** direto da página.

⚠️ Se você acessar `qrcode.php` por `localhost`, o QR gerado só funciona neste computador — para gerar um QR que funcione nos celulares da igreja, acesse pelo IP da máquina na rede (veja a seção abaixo).

## IP fixo para o QR Code sempre funcionar

O sistema vai rodar num computador fixo na igreja, acessado pelo Wi-Fi local. Para o QR Code **impresso uma vez** continuar funcionando para sempre, o endereço desse computador na rede (IP + porta) **não pode mudar**. Por padrão, o Windows pega um IP diferente a cada tanto tempo (fornecido pelo roteador) — por isso é preciso fixá-lo. A porta já é sempre fixa em **8000** (o "Iniciar Sistema.exe" não usa outra porta automaticamente).

Duas formas de fixar o IP (escolha uma):

### Opção A — Reserva de IP no roteador (recomendado)

Não mexe em nada no computador; o roteador sempre dá o mesmo IP pra essa máquina.

1. Descubra o endereço físico (MAC) da placa de rede do computador:
   ```powershell
   ipconfig /all
   ```
   Procure por "Endereço Físico" da conexão em uso (Ethernet ou Wi-Fi).
2. Entre no painel do roteador (geralmente `192.168.0.1` ou `192.168.1.1` no navegador — veja o manual do roteador da igreja).
3. Procure por "Reserva de DHCP" / "DHCP Reservation" / "IP Reservado" e associe o MAC do passo 1 a um IP fixo (ex.: `192.168.1.50`).
4. Reinicie o computador (ou desconecte/reconecte a rede) para ele pegar o IP reservado.

### Opção B — IP fixo direto no Windows

Use se não tiver acesso ao roteador. Passo a passo (ajuste os valores conforme a rede da igreja — pergunte pra quem administra a internet local, ou veja o IP atual com `ipconfig`):

```powershell
# Exemplo: fixa o IP 192.168.1.50 na rede 192.168.1.0/24, gateway 192.168.1.1
New-NetIPAddress -InterfaceAlias "Ethernet" -IPAddress 192.168.1.50 -PrefixLength 24 -DefaultGateway 192.168.1.1
Set-DnsClientServerAddress -InterfaceAlias "Ethernet" -ServerAddresses 8.8.8.8,1.1.1.1
```
Troque `"Ethernet"` pelo nome da conexão certa (veja com `Get-NetAdapter`) e escolha um IP **fora** da faixa que o roteador distribui automaticamente, para não conflitar com outro dispositivo.

### Depois de fixar o IP

1. No próprio computador (ou em qualquer aparelho na mesma rede), acesse `http://<IP-FIXO>:8000/qrcode.php` (ex.: `http://192.168.1.50:8000/qrcode.php`) — **não** `localhost`.
2. Baixe ou imprima o QR Code nessa página. Ele vai continuar válido enquanto o IP não mudar.

## Tecnologias utilizadas

- PHP 8+ (sem frameworks, usando PDO para acesso ao banco)
- MySQL / MariaDB
- Autenticação com sessão PHP + senha com hash (`password_hash`/`password_verify`)
- HTML5, CSS3 e JavaScript puro — inclusive a geração do QR Code, feita com a biblioteca [qrcodejs](https://github.com/davidshimjs/qrcodejs) (MIT), embutida localmente em `assets/js/qrcode.js` (sem depender de nenhum serviço externo)

## Estrutura do projeto

```
sistema-cadastro-membros/
├── Iniciar Sistema.exe        # Dê dois cliques aqui para ligar tudo de uma vez
├── Iniciar-Sistema.ps1        # Código-fonte do executável acima (PowerShell)
├── Instalar Sistema.exe       # Instala PHP/MariaDB numa máquina nova (chamado sozinho, se precisar)
├── Instalar-Sistema.ps1       # Código-fonte do executável acima (PowerShell)
├── assets/
│   ├── css/style.css        # Estilos da interface
│   ├── js/script.js         # Máscaras de campo e confirmação de exclusão
│   ├── js/qrcode.js         # Biblioteca de geração de QR Code (vendorizada, MIT)
│   ├── img/logo.jpg         # Logo da igreja
│   ├── img/logo.ico         # Ícone usado pelo Iniciar Sistema.exe
│   └── img/logo-instalador.ico # Ícone usado pelo Instalar Sistema.exe (com selo de download)
├── config/
│   └── database.php         # Configuração de conexão com o MySQL (PDO)
├── database/
│   └── schema.sql           # Script de criação do banco + dados de exemplo
├── includes/
│   ├── header.php           # Cabeçalho/menu da área administrativa
│   ├── footer.php           # Rodapé
│   ├── funcoes.php          # Funções utilitárias (formatação de data, etc.)
│   ├── auth.php             # Controle de login/sessão dos administradores
│   └── formulario_membro.php# Formulário reutilizado por cadastrar.php e editar.php
├── login.php                 # Login dos administradores
├── logout.php                # Encerra a sessão
├── qrcode.php                 # Gera o QR Code do autocadastro (admin)
├── cadastro-publico.php      # Autocadastro público — para onde o QR Code aponta
├── index.php                # Listagem/consulta de membros (admin)
├── cadastrar.php            # Inclusão de novo membro (admin)
├── editar.php                # Edição de membro existente (admin)
├── visualizar.php           # Ficha detalhada do membro (admin)
├── excluir.php               # Exclusão de membro (admin)
└── README.md
```

## Como executar localmente

Este ambiente já tem **PHP 8.4** e um **MariaDB 12.3** instalados e configurados especificamente para este projeto (veja a seção abaixo). Se for rodar em outra máquina, siga a Opção 1 ou 2.

### Ambiente já preparado nesta máquina

- PHP 8.4 instalado via winget (`php.ini` com `pdo_mysql` habilitado).
- MariaDB 12.3 instalado via winget, rodando **na porta 3307** (não 3306, pois esta máquina já tinha um MySQL Server 8.0 ocupando a porta padrão). `config/database.php` já aponta para a porta 3307.
- O serviço do Windows do MariaDB precisa ser registrado uma única vez com privilégios de Administrador (não é possível fazer isso por um terminal sem elevação). Abra o **PowerShell como Administrador** e rode:

```powershell
& "C:\Program Files\MariaDB 12.3\bin\mariadbd.exe" --install MariaDB --defaults-file="C:\Program Files\MariaDB 12.3\data\my.ini"
net start MariaDB
```

Depois disso, o MariaDB inicia sozinho com o Windows (porta 3307, usuário `root` sem senha). Importe o banco uma vez:

```bash
"C:\Program Files\MariaDB 12.3\bin\mariadb.exe" -u root -P 3307 -h 127.0.0.1 < database/schema.sql
```

E suba o PHP na pasta do projeto:

```bash
cd sistema-cadastro-membros
php -S localhost:8000
```

Acesse `http://localhost:8000`.

### Opção 1 — Usando XAMPP / WAMP / MAMP (outra máquina)

1. Instale o [XAMPP](https://www.apachefriends.org/) (ou WAMP/MAMP).
2. Copie a pasta `sistema-cadastro-membros` para dentro de `htdocs` (no XAMPP, normalmente `C:\xampp\htdocs\`).
3. Inicie os serviços **Apache** e **MySQL** no painel de controle do XAMPP.
4. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`), crie o banco importando o arquivo `database/schema.sql` (aba "Importar" ou cole o conteúdo na aba "SQL").
5. Ajuste `config/database.php`: normalmente porta `3306` (padrão do XAMPP) e sem senha para `root`.
6. Acesse `http://localhost/sistema-cadastro-membros/` no navegador.

### Opção 2 — Usando o servidor embutido do PHP (outra máquina)

Pré-requisitos: PHP instalado e um servidor MySQL/MariaDB em execução localmente.

```bash
# 1. Crie o banco de dados e as tabelas
mysql -u root -p < database/schema.sql

# 2. Ajuste config/database.php com host/porta/usuário/senha do seu MySQL

# 3. Entre na pasta do projeto e suba o servidor embutido do PHP
cd sistema-cadastro-membros
php -S localhost:8000

# 4. Acesse no navegador
http://localhost:8000
```

## Banco de dados

O banco `igreja_membros` contém a tabela `membros`, com os seguintes campos principais:

| Campo             | Descrição                                   |
|--------------------|----------------------------------------------|
| nome               | Nome completo (obrigatório)                  |
| data_nascimento    | Data de nascimento                            |
| cpf                | CPF (único)                                   |
| telefone, email    | Contato                                       |
| endereco, bairro, cidade, estado, cep | Endereço completo         |
| data_batismo       | Data de batismo na igreja                     |
| funcao             | Grupo/cargo na igreja (Membro, Diácono, Obreiro(a), Pastor, etc.) — escolhido no cadastro |
| status             | Ativo ou Inativo                              |
| senha              | Hash da senha de login — só existe se um administrador definiu uma senha para essa pessoa (função "Membro" nunca tem) |
| observacoes        | Anotações livres                              |
| data_cadastro / data_atualizacao | Preenchidos automaticamente pelo sistema |

O script `database/schema.sql` já inclui 5 membros de exemplo para facilitar os testes e a demonstração do sistema.

## Segurança

- Todas as consultas usam **prepared statements (PDO)**, prevenindo SQL Injection.
- Toda saída de dados do usuário passa por `htmlspecialchars()`, prevenindo XSS.
- Exclusão de registros exige confirmação e é feita via `POST`.
- Todas as páginas administrativas (`index.php`, `cadastrar.php`, `editar.php`, `excluir.php`, `visualizar.php`, `qrcode.php`) exigem login (`includes/auth.php`).
- Senhas são armazenadas com `password_hash()` (bcrypt) e verificadas com `password_verify()` — nunca em texto puro.
- A página pública `cadastro-publico.php` deixa escolher o **grupo/função**, mas **nunca** tem campo de senha — o servidor sempre grava `senha = NULL` nesses cadastros, então ninguém consegue virar administrador só pelo formulário público. Acesso de login só existe se um administrador definir uma senha depois, pelo painel.
- **Antes de usar em produção:** troque a senha de demonstração (`quadrangular2026`) de cada administrador — edite o cadastro de cada um em "Editar" e defina uma nova senha (veja "Como cadastrar um novo administrador" acima).

## Autor

Projeto desenvolvido para a Atividade Extensionista II — Análise e Desenvolvimento de Sistemas — Centro Universitário Internacional UNINTER.
