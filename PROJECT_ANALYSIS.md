# Nome do Projeto

**Dona Joana POS** (repositório: PDV / pos-system)

O front-end Laravel+Vue identifica o sistema como **☕ Dona Joana POS**. O histórico Git registra também o commit inicial **“PDV Café em PHP”**, referente à aplicação legada em PHP procedural que ainda coexiste no mesmo repositório.

---

# Descrição do Projeto

Este repositório contém um sistema de ponto de venda (PDV) voltado a um contexto de café/pastelaria. Há **duas camadas de código**: (1) um painel administrativo legado em PHP/PDO/MySQL (`index.php`, `autenticar.php`, `painel-adm/`) e (2) uma aplicação moderna em **Laravel 12 + Vue 3**, com tela de login, abertura de caixa, listagem de produtos, carrinho, teclado numérico de valor recebido e criação de pedidos via API.

A aplicação Laravel é a parte ativa do fluxo de caixa: autenticação por sessão, interface POS em Vue montada em `resources/views/pos.blade.php`, e APIs para produtos, pedidos e estado do caixa. O README.md do projeto é o template padrão do Laravel e **não descreve** o domínio de negócio do PDV.

O painel legado (`painel-adm`) cobre CRUD de usuários, fornecedores, categorias e produtos, listagem de compras e geração de código de barras. Referências a painéis `painel-gerente` e `painel-vendedor` existem em `autenticar.php`, mas **essas pastas não estão presentes** no repositório. O arquivo `config.php` exigido por `conexao.php` também **não está versionado**.

---

# Objetivo

Disponibilizar um PDV para operação de vendas em balcão (seleção de produtos, cálculo de total/troco e registro de pedidos), com autenticação de operador e controle de abertura de caixa, além de manter (no legado) um painel administrativo de cadastros para café/pastelaria.

---

# Problema que o sistema resolve

Automatiza o registro de vendas no caixa: em vez de anotar pedidos manualmente, o operador autentica-se, abre o caixa, escolhe produtos ativos, informa o valor recebido, visualiza o troco e grava o pedido com baixa de estoque. No legado, o administrador gerencia usuários, produtos, categorias, fornecedores e visualiza compras.

---

# Público-alvo

Com base no código encontrado:

- **Operador de caixa / vendedor** — usa a tela POS Laravel+Vue (login, caixa, vendas).
- **Administrador** — usa o `painel-adm` (nível `Administrador` em sessão).
- Níveis `Gerente` e `Vendedor` são tratados em `autenticar.php`, mas os destinos `painel-gerente` e `painel-vendedor` **não existem** neste repositório.

Não há documentação de personas ou mercado-alvo além do que o código e os textos de UI indicam (“Café e Pastelaria”, “Dona Joana POS”).

---

# Principais funcionalidades

## Aplicação Laravel + Vue (Dona Joana POS)

| Funcionalidade | Evidência no código | Observação |
|---|---|---|
| Login / logout por sessão | `AuthController`, rotas `guest`/`auth` | Credenciais: email + senha |
| Tela POS autenticada | `routes/web.php` → view `pos` | Middleware `auth` |
| Abertura de caixa | `CashController::open`, `OpenCashPanel.vue` | Persistido em **sessão PHP**, não em tabela |
| Consulta estado do caixa | `CashController::current` | status `open`/`closed` |
| Listagem de produtos ativos | `ProductController::index` | `is_active = true`, ordenado por nome |
| Grade de produtos + carrinho | `ProductGrid.vue`, `CartPanel.vue`, `PosPage.vue` | Categorias inferidas pelo **nome** no front (não há coluna `category` no model/migration) |
| Teclado numérico (valor recebido) e troco | `PosPage.vue` / `CartPanel.vue` | Moeda exibida em **€** |
| Pagamento / criação de pedido | `POST /api/v1/orders` via `pay()` | Status padrão do pedido: `open` (não há transição para `paid` no fluxo de pagamento do front) |
| Validação de estoque e baixa | `OrderService::validateStock` / `decrement` | Em transação DB |
| Adicionar item a pedido existente | `POST /api/v1/orders/{orderId}/items` | API disponível; **não usada** pelo front POS atual |
| Listar / detalhar pedidos | `OrderController::index` / `show` | API disponível; **não usada** pelo front POS atual |
| Menus Produtos e Relatórios | `PosSidebar.vue` | UI mostra **“Modulo em preparacao”** — sem implementação |

## Painel legado PHP (`painel-adm`)

