-- =============================================
--  MenosPlástico — Script do Banco de Dados
--  Execute isso no phpMyAdmin da sua hospedagem
-- =============================================

CREATE DATABASE IF NOT EXISTS menosplastico
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE menosplastico;

CREATE TABLE IF NOT EXISTS contatos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(150)  NOT NULL,
  email       VARCHAR(200)  NOT NULL,
  assunto     VARCHAR(100)  NOT NULL,
  mensagem    TEXT          NOT NULL,
  ip          VARCHAR(45)   DEFAULT NULL,
  criado_em   DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
