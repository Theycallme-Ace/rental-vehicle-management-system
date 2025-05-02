-- SQL to insert default superadmin user for MySQL
INSERT INTO users (username, email, password, role) VALUES 
('superadmin', 'admin@rental.com', '$2y$10$iQPvLG4G4ntEj76dpp4CQuckklh2uBOKKK7fzjchnxh3vRpJxo.PG', 'superadmin');
