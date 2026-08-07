# Análise do Projeto

## Objetivo

Disponibilizar um PDV utilizável em balcão de cafeteria: autenticação, loja, ciclo de caixa, vendas com pagamentos, recibo/comanda, catálogo, dashboard, relatórios e configurações da empresa — num único repositório full-stack adequado a portfólio.

## Arquitetura

- **Backend:** Laravel 12 com camadas `Controller → Service → Model`
- **Frontend:** Vue 3 (Composition API) montado numa view Blade autenticada
- **Estado UI:** local em `PosPage.vue` (sem Vue Router / sem Pinia)
- **API:** `/api/v1` via Axios com cookie de sessão e CSRF
- **Domínio tipado:** Enums (`CashStatus`, `PaymentMethod`, `OrderStatus`) + Form Requests

## Decisões técnicas

| Decisão | Motivo |
|---------|--------|
| Services dedicados | Controllers finos; regras centralizadas e reutilizáveis |
| Sessão web (não Sanctum token) | Operador no mesmo browser; CSRF suficiente |
| Sem Vue Router / Pinia | POS monólito numa página autenticada |
| Caixa persistido em `cash_registers` | Fonte de verdade para vendas e histórico |
| Impressão HTML (`window.print`) | Recibo/comanda sem PDF nem libs externas |
| Lazy loading de módulos Vue | Boot leve no ecrã de vendas |
| Chart.js + vue-sonner | Gráficos e feedback UX sem stack extra |

## Melhorias futuras

- Impressão térmica nativa
- Gestão de clientes
- Cupões / descontos
- Multiempresa
- Estoque avançado (movimentos, alertas)
- PWA / modo offline
- Testes automatizados (Feature / Component)
- Form Requests para filtros de cash e reports
