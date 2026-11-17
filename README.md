# PDV — Sistema de Vendas (MVC + Design Patterns)

Continuação do Sistema de PDV fornecido em aula. O código original (arquivos soltos `index.html`,
`salvar_produto.php`, `pdv.php`, `editar_produto.php`, `db.php`) foi reorganizado na arquitetura
**MVC**, recebeu **design patterns** e ganhou três telas novas com CRUD completo:

| # | Tela | Create | Read | Update | Delete |
|---|------|--------|------|--------|--------|
| 1 | **Cadastro de clientes** | Novo cliente | Lista + busca por nome/CPF | Editar cliente | Excluir (bloqueado se o cliente tem vendas) |
| 2 | **Carrinho de vendas** | Adicionar produto | Ver itens, subtotal, desconto e total | Alterar quantidade, cliente e desconto | Remover item / esvaziar |
| 3 | **Relatórios de vendas** | (a venda nasce ao finalizar o carrinho) | Lista com filtros, totais, gráfico por dia, mais vendidos, detalhe da venda e CSV | Editar cliente, pagamento, status e observação | Excluir venda (devolve o estoque) |

O CRUD de **produtos** que já existia continua funcionando, agora dentro do MVC.

**Tecnologias:** PHP 8.0+ puro (sem framework e sem Composer), PDO, Bootstrap 5.
**Bancos suportados:** MySQL/MariaDB, PostgreSQL (incluindo Neon) e SQLite. Para trocar de banco, basta mudar uma linha no `.env`.

---

## 1. Arquitetura MVC

```
atividade/
├── public/                  ← ÚNICA pasta acessível pelo navegador
│   ├── index.php            ← Front Controller (toda requisição entra aqui)
│   ├── assets/css/app.css
│   └── imagens/             ← fotos dos produtos
├── app/
│   ├── bootstrap.php        ← autoload, .env, sessão
│   ├── helpers.php          ← e(), url(), moeda(), dataBr()
│   ├── Core/                ← "mini framework": Router, Controller, View, Database...
│   ├── Models/              ← M: Cliente, Produto, Venda, ItemVenda
│   ├── Views/               ← V: telas HTML (layout + uma pasta por módulo)
│   ├── Controllers/         ← C: Home, Cliente, Produto, Carrinho, Relatorio
│   ├── Repositories/        ← acesso ao banco (todo o SQL fica aqui)
│   └── Services/            ← regras de negócio (carrinho, venda, descontos)
├── database/                ← scripts SQL (MySQL, PostgreSQL, SQLite) + dados de exemplo
├── storage/                 ← banco SQLite local
└── .env.example             ← modelo de configuração (o .env real não vai para o GitHub)
```

### Papel de cada camada

| Camada | Pasta | Responsabilidade | Exemplo |
|--------|-------|------------------|---------|
| **Model** | `app/Models` | Representa os dados e as regras deles (validação). Não acessa banco nem gera HTML. | `Cliente::validar()` confere os dígitos do CPF |
| **View** | `app/Views` | Só exibe dados. Não tem SQL nem regra de negócio. Todo texto passa por `e()` (proteção contra XSS). | `clientes/index.php` monta a tabela |
| **Controller** | `app/Controllers` | Recebe a requisição, chama Repositories/Services e escolhe a View. | `ClienteController::salvar()` |
| Repository | `app/Repositories` | Lê e grava no banco (apoio ao Model). | `ClienteRepository::inserir()` |
| Service | `app/Services` | Regras que envolvem várias entidades. | `VendaService::finalizar()` |

### Fluxo de uma requisição

```mermaid
sequenceDiagram
    participant N as Navegador
    participant FC as public/index.php<br/>(Front Controller)
    participant R as Router
    participant C as ClienteController
    participant Repo as ClienteRepository
    participant DB as Banco (PDO)
    participant V as View

    N->>FC: POST index.php?r=clientes/salvar
    FC->>R: despachar("clientes/salvar")
    R->>C: salvar()
    C->>C: Cliente::fromArray($_POST)->validar()
    C->>Repo: inserir($cliente)
    Repo->>DB: INSERT ... (prepared statement)
    C-->>N: redirect para clientes/index (Post/Redirect/Get)
    N->>FC: GET index.php?r=clientes/index
    FC->>R: despachar
    R->>C: index()
    C->>Repo: listar()
    C->>V: render("clientes/index", dados)
    V-->>N: HTML
```

### Antes x depois