| Funcionalidade | Evidência |
|---|---|
| Login por email/NIF + senha (texto) | `autenticar.php` |
| Home com cards de métricas | `painel-adm/home.php` (valores **fixos** no HTML, não consultados do banco nesses trechos lidos) |
| CRUD usuários (nome, NIF, email, senha, nível) | `usuarios.php` + inserir/excluir |
| CRUD fornecedores | `fornecedores.php` + inserir/excluir |
| CRUD categorias | `categorias.php` + inserir/excluir |
| CRUD produtos (código, estoque, valor compra/venda, fornecedor, foto) | `produtos.php` + inserir/excluir |
| Compra de produto / listagem de compras | `produtos/comprar-produto.php`, `compras.php` |
| Geração de código de barras | `barras.php`, `geraCodigoBarra.php`, `produtos/barras.php` |
| Edição de perfil | `editar-perfil.php` |
| Restrição a Administrador | `verificar-permissao.php` |

---

# Tecnologias utilizadas

## Stack Laravel + Vue (implementação atual do POS)

- **PHP** ^8.2  
- **Laravel** ^12.0  
- **Vue** ^3.5  
- **Vite** ^6 + `laravel-vite-plugin` + `@vitejs/plugin-vue`  
- **Tailwind CSS** ^4 (`@tailwindcss/vite`)  
- **Axios** ^1.7  
- **Eloquent ORM**, Form Requests, Enums PHP  
- **SQLite** como conexão padrão em `.env.example` (`DB_CONNECTION=sqlite`)  
- Sessão em banco (`SESSION_DRIVER=database` no `.env.example`)  
- **PHPUnit** ^11 (testes mínimos)  
- Ferramentas de suporte no `composer.json`: Tinker, Pint, Sail, Pail, Faker, Mockery, Collision  

## Stack legada (PHP procedural)

- PHP com **PDO** (MySQL: `mysql:dbname=...` em `conexao.php`)  
- Bootstrap 5 (CDN), jQuery, DataTables (referências a `vendor/DataTables` e `vendor/login`)  
- Sessão PHP nativa (`@session_start()`)  

## O que **não** aparece no código como integração de produto

Não há uso de Sanctum/Passport/JWT, gateways de pagamento, envio de e-mail de negócio, Redis/Horizon, broadcasting, WhatsApp, Stripe, Mercado Pago etc. Configurações genéricas do skeleton Laravel (mail, Redis, AWS) existem em `.env.example`/config, mas **sem implementação de domínio** associada.

---

# Arquitetura

## Visão geral

```
[Browser]
   │
   ├─ Blade login (SSR) ──► AuthController (session guard web)
   │
   ├─ Blade pos + Vue SPA (#app)
   │     ├─ GET  /api/v1/products          (routes/api.php — sem middleware auth no arquivo)
   │     ├─ POST /api/v1/orders            (idem)
   │     ├─ GET/POST /api/v1/cash/*        (routes/web.php sob middleware auth)
   │     └─ POST /logout
   │
   └─ Legado (se acessado via index.php / painel-adm)
         └─ PDO MySQL (conexao.php → config.php ausente no repo)
```

## Camadas da aplicação Laravel

1. **Apresentação**: Blade (`auth/login`, `pos`) + componentes Vue em `resources/js/pos/`.  
2. **HTTP**: Controllers em `app/Http/Controllers` (+ Form Requests).  
3. **Domínio / serviço**: `OrderService` concentra criação de pedido, itens, total, estoque e referência (`DJ-` + random).  
4. **Persistência**: Models Eloquent (`User`, `Product`, `Order`, `OrderItem`) + migrations.  
5. **Estado de caixa**: apenas **sessão** (`cash_open`, `cash_initial_balance`, `cash_opened_at`) — sem model/migration de caixa.

Padrão observado: Controller fino + Service para pedidos; produtos e caixa tratados direto no controller.

---

# Estrutura do projeto

```
pos-system/
├── app/
│   ├── Enums/OrderStatus.php          # open | paid | cancelled
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   └── Api/V1/{Product,Order,Cash}Controller.php
│   ├── Http/Requests/{StoreOrder,AddItem}Request.php
│   ├── Models/{User,Product,Order,OrderItem}.php
│   └── Services/OrderService.php
├── bootstrap/app.php                  # rotas web, api, health /up; redirect guests → login
├── config/                            # configs padrão Laravel
├── database/
│   ├── migrations/                    # users, cache, jobs, products, orders, order_items
│   ├── seeders/{Database,Product}Seeder.php
│   └── factories/UserFactory.php
├── resources/
│   ├── js/pos/                        # main.js, PosPage + components
│   ├── css/app.css                    # Tailwind
│   └── views/{auth/login,pos}.blade.php
├── routes/{web,api,console}.php
├── tests/{Feature,Unit}/ExampleTest.php
├── public/                            # front controller Laravel
├── autenticar.php, index.php, logout.php, conexao.php   # legado
├── painel-adm/                        # painel administrativo legado
├── img/                               # imagens (logo, produtos, categorias)
├── laravel12_base/                    # scaffold Laravel (ignorado via .gitignore)
├── LICENSE                            # MIT, Copyright (c) 2026 Ericka
└── README.md                          # template Laravel (não documenta o PDV)
```

