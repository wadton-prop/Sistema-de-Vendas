-- =========================================================
-- Dados de exemplo (opcional). Funciona em MySQL, PostgreSQL e SQLite.
-- No SQLite é carregado automaticamente junto com o schema.
-- =========================================================

INSERT INTO produtos (cod, nome, categoria, validade, quantidade, preco_unitario, preco_pacote, imagem) VALUES
('7891000100103', 'Leite Integral 1L',     'Laticínios', '2027-03-10', 48, 5.49, 62.00, 'download (2).jpg'),
('7894900011517', 'Refrigerante Cola 2L',  'Bebidas',    '2027-06-01', 30, 9.99, 54.00, 'download (3).jpg'),
('7896004000015', 'Arroz Tipo 1 5kg',      'Mercearia',  '2027-12-31', 20, 27.90, 0.00, 'baixados.webp'),
('7896005800027', 'Feijão Carioca 1kg',    'Mercearia',  '2027-10-15', 35, 8.49, 0.00, NULL);

INSERT INTO clientes (nome, cpf, email, telefone, endereco) VALUES
('Maria Oliveira', '123.456.789-09', 'maria@email.com', '(65) 99999-1111', 'Rua das Flores, 100 - Cuiabá/MT'),
('João Santos',    '987.654.321-00', 'joao@email.com',  '(65) 98888-2222', 'Av. Brasil, 250 - Várzea Grande/MT');
