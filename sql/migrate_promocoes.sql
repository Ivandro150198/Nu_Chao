-- Migração: promoções, stock alerta e configurações do site
USE nu_chao;

ALTER TABLE produtos
  ADD COLUMN IF NOT EXISTS preco_promocional DECIMAL(12, 2) NULL AFTER preco,
  ADD COLUMN IF NOT EXISTS em_promocao TINYINT(1) NOT NULL DEFAULT 0 AFTER preco_promocional,
  ADD COLUMN IF NOT EXISTS stock_alerta INT NOT NULL DEFAULT 5 AFTER stock;

CREATE TABLE IF NOT EXISTS promocoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    mensagem VARCHAR(500) NOT NULL,
    tipo ENUM('BANNER', 'ALERTA', 'CUPOM') NOT NULL DEFAULT 'ALERTA',
    desconto_percent INT NULL,
    codigo_cupom VARCHAR(40) NULL,
    produto_id INT NULL,
    data_inicio DATETIME NULL,
    data_fim DATETIME NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(100) PRIMARY KEY,
    valor TEXT NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO configuracoes (chave, valor) VALUES
('mostrar_stock', '1'),
('stock_alerta_global', '5'),
('alerta_promocao_activo', '1'),
('alerta_promocao_texto', 'Promoção activa: frete especial em Bandim este fim de semana!'),
('loja_aberta', '1'),
('mensagem_loja_fechada', 'Estamos temporariamente encerrados. Volte em breve.'),
('whatsapp_loja', '245955000000'),
('mostrar_precos', '1');

INSERT INTO promocoes (titulo, mensagem, tipo, desconto_percent, activo, data_inicio, data_fim)
SELECT 'Lançamento Nu Chao', 'Descontos em peças seleccionadas — pague na entrega!', 'ALERTA', 10, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)
WHERE NOT EXISTS (SELECT 1 FROM promocoes LIMIT 1);

UPDATE produtos SET em_promocao = 1, preco_promocional = ROUND(preco * 0.9, 0)
WHERE id IN (1, 4, 7) AND (preco_promocional IS NULL OR preco_promocional = 0);