---

# Banco de dados

## Laravel (migrations versionadas)

### `users`
`id`, `name`, `email` (unique), `email_verified_at`, `password`, `remember_token`, `timestamps`  
(+ tabelas `password_reset_tokens` e `sessions` na mesma migration).

### `products`
`id`, `name`, `price` (decimal 10,2), `stock` (unsigned int, default 0), `is_active` (bool, default true), `timestamps`.

### `orders`
`id`, `reference` (nullable), `status` (string, default `open`), `total` (decimal 10,2, default 0), `timestamps`.

### `order_items`
`id`, `order_id` (FK → orders, cascadeOnDelete), `product_id` (FK → products, restrictOnDelete), `quantity`, `unit_price`, `line_total`, `timestamps`.

Também existem migrations padrão de `cache` e `jobs` do skeleton Laravel.

### Seeders
- Usuário: `admin@pos-system.com`, nome **Dona Joana**, senha `admin123456789` (hasheada via cast do model).  
- Produtos: Cafe Expresso (6.50), Pao de Queijo (8.00), Capuccino (12.00), com estoques 100/80/60.

### Caixa
**Não há tabela de caixa.** Estado guardado na sessão HTTP.

## Legado (inferido pelas queries PDO)

Tabelas referenciadas no código PHP: `usuarios`, `produtos`, `categorias`, `fornecedores`, `compras`.  
Não há migrations SQL do legado no repositório. A conexão depende de `config.php` (**ausente** no repo). Timezone definido como `Europe/Lisbon` em `conexao.php`.

---

# APIs e integrações

## Endpoints Laravel encontrados

### Em `routes/api.php` (prefixo `/api/v1` pelo bootstrap Laravel)

| Método | Rota | Controller | Auth no arquivo de rotas |
|---|---|---|---|
| GET | `/api/v1/products` | `ProductController@index` | Não declarado |
| GET | `/api/v1/orders` | `OrderController@index` | Não declarado |
| POST | `/api/v1/orders` | `OrderController@store` | Não declarado |
| GET | `/api/v1/orders/{id}` | `OrderController@show` | Não declarado |
| POST | `/api/v1/orders/{orderId}/items` | `OrderController@addItem` | Não declarado |

### Em `routes/web.php` sob `middleware('auth')`, prefixo `api/v1`

| Método | Rota | Controller |
|---|---|---|
| POST | `/api/v1/cash/open` | `CashController@open` (body opcional: `initial_balance`; front envia `50`) |
| GET | `/api/v1/cash/current` | `CashController@current` |

### Rotas web de autenticação / UI

| Método | Rota | Função |
|---|---|---|
| GET | `/login` | View login (`guest`) |
| POST | `/login` | `AuthController@login` |
| GET | `/` | View POS (`auth`) |
| POST | `/logout` | `AuthController@logout` |
| GET | `/up` | Health check Laravel |

## Integrações externas de negócio

**Nenhuma integração externa de negócio está implementada** (pagamentos, ERPs, impressoras fiscais, SMS, etc.). O front consome apenas as APIs internas via Axios (com CSRF token configurado em `bootstrap.js`).

---

# Autenticação

## Laravel POS
- Guard `web` com driver **session** (`config/auth.php`).  
- Login: `Auth::attempt` com email/senha; regeneração de sessão.  
- Logout: `Auth::logout`, invalidação e regeneração de CSRF.  
- Visitantes não autenticados são redirecionados para `route('login')` (`bootstrap/app.php`).  
- Nome do operador injetado na view POS via `data-user-name`.  
- **Não há** roles/permissions no app Laravel (diferente do legado).  
- **Não há** API tokens (Sanctum/Passport).

## Legado
- Login em `autenticar.php` comparando senha em texto na query PDO.  
- Sessão: `nome_usuario`, `nivel_usuario`, `nif_usuario`, `id_usuario`.  
- `painel-adm` exige nível `Administrador`.

---

# O que foi desenvolvido

