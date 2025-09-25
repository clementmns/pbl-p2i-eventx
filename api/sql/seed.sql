
INSERT INTO roles (id, name) VALUES
	(1, 'user'),
	(2, 'admin')
	ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO users (id, mail, password, created_at, update_at, isActive, roleId) VALUES
	(1, 'admin@admin.com', '$2y$10$wH8Qw6Qw6Qw6Qw6Qw6Qw6eQw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6', '2025-09-25 10:00:00', '2025-09-25 10:00:00', 1, 2),
	(2, 'user@user.com', '$2y$10$abcdefabcdefabcdefabcdefabcdefabcdefabcdefabcdefabcdef', '2025-09-25 11:00:00', '2025-09-25 11:00:00', 1, 1)
	ON DUPLICATE KEY UPDATE mail=VALUES(mail), password=VALUES(password), created_at=VALUES(created_at), update_at=VALUES(update_at), isActive=VALUES(isActive), roleId=VALUES(roleId);

INSERT INTO profiles (id, firstName, lastName, pictures, description, userId) VALUES
	(1, 'Admin', 'User', 'admin.jpg', 'Administrator profile', 1),
	(2, 'Regular', 'User', 'user.jpg', 'Regular user profile', 2)
	ON DUPLICATE KEY UPDATE firstName=VALUES(firstName), lastName=VALUES(lastName), pictures=VALUES(pictures), description=VALUES(description), userId=VALUES(userId);

INSERT INTO events (id, name, description, startDate, endDate, place, userId) VALUES
	(1, 'Launch Event', 'Project launch event', '2025-10-01 09:00:00', '2025-10-01 12:00:00', 'Main Hall', 1),
	(2, 'Workshop', 'Technical workshop', '2025-10-05 14:00:00', '2025-10-05 17:00:00', 'Room 101', 2)
	ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), startDate=VALUES(startDate), endDate=VALUES(endDate), place=VALUES(place), userId=VALUES(userId);

INSERT INTO registrations (idUser, idEvent, createdAt, updateAt) VALUES
	(2, 1, '2025-09-25 12:00:00', '2025-09-25 12:00:00'),
	(2, 2, '2025-09-25 12:05:00', '2025-09-25 12:05:00')
	ON DUPLICATE KEY UPDATE createdAt=VALUES(createdAt), updateAt=VALUES(updateAt);

INSERT INTO wishlists (idUser, idEvent, createdAt, updateAt) VALUES
	(2, 1, '2025-09-25 13:00:00', '2025-09-25 13:00:00'),
	(2, 2, '2025-09-25 13:05:00', '2025-09-25 13:05:00')
	ON DUPLICATE KEY UPDATE createdAt=VALUES(createdAt), updateAt=VALUES(updateAt);
