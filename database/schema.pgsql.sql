-- =========================================================
-- PDV - Script de criação do banco (PostgreSQL / Neon)
-- Rode no SQL Editor do Neon ou: psql "SUA_URL" -f schema.pgsql.sql
-- =========================================================

CREATE TABLE IF NOT EXISTS produtos (
    id              SERIAL PRIMARY KEY,
    cod             VARCHAR(50)    NOT NULL,
    nome            VARCHAR(150)   NOT NULL,
    categoria       VARCHAR(100),
    validade        DATE,
    quantidade      INTEGER        NOT NULL DEFAULT 0,
    preco_unitario  NUMERIC(10,2)  NOT NULL DEFAULT 0,
    preco_pacote    NUMERIC(10,2)  NOT NULL DEFAULT 0,
    imagem          VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS clientes (
    id          SERIAL PRIMARY KEY,
    nome        VARCHAR(150) NOT NULL,
    cpf         VARCHAR(14)  NOT NULL UNIQUE,
    email       VARCHAR(150),
    telefone    VARCHAR(20),
    endereco    VARCHAR(255),
    criado_em   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendas (
    id               SERIAL PRIMARY KEY,
    cliente_id       INTEGER NULL REFERENCES clientes(id) ON DELETE SET NULL,
    data_venda       TIMESTAMP     NOT NULL,
    subtotal         NUMERIC(10,2) NOT NULL DEFAULT 0,
    desconto         NUMERIC(10,2) NOT NULL DEFAULT 0,
    total            NUMERIC(10,2) NOT NULL DEFAULT 0,
    forma_pagamento  VARCHAR(20)   NOT NULL,
    status           VARCHAR(20)   NOT NULL DEFAULT 'concluida',
    observacao       VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS itens_venda (
    id              SERIAL PRIMARY KEY,
    venda_id        INTEGER       NOT NULL REFERENCES vendas(id) ON DELETE CASCADE,
    produto_id      INTEGER       NULL REFERENCES produtos(id) ON DELETE SET NULL,
    nome_produto    VARCHAR(150)  NOT NULL,
    quantidade      INTEGER       NOT NULL,
    preco_unitario  NUMERIC(10,2) NOT NULL,
    subtotal        NUMERIC(10,2) NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_vendas_data ON vendas (data_venda);