1. **Base Laravel 12** com Vite, Vue 3 e Tailwind 4.  
2. **Autenticação web** (login Blade + logout).  
3. **UI POS** (“Dona Joana POS”): topbar, sidebar, abertura de caixa, grade de produtos, carrinho, teclado, total/troco, beep ao adicionar item, atalho Enter para pagar.  
4. **API de produtos** (ativos).  
5. **API e serviço de pedidos** com transação, referência `DJ-XXXXXXXX`, cálculo de totais (bcmul quando disponível), validação e decremento de estoque, enum de status.  
6. **API de caixa** baseada em sessão.  
7. **Seeders** de usuário e produtos de demonstração.  
8. **Teste de feature** garantindo redirect de guest `/` → `/login`.  
9. **Código legado** de PDV café/pastelaria (painel adm, login, CRUDs, códigos de barras, imagens) ainda versionado no mesmo repositório.  
10. Pasta `laravel12_base/` como cópia de scaffold (ignorada no `.gitignore`).

---

# O que aprendi desenvolvendo este projeto

Com base apenas no que o código demonstra ter sido construído (não há diário de aprendizado no repositório):

- Estruturar um PDV full-stack separando **Blade (auth/shell)** de **SPA Vue** montada em um ponto de entrada.  
- Organizar regras de pedido em um **Service** com `DB::transaction`, validação de estoque e precisão decimal.  
- Expor uma **API REST versionada (`v1`)** consumida pelo front via Axios + CSRF.  
- Usar **Form Requests** e **Enums** tipados para status de pedido.  
- Controlar acesso à tela operacional com **middleware de sessão** Laravel.  
- Conviver, no mesmo repositório, com um **sistema legado procedural** e uma reescrita/evolução em Laravel — o que evidencia migração gradual de stack.  
- Limitações conscientes no front: menus “em preparação”, caixa só em sessão, status `paid` definido no enum mas não aplicado no fluxo de pagamento atual.

---

# Competências adquiridas

Competências **demonstráveis pelo código** deste repositório:

- PHP 8.2+ e Laravel 12 (rotas, middleware, Eloquent, migrations, seeders, validation)  
- Vue 3 Composition API (`script setup`), componentes e estado reativo  
- Integração Laravel + Vite + Tailwind CSS 4  
- Design de API JSON REST simples  
- Modelagem relacional pedido/itens/produto com FKs e cascade/restrict  
- Controle de estoque em operação de venda  
- Autenticação baseada em sessão e CSRF  
- PHP procedural + PDO + Bootstrap (legado)  
- Versionamento Git de um produto em evolução (legado → Laravel)

---

# Possíveis melhorias

Sugestões ancoradas em lacunas **observadas no código** (não inventadas como roadmap externo):

1. **Proteger rotas de `routes/api.php`** com autenticação (hoje products/orders não declaram middleware `auth` no arquivo de API).  
2. **Persistir caixa no banco** (abrir/fechar, saldo, operador, histórico) em vez de só sessão.  
3. **Marcar pedido como `paid`** (e eventualmente fluxo de `cancelled`) no pagamento; o enum existe, o front cria com default `open`.  
4. Implementar módulos **Produtos** e **Relatórios** sinalizados como “em preparação”.  
5. Adicionar campo real de **categoria** no produto (o front infere por regex no nome).  
6. **Fechamento de caixa**, sangria, múltiplos meios de pagamento — não existem.  
7. Ampliar **testes** (hoje só redirect guest e `assertTrue(true)`).  
8. Unificar ou remover o **legado PHP** (e assets `vendor/login` referenciados mas fora do fluxo Laravel); incluir `config.php` de exemplo ou documentar a ausência.  
9. Completar ou remover referências a `painel-gerente` / `painel-vendedor`.  
10. Segurança do legado: senha em texto, SQL com interpolação de sessão/IDs em vários pontos do painel.  
11. Documentar o domínio no README (hoje é o template Laravel).  
12. Tratar erro de estoque insuficiente com resposta HTTP adequada (hoje `InvalidArgumentException` sem handler específico visível no `bootstrap/app.php`).

---

# Resumo em até 10 linhas

Dona Joana POS é um PDV de café/pastelaria com UI Laravel 12 + Vue 3 para login, abertura de caixa, venda por grade de produtos, carrinho, troco e criação de pedidos com baixa de estoque. APIs REST cobrem produtos, pedidos e estado de caixa (este último só em sessão). O domínio de pedidos usa `OrderService`, Form Requests e enum `open|paid|cancelled`. Menus de produtos e relatórios no POS ainda não estão implementados. O mesmo repositório guarda um painel administrativo legado em PHP/PDO (usuários, produtos, categorias, fornecedores, compras, códigos de barras). Não há integrações externas de pagamento ou ERP. Testes automatizados são mínimos. O README não descreve o produto; esta análise baseia-se exclusivamente no código versionado.
)