| PDV original | Agora |
|--------------|-------|
| Cada arquivo `.php` misturava SQL, regra e HTML | SQL só nos Repositories, regras nos Models/Services, HTML só nas Views |
| `include 'db.php'` + `global $conn` em todo arquivo | Uma conexão única via **Singleton** |
| `salvar_produto.php` concatenava valores no SQL (**SQL Injection**) | 100% prepared statements |
| Senha do banco escrita no código | Senhas no `.env`, que está no `.gitignore` |
| Upload usava o nome original do arquivo (permitia sobrescrever ou enviar `.php`) | Nome aleatório e validação de extensão e de imagem |
| `editar_produto.php` dava warning sem `?id=` (ver `error_log`) | Controller valida e redireciona com mensagem |
| Sem proteção CSRF | Token CSRF em todos os formulários POST |

---

## 2. Design Patterns adotados

### 2.1 Front Controller + Router
**Onde:** `public/index.php`, `app/Core/Router.php`

Todas as requisições passam por um único arquivo, que registra as rotas (`clientes/editar` → `ClienteController::editar`).
Configurações comuns (sessão, autoload, tratamento de erro) ficam em um lugar só, e não repetidas em cada página como no original.

### 2.2 Singleton
**Onde:** `app/Core/Database.php`

```php
$pdo = Database::getInstance()->getConnection();
```
O construtor é `private`, então só existe **uma** conexão PDO por requisição, reaproveitada por todos os Repositories.
Isso substitui o `global $conn` do `db.php` original.

### 2.3 Factory
**Onde:** `app/Core/ConnectionFactory.php` e `app/Services/Desconto/DescontoFactory.php`

- `ConnectionFactory::criar()` lê `DB_DRIVER` do `.env` e devolve o PDO certo (MySQL, PostgreSQL/Neon ou SQLite). O resto do sistema não sabe qual banco está em uso. Isso juntou as duas versões do professor (MySQL e PostgreSQL) em um único código.
- `DescontoFactory::criar('percentual', 10)` devolve a estratégia de desconto correta. O controller não dá `new` em nenhuma classe de desconto.

### 2.4 Repository
**Onde:** `app/Repositories/*`

Cada tabela tem uma classe que concentra o SQL dela (`listar`, `buscarPorId`, `inserir`, `atualizar`, `excluir`).
Os Controllers pedem objetos (`$repo->buscarPorId(5)` devolve um `Cliente`) sem escrever SQL. Se trocar de banco, só os Repositories mudam.

### 2.5 Strategy
**Onde:** `app/Services/Desconto/`

```
DescontoStrategy (interface)
 ├── SemDesconto
 ├── DescontoPercentual   (ex.: 10%)
 └── DescontoValorFixo    (ex.: R$ 5,00, nunca maior que o subtotal)
```
O carrinho só chama `$estrategia->calcular($subtotal)`. Para criar uma promoção nova (ex.: "leve 3 pague 2"), basta
criar outra classe que implemente a interface, sem alterar o carrinho (princípio Aberto/Fechado).

### 2.6 Facade / Service Layer
**Onde:** `app/Services/VendaService.php`

Finalizar uma venda envolve validar o cliente, reler o preço e o estoque de cada produto, baixar o estoque, gravar a venda e os
itens e limpar o carrinho. O controller faz uma chamada só:

```php
$vendaId = (new VendaService())->finalizar($carrinho, $formaPagamento, $observacao);
```
Tudo roda dentro de uma **transação**: se um produto não tiver estoque, nada é gravado (rollback).
O mesmo serviço cuida de cancelar/reativar e excluir vendas, devolvendo ou baixando o estoque.

### 2.7 Template View
**Onde:** `app/Core/View.php`, `app/Views/layout/base.php`

A view de cada tela é renderizada primeiro e depois inserida no layout comum (menu, mensagens, Bootstrap).
O cabeçalho HTML não se repete em todos os arquivos.

### Padrões de apoio
- **Post/Redirect/Get + Flash Messages** (`Core/Flash.php`): depois de salvar, o sistema redireciona e mostra a mensagem uma vez. Dar F5 não reenvia o formulário.
- **Data Mapper simples** (`Model::fromArray`): converte linha do banco ↔ objeto.

---

## 3. POP — Procedimento Operacional Padrão de Implantação

| Item | Descrição |
|------|-----------|
| **Objetivo** | Padronizar a instalação do PDV em ambiente local (teste) e em servidor de hospedagem (produção). |
| **Aplicação** | Desenvolvedores e responsáveis pela implantação do sistema. |
| **Resultado esperado** | Sistema acessível pelo navegador, conectado ao banco, com as 4 telas funcionando. |

