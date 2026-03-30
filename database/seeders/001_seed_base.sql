INSERT INTO cities (name, code, is_active) VALUES
('Maputo', 'MPT', 1),
('Matola', 'MTL', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO service_areas (city_id, name, is_active, special_fee, coverage_enabled)
SELECT id, 'Central', 1, 0, 1 FROM cities WHERE code='MPT'
UNION ALL
SELECT id, 'Costa do Sol', 1, 15, 1 FROM cities WHERE code='MPT'
UNION ALL
SELECT id, 'Machava', 1, 10, 1 FROM cities WHERE code='MTL';

INSERT INTO admins (name, email, password_hash)
VALUES ('Super Admin', 'admin@movaja.local', '$2y$10$fT9GjSEQPgS4lsHwDSwVGep3JxU3fN8P6tM2vIhmf6fOMY8qO03x2')
ON DUPLICATE KEY UPDATE name=VALUES(name);
