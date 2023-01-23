INSERT INTO roles(id, name, created_at, updated_at) VALUES
  (1, 'administrator', '2016-10-20 11:05:00', '2016-10-20 11:05:00'),
  (2, 'user', '2016-10-20 11:05:00', '2016-10-20 11:05:00');

INSERT INTO media(id, name, owner_id, is_public, link, created_at, updated_at, deleted_at) VALUES
  (1, 'Product main photo', 1 , true, 'http://localhost/test.jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00', null),
  (2, 'Category Photo photo', 1, false, 'http://localhost/test1.jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00', null);

INSERT INTO users(id, name, email, background_image_id, avatar_image_id, password, role_id, created_at, updated_at) VALUES
  (1, 'Mr Admin', 'admin@example.com', 1, 2, '$2y$10$X4receiTrF24bXrEbAiChOZ8TMNPqoXuhuThgynvBdWIHZeu5HzsS', 1, '2016-10-20 11:05:00', '2016-10-20 11:05:00'),
  (2, 'Another User', 'user@example.com', 1, 2, '$2y$10$X4receiTrF24bXrEbAiChOZ8TMNPqoXuhuThgynvBdWIHZeu5HzsS', 2, '2016-10-20 11:05:00', '2016-10-20 11:05:00');