-- ============================================
-- E-Commerce COD - Schema Inicial
-- Banco de Dados: MySQL
-- ============================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS ecommerce_cod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_cod;

-- ============================================
-- Tabela: usuarios
-- ============================================
CREATE TABLE usuarios (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('CLIENTE', 'ADMIN', 'ENTREGADOR') NOT NULL DEFAULT 'CLIENTE',
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_telefone (telefone),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: zonas_entrega
-- ============================================
CREATE TABLE zonas_entrega (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nome_bairro VARCHAR(100) NOT NULL,
    descricao_zona VARCHAR(255),
    taxa_entrega DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    tempo_estimado_entrega VARCHAR(50),
    ativa BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_nome_bairro (nome_bairro),
    INDEX idx_ativa (ativa),
    INDEX idx_taxa_entrega (taxa_entrega)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: produtos
-- ============================================
CREATE TABLE produtos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagem_url VARCHAR(500),
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nome (nome),
    INDEX idx_preco (preco),
    INDEX idx_ativo (ativo),
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: pedidos
-- ============================================
CREATE TABLE pedidos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT NOT NULL,
    zona_id BIGINT NOT NULL,
    endereco_detalhado VARCHAR(500) NOT NULL,
    ponto_referencia VARCHAR(255),
    metodo_pagamento ENUM('DINHEIRO', 'TPA_CARTAO', 'TRANSFERENCIA', 'MOBILE_MONEY') NOT NULL,
    precisa_troco_para DECIMAL(10, 2) NULL COMMENT 'Valor para o qual precisa de troco, NULL se não precisa',
    status_pedido ENUM('PENDENTE', 'A_CAMINHO', 'ENTREGUE', 'CANCELADO') NOT NULL DEFAULT 'PENDENTE',
    status_pagamento ENUM('PENDENTE', 'PAGO_NA_ENTREGA') NOT NULL DEFAULT 'PENDENTE',
    total_produtos DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_entrega DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    valor_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (zona_id) REFERENCES zonas_entrega(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_zona_id (zona_id),
    INDEX idx_status_pedido (status_pedido),
    INDEX idx_status_pagamento (status_pagamento),
    INDEX idx_criado_em (criado_em),
    INDEX idx_pedido_pendente (status_pedido, criado_em) COMMENT 'Índice composto para busca rápida de pedidos pendentes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: itens_pedido
-- ============================================
CREATE TABLE itens_pedido (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT NOT NULL,
    produto_id BIGINT NOT NULL,
    quantidade INT NOT NULL CHECK (quantidade > 0),
    preco_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_produto_id (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Dados Iniciais (Seed Data)
-- ============================================

-- Inserir usuário administrador padrão
INSERT INTO usuarios (nome_completo, email, telefone, senha_hash, tipo_usuario) 
VALUES ('Administrador', 'admin@ecommerce.com', '+244900000000', '$2a$10$N.zmdr9k7uOCQb376NoUnuTJ8iAt6Z5EHsM8lE9lBOsl7iAt6Z5EH', 'ADMIN');

-- Inserir zonas de entrega exemplo (adaptar para localização real)
INSERT INTO zonas_entrega (nome_bairro, descricao_zona, taxa_entrega, tempo_estimado_entrega) VALUES
('Centro', 'Zona central da cidade', 500.00, '30-45 minutos'),
('Talatona', 'Zona sul', 1500.00, '45-60 minutos'),
('Kilamba', 'Zona leste', 2000.00, '60-90 minutos'),
('Viana', 'Zona industrial', 1800.00, '50-70 minutos'),
('Samba', 'Zona oeste', 1200.00, '40-50 minutos');

-- Inserir produtos exemplo
INSERT INTO produtos (nome, descricao, preco, stock, imagem_url) VALUES
('Arroz 5kg', 'Arroz branco premium', 3500.00, 50, '/images/arroz.jpg'),
('Feijão 1kg', 'Feijão preto', 1200.00, 30, '/images/feijao.jpg'),
('Óleo 1L', 'Óleo de soja', 800.00, 40, '/images/oleo.jpg'),
('Açúcar 1kg', 'Açúcar branco', 600.00, 60, '/images/acucar.jpg'),
('Farinha 1kg', 'Farinha de trigo', 450.00, 35, '/images/farinha.jpg'),
('Tomate 1kg', 'Tomate fresco', 1500.00, 25, '/images/tomate.jpg'),
('Cebola 1kg', 'Cebola amarela', 900.00, 40, '/images/cebola.jpg'),
('Carne 1kg', 'Carne bovina', 5000.00, 20, '/images/carne.jpg'),
('Frango 1kg', 'Frango inteiro', 3500.00, 30, '/images/frango.jpg'),
('Peixe 1kg', 'Peixe fresco', 4000.00, 15, '/images/peixe.jpg');

-- ============================================
-- Triggers para garantir integridade
-- ============================================

DELIMITER //

-- Trigger para atualizar subtotal do item_pedido automaticamente
CREATE TRIGGER trg_itens_pedido_subtotal_before_insert
BEFORE INSERT ON itens_pedido
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.quantidade * NEW.preco_unitario;
END //

CREATE TRIGGER trg_itens_pedido_subtotal_before_update
BEFORE UPDATE ON itens_pedido
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.quantidade * NEW.preco_unitario;
END //

-- Trigger para atualizar valor_total do pedido quando itens mudam
CREATE TRIGGER trg_pedidos_atualizar_total_after_insert
AFTER INSERT ON itens_pedido
FOR EACH ROW
BEGIN
    UPDATE pedidos p
    SET p.total_produtos = (
        SELECT COALESCE(SUM(subtotal), 0) 
        FROM itens_pedido 
        WHERE pedido_id = NEW.pedido_id
    ),
    p.valor_total = p.total_produtos + p.total_entrega
    WHERE p.id = NEW.pedido_id;
END //

CREATE TRIGGER trg_pedidos_atualizar_total_after_update
AFTER UPDATE ON itens_pedido
FOR EACH ROW
BEGIN
    UPDATE pedidos p
    SET p.total_produtos = (
        SELECT COALESCE(SUM(subtotal), 0) 
        FROM itens_pedido 
        WHERE pedido_id = NEW.pedido_id
    ),
    p.valor_total = p.total_produtos + p.total_entrega
    WHERE p.id = NEW.pedido_id;
END //

CREATE TRIGGER trg_pedidos_atualizar_total_after_delete
AFTER DELETE ON itens_pedido
FOR EACH ROW
BEGIN
    UPDATE pedidos p
    SET p.total_produtos = (
        SELECT COALESCE(SUM(subtotal), 0) 
        FROM itens_pedido 
        WHERE pedido_id = OLD.pedido_id
    ),
    p.valor_total = p.total_produtos + p.total_entrega
    WHERE p.id = OLD.pedido_id;
END //

DELIMITER ;

-- ============================================
-- Views para consultas frequentes
-- ============================================

-- View para pedidos pendentes com detalhes
CREATE VIEW vw_pedidos_pendentes AS
SELECT 
    p.id,
    p.criado_em,
    u.nome_completo AS cliente_nome,
    u.telefone AS cliente_telefone,
    z.nome_bairro,
    p.endereco_detalhado,
    p.ponto_referencia,
    p.metodo_pagamento,
    p.precisa_troco_para,
    p.valor_total,
    p.status_pedido
FROM pedidos p
JOIN usuarios u ON p.usuario_id = u.id
JOIN zonas_entrega z ON p.zona_id = z.id
WHERE p.status_pedido = 'PENDENTE'
ORDER BY p.criado_em ASC;

-- View para produtos com stock baixo
CREATE VIEW vw_produtos_stock_baixo AS
SELECT 
    id,
    nome,
    descricao,
    preco,
    stock,
    imagem_url
FROM produtos
WHERE stock <= 10 AND ativo = TRUE
ORDER BY stock ASC;

-- ============================================
-- Finalização
-- ============================================
-- Script concluído com sucesso
