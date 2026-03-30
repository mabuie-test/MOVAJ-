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
VALUES ('Super Admin', 'teste@mova.com', '$2y$12$vHH4ZZEUj/LSfg4dHrfsVOBbYE695uWFjnpiBj4xW1XuTkYkDDFk2')
ON DUPLICATE KEY UPDATE name=VALUES(name);
