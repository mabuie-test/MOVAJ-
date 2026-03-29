# MovaJá

Plataforma web MVC para marketplace logístico urbano em Moçambique (Maputo/Matola), conectando **merchants**, **riders** e **clientes finais** com rota real de mapa, cobrança antecipada C2B, OTP/PIN e payout B2C.

## Stack

- PHP 8.1+
- MySQL/MariaDB
- Composer + Dotenv
- PDO
- MVC clássico
- Bootstrap 5 + Fetch API

## Fases implementadas

1. **Fundação**: estrutura de projeto, bootstrap, core MVC, migrations e seeders.
2. **Auth multi-perfil**: registo/login para merchant, rider e admin com sessões segregadas.
3. **Mapa e rota real**: geocoding (Nominatim) e rota/ETA/distância (OSRM).
4. **Pedidos + quote engine**: cotação e criação de pedidos com persistência de coordenadas/rota/pricing.
5. **Cobrança C2B Débito API**: inicia e persiste transações mpesa/emola.
6. **Fila rider**: jobs disponíveis por cidade, aceite e atualização de estados com histórico.
7. **Tracking + OTP**: tracking por token e confirmação de entrega via OTP.
8. **Payout B2C Débito API**: disparo automático após confirmação de entrega.
9. **Dashboards**: painéis merchant, rider, admin com KPIs base.
10. **Relatórios/finanças/notificações/scripts**: consultas de receita/top merchants e jobs CLI operacionais.
11. **Revisão de segurança e arquitetura**: CSRF, prepared statements, separação por camadas e serviços.

## Arquitetura

- `app/Core`: Router, Request, Response, View, Database, Session.
- `app/Controllers`: HTTP orchestration por domínio.
- `app/Services`: regras de negócio (Auth, Route, Pricing, Order, Payment, Delivery, Report).
- `app/Repositories`: SQL e consultas especializadas.
- `app/Models`: entidades/tabelas.
- `app/Middleware`: CSRF/Auth.
- `app/Policies`: autorização por recurso.
- `database/migrations`: schema completo.
- `scripts`: automações operacionais.

## Instalação

```bash
composer install
cp .env.example .env
php database/migrate.php
php database/seed.php
php -S localhost:8000 -t public
```

## Variáveis `.env`

- Aplicação: `APP_*`
- Banco: `DB_*`
- Débito API: `DEBITO_*`
- Mapas: `MAP_PROVIDER`, `MAP_BASE_URL`, `GEOCODING_BASE_URL`
- Pricing: `BASE_DELIVERY_PRICE`, `PRICE_PER_KM`, `PLATFORM_COMMISSION_*`, `*_SURCHARGE`
- Operação: `OTP_EXPIRY_MINUTES`, `MAX_ASSIGNMENT_TIME_MINUTES`

## Rotas principais

### Público
- `GET /`
- `GET /track/{token}`
- `POST /track/{token}/otp`

### Auth
- `GET|POST /login`
- `GET|POST /register/merchant`
- `GET|POST /register/rider`
- `GET|POST /forgot-password`
- `GET /reset-password/{token}`
- `POST /reset-password`

### Merchant
- `GET /merchant/dashboard`
- `GET /merchant/orders`
- `GET /merchant/orders/create`
- `POST /merchant/orders/quote`
- `POST /merchant/orders`
- `GET /merchant/orders/{id}`
- `POST /merchant/orders/{id}/pay`

### Rider
- `GET /rider/dashboard`
- `GET /rider/jobs`
- `POST /rider/jobs/{id}/accept`
- `POST /rider/jobs/{id}/status`

### Admin
- `GET /admin`
- `GET /admin/reports`
- `POST /admin/riders/{id}/approve`

## Cron jobs

```bash
* * * * * php /path/scripts/check_pending_payments.php
* * * * * php /path/scripts/check_pending_payouts.php
*/5 * * * * php /path/scripts/expire_otps.php
*/5 * * * * php /path/scripts/reassign_stale_orders.php
0 23 * * * php /path/scripts/daily_metrics.php
```

## Credenciais seed

- Admin: `admin@movaja.local`
- Password hash seeded na migration/seeder (trocar em produção).

## Segurança

- PDO com prepared statements.
- CSRF em todas as rotas `POST`.
- Sessão regenerada em login.
- Escaping de saída nas views com helper `e()`.
- Tokens públicos para tracking.
- Histórico de status para rastreabilidade operacional.
