-- =========================================================
-- PDV - Script de criação do banco (MySQL / MariaDB)
-- Rode no phpMyAdmin (aba SQL) ou: mysql -u root -p pdv < schema.mysql.sql
-- =========================================================

CREATE TABLE IF NOT EXISTS produtos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    cod             VARCHAR(50)    NOT NULL,
    nome            VARCHAR(150)   NOT NULL,
    categoria       VARCHAR(100),
    validade        DATE,
    quantidade      INT            NOT NULL DEFAULT 0,
    preco_unitario  DECIMAL(10,2)  NOT NULL DEFAULT 0,
    preco_pacote    DECIMAL(10,2)  NOT NULL DEFAULT 0,
    imagem          VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clientes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(150) NOT NULL,
    cpf         VARCHAR(14)  NOT NULL UNIQUE,
    email       VARCHAR(150),
    telefone    VARCHAR(20),
    endereco    VARCHAR(255),
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vendas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id       INT NULL,
    data_venda       DATETIME      NOT NULL,
    subtotal         DECIMAL(10,2) NOT NULL DEFAULT 0,
    desconto         DECIMAL(10,2) NOT NULL DEFAULT 0,
    total            DECIMAL(10,2) NOT NULL DEFAULT 0,
    forma_pagamento  VARCHAR(20)   NOT NULL,
    status           VARCHAR(20)   NOT NULL DEFAULT 'concluida',
    observacao       VARCHAR(255),
    INDEX idx_vendas_data (data_venda),
    CONSTRAINT fk_vendas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS itens_venda (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    venda_id        INT           NOT NULL,
    produto_id      INT           NULL,
    nome_produto    VARCHAR(150)  NOT NULL,
    quantidade      INT           NOT NULL,
    preco_unitario  DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_itens_venda FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
