-- Assign admin role to the first registered user (bootstrap access)
-- Requires: 002_create_roles_tables.sql

USE fitcoch;

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
CROSS JOIN roles r
WHERE r.name = 'admin'
ORDER BY u.id ASC
LIMIT 1;
