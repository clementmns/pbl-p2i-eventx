INSERT INTO roles (id, name) VALUES (0, 'user'), (1, 'admin')
	ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO users (id, mail, password, isActive, roleId)
VALUES (1, 'admin@admin.com', '$2y$10$wH8Qw6Qw6Qw6Qw6Qw6Qw6eQw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6', 1, 1)
	ON DUPLICATE KEY UPDATE mail=VALUES(mail), password=VALUES(password), isActive=VALUES(isActive), roleId=VALUES(roleId);
