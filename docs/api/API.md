# API

Base: `/api/v1`  
Formato: JSON  
Auth: sessão Laravel + CSRF (`X-CSRF-TOKEN`) nas rotas protegidas

Resposta típica:

```json
{ "message": "...", "data": {} }
```

---

## Web

| Method | Path | Auth | Descrição |
|--------|------|------|-----------|
| `GET` | `/login` | guest | Ecrã de login |
| `POST` | `/login` | guest | Autenticar |
| `POST` | `/logout` | auth | Terminar sessão |
| `GET` | `/` | auth | POS (Vue) |

---

## Products

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/products` | — |
| `GET` | `/api/v1/products/{id}` | — |
| `POST` | `/api/v1/products` | auth |
| `PUT` | `/api/v1/products/{id}` | auth |
| `POST` | `/api/v1/products/{id}/activate` | auth |
| `POST` | `/api/v1/products/{id}/deactivate` | auth |
| `DELETE` | `/api/v1/products/{id}` | auth |

**Query (`GET` list):** `category_id`, `search`, `all`, `is_active`, `is_favorite`

**Body (create/update):** `category_id`, `name`, `sku`, `description`, `price`, `stock`, `image`, `button_color`, `sort_order`, `is_active`, `is_favorite`

---

## Categories

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/categories` | — |
| `GET` | `/api/v1/categories/active` | — |
| `GET` | `/api/v1/categories/{id}` | — |
| `POST` | `/api/v1/categories` | auth |
| `PUT` | `/api/v1/categories/{id}` | auth |
| `POST` | `/api/v1/categories/{id}/activate` | auth |
| `POST` | `/api/v1/categories/{id}/deactivate` | auth |
| `DELETE` | `/api/v1/categories/{id}` | auth |

**Body (create/update):** `name`, `slug`, `color`, `sort_order`, `is_active`

---

## Stores

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/stores` | — |
| `GET` | `/api/v1/stores/active` | — |

---

## Orders

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/orders` | — |
| `POST` | `/api/v1/orders` | — |
| `GET` | `/api/v1/orders/{id}` | — |
| `POST` | `/api/v1/orders/{orderId}/items` | — |

**Body (`POST /orders`):** `cash_register_id`, `status`, `payment_method`, `items[]` (`product_id`, `quantity`)

**Body (`POST .../items`):** `product_id`, `quantity`

---

## Cash

| Method | Path | Auth |
|--------|------|------|
| `POST` | `/api/v1/cash/open` | auth |
| `GET` | `/api/v1/cash/current` | auth |
| `POST` | `/api/v1/cash/close` | auth |
| `GET` | `/api/v1/cash/history` | auth |
| `GET` | `/api/v1/cash/{id}` | auth |

**Body (open):** `store_id`, `opening_balance`  
**Body (close):** `closing_balance`, `notes` (opcional)  
**Query (history):** `store_id`, `status`, `from`, `to`, `page`

---

## Dashboard

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/dashboard` | auth |

---

## Settings

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/settings` | auth |
| `GET` | `/api/v1/settings/current` | auth |
| `POST` | `/api/v1/settings` | auth |
| `POST` | `/api/v1/settings/current` | auth |
| `GET` | `/api/v1/settings/{id}` | auth |
| `PUT` | `/api/v1/settings/{id}` | auth |
| `POST` | `/api/v1/settings/{id}` | auth |
| `DELETE` | `/api/v1/settings/{id}` | auth |

**Body:** dados da empresa (`company_name`, `trade_name`, `tax_number`, `phone`, `address`, `vat`, `receipt_footer`, `logo`, …)

---

## Reports

| Method | Path | Auth |
|--------|------|------|
| `GET` | `/api/v1/reports/summary` | auth |
| `GET` | `/api/v1/reports/sales` | auth |
| `GET` | `/api/v1/reports/products` | auth |
| `GET` | `/api/v1/reports/categories` | auth |
| `GET` | `/api/v1/reports/payments` | auth |
| `GET` | `/api/v1/reports/cash-registers` | auth |

**Query comum:** `from`, `to`, `store_id` (conforme endpoint)
