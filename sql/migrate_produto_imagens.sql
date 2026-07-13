-- Multi-imagens por produto (lojas já instaladas)
USE nu_chao;

CREATE TABLE IF NOT EXISTS produto_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    ficheiro VARCHAR(255) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_produto_ordem (produto_id, ordem),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO produto_imagens (produto_id, ficheiro, ordem)
SELECT p.id, p.imagem, 0
FROM produtos p
WHERE p.imagem IS NOT NULL AND p.imagem != ''
  AND NOT EXISTS (
    SELECT 1 FROM produto_imagens pi WHERE pi.produto_id = p.id AND pi.ficheiro = p.imagem
  );
