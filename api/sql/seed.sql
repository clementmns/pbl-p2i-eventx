INSERT INTO roles (id, name) VALUES (1, 'user'), (2, 'admin')
	ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO users (id, mail, password, isActive, roleId)
VALUES (1, 'admin@admin.com', '$2y$10$wH8Qw6Qw6Qw6Qw6Qw6Qw6eQw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6', 1, 2)
	ON DUPLICATE KEY UPDATE mail=VALUES(mail), password=VALUES(password), isActive=VALUES(isActive), roleId=VALUES(roleId);
