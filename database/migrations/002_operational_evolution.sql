ALTER TABLE orders
  ADD COLUMN accepted_at DATETIME NULL,
  ADD COLUMN pickup_arrived_at DATETIME NULL,
  ADD COLUMN picked_up_at DATETIME NULL,
  ADD COLUMN in_transit_at DATETIME NULL,
  ADD COLUMN delivered_at DATETIME NULL,
  ADD COLUMN pickup_delay_minutes INT NULL,
  ADD COLUMN delivery_delay_minutes INT NULL,
  ADD COLUMN sla_status VARCHAR(30) NULL;

CREATE TABLE IF NOT EXISTS order_dispatch_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  rider_id BIGINT UNSIGNED NOT NULL,
  distance_to_pickup_km DECIMAL(10,2) NOT NULL,
  rank_score DECIMAL(10,4) NOT NULL,
  dispatch_status VARCHAR(30) NOT NULL,
  reserved_until DATETIME NULL,
  responded_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_dispatch_order(order_id, dispatch_status),
  CONSTRAINT fk_dispatch_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_dispatch_rider FOREIGN KEY (rider_id) REFERENCES riders(id)
);

CREATE TABLE IF NOT EXISTS rider_locations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rider_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  lat DECIMAL(10,7) NOT NULL,
  lng DECIMAL(10,7) NOT NULL,
  heading DECIMAL(6,2) NULL,
  speed DECIMAL(8,2) NULL,
  accuracy DECIMAL(8,2) NULL,
  source VARCHAR(30) DEFAULT 'gps',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rider_locations(rider_id, created_at),
  CONSTRAINT fk_rider_location_rider FOREIGN KEY (rider_id) REFERENCES riders(id),
  CONSTRAINT fk_rider_location_order FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE IF NOT EXISTS delivery_proofs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  rider_id BIGINT UNSIGNED NOT NULL,
  recipient_name VARCHAR(180) NULL,
  delivery_photo_path VARCHAR(255) NULL,
  recipient_signature_path VARCHAR(255) NULL,
  otp_validated TINYINT(1) NOT NULL DEFAULT 0,
  delivered_at DATETIME NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_proof_order(order_id),
  CONSTRAINT fk_delivery_proof_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_delivery_proof_rider FOREIGN KEY (rider_id) REFERENCES riders(id)
);

CREATE TABLE IF NOT EXISTS rider_wallets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rider_id BIGINT UNSIGNED NOT NULL,
  available_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  pending_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_credited DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_paid_out DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_wallet_rider(rider_id),
  CONSTRAINT fk_wallet_rider FOREIGN KEY (rider_id) REFERENCES riders(id)
);

CREATE TABLE IF NOT EXISTS rider_wallet_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rider_wallet_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  type VARCHAR(30) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  balance_before DECIMAL(12,2) NOT NULL,
  balance_after DECIMAL(12,2) NOT NULL,
  reference VARCHAR(120) NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_wallet_tx(rider_wallet_id, created_at),
  CONSTRAINT fk_wallet_tx_wallet FOREIGN KEY (rider_wallet_id) REFERENCES rider_wallets(id),
  CONSTRAINT fk_wallet_tx_order FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE IF NOT EXISTS rider_wallet_payout_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rider_wallet_id BIGINT UNSIGNED NOT NULL,
  provider VARCHAR(30) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  debito_reference VARCHAR(120) NULL,
  raw_response JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_wallet_payout_status(status, created_at),
  CONSTRAINT fk_wallet_payout_wallet FOREIGN KEY (rider_wallet_id) REFERENCES rider_wallets(id)
);
