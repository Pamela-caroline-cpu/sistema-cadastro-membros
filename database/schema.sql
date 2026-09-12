-- Sistema de Cadastro de Membros - Primeira Igreja Quadrangular de Dois Vizinhos - PR
-- Script de criação do banco de dados e carga inicial (dados de exemplo)

CREATE DATABASE IF NOT EXISTS igreja_membros
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE igreja_membros;

CREATE TABLE IF NOT EXISTS membros (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nome              VARCHAR(150) NOT NULL,
    data_nascimento   DATE NULL,
    cpf               VARCHAR(14) NULL UNIQUE,
    telefone          VARCHAR(20) NULL,
    email             VARCHAR(150) NULL,
    endereco          VARCHAR(200) NULL,
    bairro            VARCHAR(100) NULL,
    cidade            VARCHAR(100) NULL,
    estado            CHAR(2) NULL,
    cep               VARCHAR(10) NULL,
    data_batismo      DATE NULL,
    funcao            VARCHAR(50) NOT NULL DEFAULT 'Membro',
    status            ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
    senha             VARCHAR(255) NULL, -- hash da senha; só é usado por quem tem funcao <> 'Membro' (acesso de administrador)
    observacoes       TEXT NULL,
    data_cadastro     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Dados de exemplo para teste/demonstração
-- As contas de liderança abaixo (função diferente de "Membro") já têm senha de demonstração
-- definida como "quadrangular2026" (hash bcrypt) — troque antes de usar em produção.
INSERT INTO membros (nome, data_nascimento, cpf, telefone, email, endereco, bairro, cidade, estado, cep, data_batismo, funcao, status, senha, observacoes) VALUES
('Maria Aparecida Souza', '1978-04-12', '123.456.789-01', '(46) 99911-2233', 'maria.souza@email.com', 'Rua das Flores, 120', 'Centro', 'Dois Vizinhos', 'PR', '85660-000', '1995-06-18', 'Líder de Louvor', 'Ativo', '$2y$12$xz2Lzz5.9iuWfGVXdWfvzO9V5m/HJkA5qo/UTOVLaD55rEwl4CcVi', 'Participa do ministério de música há mais de 10 anos.'),
('João Batista Ferreira', '1985-11-03', '234.567.890-12', '(46) 99822-3344', 'joao.ferreira@email.com', 'Av. Brasil, 456', 'Jardim América', 'Dois Vizinhos', 'PR', '85660-010', '2001-09-02', 'Diácono', 'Ativo', '$2y$12$xz2Lzz5.9iuWfGVXdWfvzO9V5m/HJkA5qo/UTOVLaD55rEwl4CcVi', NULL),
('Ana Paula Lima', '2000-02-27', '345.678.901-23', '(46) 99733-4455', 'ana.lima@email.com', 'Rua São Pedro, 78', 'Vila Nova', 'Dois Vizinhos', 'PR', '85660-020', '2015-12-13', 'Membro', 'Ativo', NULL, 'Envolvida no ministério infantil.'),
('Carlos Eduardo Santos', '1962-07-19', '456.789.012-34', '(46) 99644-5566', 'carlos.santos@email.com', 'Rua Paraná, 33', 'Centro', 'Dois Vizinhos', 'PR', '85660-000', '1980-03-09', 'Pastor Auxiliar', 'Ativo', '$2y$12$xz2Lzz5.9iuWfGVXdWfvzO9V5m/HJkA5qo/UTOVLaD55rEwl4CcVi', NULL),
('Fernanda Oliveira Costa', '1993-09-30', '567.890.123-45', '(46) 99555-6677', 'fernanda.costa@email.com', 'Rua Santa Catarina, 210', 'Bela Vista', 'Dois Vizinhos', 'PR', '85660-030', NULL, 'Membro', 'Inativo', NULL, 'Mudou-se de cidade em 2024.');
