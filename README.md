# Sistema de Cadastro de Membros

Projeto da Atividade Extensionista II (ADS - UNINTER). Sistema de cadastro de membros pra Primeira Igreja
Quadrangular de Dois Vizinhos - PR, feito em PHP + MySQL.

## Como rodar

Dá dois cliques em `Iniciar Sistema.exe`. Ele confere se o MariaDB tá ligado, sobe o servidor PHP e abre o
navegador na tela de login. Vai abrir uma janela preta do servidor - deixa ela aberta (pode minimizar), fechar
ela desliga o sistema.

Se numa máquina nova não tiver PHP/MariaDB instalado ainda, o `Iniciar Sistema.exe` chama sozinho o
`Instalar Sistema.exe`, que instala tudo via winget, registra o serviço do banco, importa o schema.sql e ainda
cria um atalho na Área de Trabalho. Só pede permissão de admin, que é obrigatório pra instalar programa. Log
de tudo isso fica em `instalar.log` se der algum problema.

(os dois .exe tem ícones diferentes de propósito pra não confundir - o do instalador tem uma seta de download)

Se o Windows acusar SmartScreen é só porque o .exe não tem assinatura paga, clica em "mais informações" →
"executar assim mesmo". O código fonte de ambos tá nos .ps1 do lado, pra quem quiser ver o que faz.

Se for rodar em outra máquina sem os executáveis, tem o passo a passo manual lá embaixo.

## O que dá pra fazer

- Login de administrador (função != Membro) pra cadastrar, editar, consultar e excluir membros
- Autocadastro público sem login, via QR Code - a pessoa escaneia, escolhe o grupo dela (Membro, Obreiro,
  Diácono etc) e se cadastra pelo celular
- Busca por nome e filtro por status na listagem
- Interface responsiva, pensada primeiro pra celular já que é onde a maioria vai acessar

## Login e acesso admin

Só tem acesso ao painel quem tem função diferente de "Membro" **e** senha cadastrada. Escolher um grupo tipo
"Obreiro" no autocadastro público não dá acesso sozinho - não tem campo de senha ali. Quem libera acesso é
sempre um admin, editando o cadastro da pessoa e definindo uma senha.

Login de teste (trocar antes de usar de verdade):
- `maria.souza@email.com` / `quadrangular2026`

Pra criar um admin: edite/cadastre alguém, mude a função pra qualquer coisa diferente de "Membro" e vai
aparecer um campo de senha (opcional - se deixar em branco a pessoa fica sem acesso, só com a função salva
mesmo). Pra tirar o acesso de alguém, muda a função de volta pra "Membro" e a senha é apagada.

## QR Code

Logado, tem "QR Code" no menu. Gera automático pro endereço que você tá usando pra acessar, com opção de
baixar em PNG ou imprimir. Se acessar por `localhost` ele avisa que aquele QR só serve nesse computador mesmo
- pra funcionar nos celulares da igreja precisa acessar pelo IP da máquina na rede.

## Deixando o IP fixo (pra imprimir o QR uma vez só)

Como o servidor vai ficar numa máquina fixa na igreja, acessado só pelo Wi-Fi local, pra imprimir o QR uma
vez e ele continuar valendo pra sempre o IP dessa máquina não pode ficar mudando. A porta já é sempre 8000
(fixo no `Iniciar Sistema.exe`).

Duas formas de fixar o IP:

**Reserva no roteador** (melhor opção) - pega o MAC da placa de rede com `ipconfig /all`, entra no painel do
roteador (normalmente `192.168.0.1` ou `192.168.1.1`) e procura "Reserva de DHCP" / "IP Reservado", associa
o MAC a um IP fixo tipo `192.168.1.50`. Reinicia o PC depois.

**IP fixo direto no Windows** (se não tiver acesso ao roteador):
```powershell
New-NetIPAddress -InterfaceAlias "Ethernet" -IPAddress 192.168.1.50 -PrefixLength 24 -DefaultGateway 192.168.1.1
Set-DnsClientServerAddress -InterfaceAlias "Ethernet" -ServerAddresses 8.8.8.8,1.1.1.1
```
Troca o nome da interface e o IP pra um que esteja fora da faixa que o roteador distribui sozinho.

Depois disso, acessa `http://<IP-fixo>:8000/qrcode.php` (não localhost) e baixa/imprime o QR de lá.

## Rodando em outra máquina, do zero

Se não for usar os executáveis:

```bash
mysql -u root -p < database/schema.sql
# ajusta host/porta/usuário/senha em config/database.php
cd sistema-cadastro-membros
php -S localhost:8000
```

Ou com XAMPP/WAMP: copia a pasta pra `htdocs`, sobe Apache+MySQL, importa o `schema.sql` pelo phpMyAdmin,
ajusta `config/database.php` (porta 3306, root sem senha é o padrão do XAMPP) e acessa
`http://localhost/sistema-cadastro-membros/`.

## Estrutura

```
sistema-cadastro-membros/
├── Iniciar Sistema.exe / .ps1
├── Instalar Sistema.exe / .ps1
├── assets/         css, js (inclusive a lib de QR code, vendorizada) e imagens
├── config/         conexão com o banco
├── database/       schema.sql
├── includes/       header, footer, auth, funções auxiliares, form compartilhado
├── login.php / logout.php
├── qrcode.php
├── cadastro-publico.php
├── index.php / cadastrar.php / editar.php / visualizar.php / excluir.php
└── README.md
```

## Banco de dados

Tabela `membros`: nome, data_nascimento, cpf, telefone, email, endereço (rua/bairro/cidade/estado/cep), data
de batismo, funcao (o grupo da pessoa), status (ativo/inativo), senha (hash, só existe se algum admin definiu
uma pra essa pessoa) e observações. `schema.sql` já vem com 5 membros de exemplo pra testar.

## Segurança

Consultas todas com prepared statement (PDO), saída sempre passando por `htmlspecialchars`, senha com
`password_hash`/`password_verify` (bcrypt, nunca texto puro), exclusão só via POST com confirmação. Páginas
administrativas exigem login. O form público nunca tem campo de senha, então dar a função "Obreiro" pra
alguém por ali não vira acesso admin sozinho.

Lembrar de trocar a senha de demonstração antes de usar de verdade.

## Tecnologias

PHP + PDO (sem framework), MySQL/MariaDB, HTML/CSS/JS puro. O QR Code usa a lib
[qrcodejs](https://github.com/davidshimjs/qrcodejs) (MIT), embutida localmente em `assets/js/qrcode.js`.

---
Pamela Caroline Ribeiro da Silva e Gustavo Henrique Maciel - ADS UNINTER
