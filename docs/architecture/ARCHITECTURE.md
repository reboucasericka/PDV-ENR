# Arquitetura

## Visão geral

```
Browser (Vue 3)
      │  Axios + sessão + CSRF
      ▼
Laravel (Blade: login / pos)
      │
      ├─ web.php  → Auth + view POS
      └─ api.php  → /api/v1
              │
              ▼
       Controller
              │
              ▼
           Service
              │
              ▼
      Model / Enum / DB
```

## Laravel

- Rotas web para autenticação e shell do POS
- Rotas API versionadas em `/api/v1`
- Validação com Form Requests
- Domínio tipado com Enums (`CashStatus`, `PaymentMethod`, `OrderStatus`)

## Fluxo Controller → Service → Model

| Camada | Pasta | Responsabilidade |
|--------|-------|------------------|
| Controller | `app/Http/Controllers/Api/V1` | HTTP, validação, JSON |
| Service | `app/Services` | Regras de negócio |
| Model | `app/Models` | Persistência Eloquent |

Controllers permanecem finos. Toda regra de domínio vive no Service correspondente.

### Controllers

`CashController`, `CategoryController`, `DashboardController`, `OrderController`, `ProductController`, `ReportController`, `SettingController`, `StoreController`

### Services

`CashService`, `CategoryService`, `DashboardService`, `OrderService`, `ProductService`, `ReportService`, `SettingService`, `StoreService`

### Models

`User`, `Store`, `CashRegister`, `Category`, `Product`, `Order`, `OrderItem`, `Setting`

## Vue

- Entry: `resources/js/pos/main.js` → `PosPage.vue`
- Composition API (`<script setup>`)
- Navegação por estado local (sem Vue Router)
- Estado de venda por props/emits (sem Pinia)
- Módulos pesados com `defineAsyncComponent`
- Estilos: Tailwind CSS 4 via Vite

## Organização das pastas

```
app/
├── Enums/
├── Http/
│   ├── Controllers/Api/V1/
│   └── Requests/
├── Models/
└── Services/

resources/
├── css/          # app.css, print.css
├── js/pos/
│   ├── pages/
│   ├── components/
│   └── main.js
└── views/
    ├── auth/login.blade.php
    └── pos.blade.php

routes/
├── api.php
└── web.php
```