### 3.1 Pré-requisitos

| Requisito | Versão / observação |
|-----------|---------------------|
| PHP | 8.0 ou superior |
| Extensões PHP | `pdo` e o driver do banco: `pdo_sqlite`, `pdo_mysql` ou `pdo_pgsql` |
| Banco | Um dos três: SQLite (nada a instalar), MySQL 5.7+/MariaDB 10.3+ ou PostgreSQL 12+/Neon |
| Servidor web | Apache (XAMPP, cPanel...) ou o servidor embutido do PHP |
| Git | Para clonar o repositório |

Para conferir o PHP: `php -v` e `php -m` (deve aparecer `PDO` e `pdo_mysql`/`pdo_pgsql`/`pdo_sqlite`).

### 3.2 Procedimento A: ambiente local rápido (SQLite)

Indicado para testar e corrigir. Não precisa instalar banco.

1. Clonar o projeto:
   ```bash
   git clone https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git pdv
   cd pdv
   ```
2. Criar o arquivo de configuração:
   ```bash
   cp .env.example .env          # Windows (PowerShell): copy .env.example .env
   ```
   Deixe `DB_DRIVER=sqlite`.
3. Subir o servidor apontando para a pasta `public`:
   ```bash
   php -S localhost:8000 -t public
   ```
4. Abrir **http://localhost:8000** no navegador.
   Na primeira execução o arquivo `storage/pdv.sqlite` é criado sozinho, com as tabelas e dados de exemplo (4 produtos e 2 clientes).

### 3.3 Procedimento B: XAMPP + MySQL (local)

1. Instalar o XAMPP e iniciar **Apache** e **MySQL** no painel.
2. Copiar a pasta do projeto para `C:\xampp\htdocs\pdv`.
3. Abrir **http://localhost/phpmyadmin**, criar o banco `pdv` (collation `utf8mb4_general_ci`).
4. Com o banco `pdv` selecionado, aba **SQL**: colar e executar `database/schema.mysql.sql`. Se quiser dados de teste, executar também `database/seed.sql`.
5. Copiar `.env.example` para `.env` e configurar:
   ```ini
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=pdv
   DB_USER=root
   DB_PASS=
   ```
6. Acessar **http://localhost/pdv/public/** (ou `http://localhost/pdv/`, que redireciona).

### 3.4 Procedimento C: hospedagem compartilhada (cPanel + MySQL)

1. No cPanel, em **Bancos de dados MySQL**: criar o banco e o usuário, e dar **todos os privilégios** ao usuário nesse banco.
2. No **phpMyAdmin** do cPanel: importar `database/schema.mysql.sql` (e `seed.sql`, se quiser os dados de exemplo).
   > Se o banco já tiver a tabela `produtos` do PDV antigo, ela é mantida (`CREATE TABLE IF NOT EXISTS`). Ela precisa ser **InnoDB** para as chaves estrangeiras funcionarem. Se não for, rode `ALTER TABLE produtos ENGINE=InnoDB;`.
3. Enviar os arquivos (Gerenciador de Arquivos ou FTP) para uma pasta, por exemplo `public_html/pdv/`.
4. Criar o `.env` no servidor (a partir do `.env.example`) com os dados do passo 1 e `DB_DRIVER=mysql`.
5. Dar permissão de escrita (755 ou 775) na pasta `public/imagens/` para o upload de fotos.
6. Acessar `https://seudominio.com/pdv/`.
   **Segurança:** os arquivos `.htaccess` incluídos bloqueiam o acesso a `.env`, `app/`, `database/` e `storage/`. Se possível, configure o domínio/subdomínio para apontar direto para a pasta `public/`.

### 3.5 Procedimento D: PostgreSQL na nuvem (Neon)

1. Em **neon.tech**: criar o projeto e copiar a *connection string* (botão **Connect**).
2. No **SQL Editor** do Neon: executar `database/schema.pgsql.sql` (e `seed.sql`, se quiser os dados de exemplo).
3. No `.env`:
   ```ini
   DB_DRIVER=pgsql
   DATABASE_URL="postgresql://usuario:senha@ep-xxxx-pooler.sa-east-1.aws.neon.tech/neondb"
   ```
   O sistema ativa `sslmode=require` e o parâmetro `endpoint` automaticamente quando o host é do Neon (mesma solução do `db.php` do professor).
