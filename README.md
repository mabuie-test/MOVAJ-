# MovaJá

Plataforma web MVC para marketplace logístico urbano em Moçambique (Maputo/Matola), conectando **merchants**, **riders** e **clientes finais** com rota real, dispatch por proximidade, tracking ao vivo, OTP/PIN, prova de entrega e carteira interna.

## Stack
- PHP 8.1+, MySQL/MariaDB, Composer, Dotenv, PDO
- MVC clássico (Controllers/Services/Repositories/Models/Views)
- Bootstrap 5 + Leaflet + Fetch API

## Evolução incremental implementada

### 1) Mapa interativo Leaflet
- Maps em: criação de pedido, detalhe do pedido merchant, detalhe do job rider, tracking público e dashboard admin.
- Reuso de `route_polyline`, distância e ETA sem recálculo desnecessário.
- Polling de tracking para mover marcador do rider.

### 2) Dispatch por proximidade
- `DispatchService` com elegibilidade, ranking por distância, reserva por tempo e reatribuição.
- Configuração por `.env`: `DISPATCH_MODE`, `DISPATCH_RADIUS_KM`, `ASSIGNMENT_RESERVATION_MINUTES`, `MAX_ACTIVE_JOBS_PER_RIDER`.
- Persistência em `order_dispatch_attempts`.

### 3) Tracking ao vivo
- `LiveTrackingService` + `rider_locations` para atualização periódica de localização.
- Endpoint público de live tracking por token (`/track/{token}/live`).

### 4) SLA operacional
- `SlaService` calcula tempos de recolha/entrega e classifica `on_time`, `delayed`, `critical_delay`.
- Persistência de métricas em colunas de `orders` (`pickup_delay_minutes`, `delivery_delay_minutes`, `sla_status` etc.).

### 5) Prova de entrega
- `ProofOfDeliveryService` para foto, assinatura base64, observações e resumo da prova.
- Persistência em `delivery_proofs`.

### 6) Wallet interna de riders
- `WalletService` com saldo disponível/pendente, crédito por entrega, histórico auditável e payout request.
- Tabelas: `rider_wallets`, `rider_wallet_transactions`, `rider_wallet_payout_requests`.
- Integração payout com Débito API e reconciliação de status.

## Migrations
- `001_create_tables.sql` (base)
- `002_operational_evolution.sql` (dispatch/live/SLA/proof/wallet)

## Scripts CLI
- `reassign_stale_orders.php`
- `reconcile_live_jobs.php`
- `check_pending_wallet_payouts.php`
- `reconcile_wallet_payout_statuses.php`
- `expire_assignment_reservations.php`
- `sla_daily_metrics.php`

## Instalação
```bash
composer install
cp .env.example .env
php database/migrate.php
php database/seed.php
php -S localhost:8000 -t public
```

## Segurança e consistência
- CSRF em POST, sessões regeneradas, prepared statements, políticas de acesso por papel.
- Riders só atualizam localização de pedidos atribuídos.
- OTP com limite de tentativas + expiração.
- Prova de entrega e transações de wallet auditáveis.