4. Subir pelo Procedimento A (passo 3) ou C (passos 3 a 6).

### 3.6 Checklist de verificação pós-implantação

- [ ] A página inicial abre e mostra os cartões (vendas hoje, clientes, produtos)
- [ ] **Clientes:** cadastrar, com CPF válido (ex.: `529.982.247-25`), buscar, editar e excluir
- [ ] **Produtos:** cadastrar com imagem, e a foto aparece na lista
- [ ] **Carrinho:** buscar produto → adicionar → alterar quantidade → aplicar 10% de desconto → finalizar
- [ ] O estoque do produto diminuiu após a venda
- [ ] **Relatórios:** a venda aparece; filtrar por data; abrir detalhe; editar status para *Cancelada* (o estoque volta); exportar CSV
- [ ] Acessar `/.env` pelo navegador retorna **403/404** (nunca o conteúdo)

### 3.7 Problemas comuns

| Sintoma | Causa provável | Solução |
|---------|----------------|---------|
| "could not find driver" | Extensão PDO do banco desativada | Habilitar `extension=pdo_mysql` / `pdo_pgsql` / `pdo_sqlite` no `php.ini` e reiniciar o Apache |
| "Access denied for user" | Usuário/senha errados no `.env` | Conferir `.env` e os privilégios do usuário no cPanel |
| "Base table or view not found" | Tabelas não criadas | Executar o script de `database/` do seu banco |
| "unable to open database file" (SQLite) | Pasta `storage/` sem permissão de escrita | Dar permissão de escrita em `storage/` |
| Imagem não salva | `public/imagens/` sem permissão | `chmod 775 public/imagens` |
| "Requisição inválida" ao salvar | Sessão expirou (token CSRF) | Recarregar a página e enviar de novo |
| Página em branco | Erro de PHP oculto | Ver o `error_log` da hospedagem |

### 3.8 Atualização e rollback

- **Atualizar:** `git pull` (ou reenviar os arquivos), **sem sobrescrever o `.env`** nem `public/imagens/`.
- **Backup antes de atualizar:** exportar o banco (phpMyAdmin → Exportar / `pg_dump` / copiar `storage/pdv.sqlite`).
- **Rollback:** `git checkout <commit-anterior>` e restaurar o backup do banco, se necessário.

---

## 4. Mapa de rotas

| Rota (`index.php?r=`) | Método | Ação |
|-----------------------|--------|------|
| `home` | GET | Painel inicial |
| `clientes/index` · `criar` · `editar&id=` | GET | Lista / formulário novo / formulário edição |
| `clientes/salvar` · `atualizar` · `excluir` | POST | Create / Update / Delete |
| `produtos/...` | | Mesma estrutura de clientes |
| `carrinho/index` | GET | Busca de produtos + carrinho |
| `carrinho/adicionar` · `atualizar` · `remover` · `limpar` | POST | CRUD dos itens |
| `carrinho/opcoes` | POST | Cliente e desconto (Strategy) |
| `carrinho/finalizar` | POST | Grava a venda (Facade + transação) |
| `relatorios/index` | GET | Relatório com filtros |
| `relatorios/ver&id=` · `editar&id=` | GET | Detalhe / formulário edição |
| `relatorios/atualizar` · `excluir` | POST | Update / Delete |
| `relatorios/csv` | GET | Exporta o relatório filtrado |

## 5. Modelo de dados

```mermaid
erDiagram
    clientes ||--o{ vendas : "faz"
    vendas ||--|{ itens_venda : "contém"
    produtos ||--o{ itens_venda : "vendido em"

    clientes {
        int id PK
        string nome
        string cpf UK
        string email
        string telefone
        string endereco
        datetime criado_em
    }
    produtos {
        int id PK
        string cod
        string nome
        string categoria
        date validade
        int quantidade
        decimal preco_unitario
        decimal preco_pacote
        string imagem
    }
    vendas {
        int id PK
        int cliente_id FK
        datetime data_venda
        decimal subtotal
        decimal desconto
        decimal total
        string forma_pagamento
        string status
    }
    itens_venda {
        int id PK
        int venda_id FK
        int produto_id FK
        string nome_produto
        int quantidade
        decimal preco_unitario
        decimal subtotal
    }
```

`itens_venda` guarda o nome e o preço **do momento da venda**. Assim o relatório continua correto mesmo se o produto mudar de preço ou for excluído.
#   S i s t e m a - d e - V e n d a s  
 