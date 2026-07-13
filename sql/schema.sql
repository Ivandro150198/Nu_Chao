-- Nu Chao - Loja de Roupas e Acessórios (Guiné-Bissau)
-- Pagamento na entrega (COD) + gestão + entregadores
CREATE DATABASE IF NOT EXISTS nu_chao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nu_chao;

DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS promocoes;
DROP TABLE IF EXISTS hero_slides;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS zonas_entrega;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS configuracoes;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(30) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    tipo ENUM('CLIENTE', 'ADMIN', 'ENTREGADOR') NOT NULL DEFAULT 'CLIENTE',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    aprovado TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Entregadores precisam de aprovação do admin',
    google_id VARCHAR(64) NULL UNIQUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zonas_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    taxa DECIMAL(12, 2) NOT NULL DEFAULT 0,
    tempo_estimado VARCHAR(50),
    ativa TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria ENUM('ROUPA', 'ACESSORIO') NOT NULL DEFAULT 'ROUPA',
    preco DECIMAL(12, 2) NOT NULL,
    preco_promocional DECIMAL(12, 2) NULL,
    em_promocao TINYINT(1) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    stock_alerta INT NOT NULL DEFAULT 5,
    tamanhos VARCHAR(100) DEFAULT 'Único',
    imagem VARCHAR(500) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produto_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    ficheiro VARCHAR(255) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_produto_ordem (produto_id, ordem),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE promocoes (
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

CREATE TABLE configuracoes (
    chave VARCHAR(100) PRIMARY KEY,
    valor TEXT NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    texto VARCHAR(500) NOT NULL,
    nota VARCHAR(255) NULL,
    cta_texto VARCHAR(80) NOT NULL DEFAULT 'Ver coleção',
    cta_href VARCHAR(255) NOT NULL DEFAULT '#produtos',
    imagem VARCHAR(500) DEFAULT 'assets/hero-banner.png',
    ordem INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,
    entregador_id INT NULL,
    zona_id INT NOT NULL,
    endereco VARCHAR(500) NOT NULL,
    ponto_referencia VARCHAR(255),
    telefone_contacto VARCHAR(30) NOT NULL,
    metodo_pagamento ENUM('DINHEIRO', 'TPA', 'MOBILE_MONEY', 'Orange Money') NOT NULL DEFAULT 'DINHEIRO',
    precisa_troco_para DECIMAL(12, 2) NULL,
    status_pedido ENUM('PENDENTE', 'CONFIRMADO', 'A_CAMINHO', 'ENTREGUE', 'CANCELADO') NOT NULL DEFAULT 'PENDENTE',
    status_pagamento ENUM('PENDENTE', 'PAGO_NA_ENTREGA', 'CANCELADO') NOT NULL DEFAULT 'PENDENTE',
    total_produtos DECIMAL(12, 2) NOT NULL DEFAULT 0,
    taxa_entrega DECIMAL(12, 2) NOT NULL DEFAULT 0,
    valor_total DECIMAL(12, 2) NOT NULL DEFAULT 0,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (entregador_id) REFERENCES usuarios(id),
    FOREIGN KEY (zona_id) REFERENCES zonas_entrega(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    tamanho VARCHAR(20) DEFAULT 'Único',
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Senhas: admin123 / entrega123 / cliente123
INSERT INTO usuarios (nome, email, telefone, senha_hash, tipo, ativo, aprovado) VALUES
('Administrador Nu Chao', 'admin@nochao.gw', '+245955000001', '$2y$10$dQ/jB9FjiWrx1pQISjooDeZUZujpobxVoDBEsWwYUIrF5wX6CwWSa', 'ADMIN', 1, 1),
('Mamadu Entregador', 'entregador@nochao.gw', '+245955000002', '$2y$10$Rn88dqGYeNlkNaGxkkITIud0aMPeqcqjbZdsMj3ERsi738UNxQSsq', 'ENTREGADOR', 1, 1),
('Cliente Demo', 'cliente@nochao.gw', '+245955000003', '$2y$10$e/hL44FNNLI.SrayxgfAaOO9I8XMUObJKLe1HsFJ.r.N0gJRCk0cK', 'CLIENTE', 1, 1);

INSERT INTO zonas_entrega (nome, descricao, taxa, tempo_estimado) VALUES
('Bissau Centro', 'Bissau Velho, Praça, porto', 500, '30-45 min'),
('Bandim', 'Mercado de Bandim e arredores', 700, '40-60 min'),
('Antula', 'Antula e zona residencial', 800, '45-70 min'),
('Bairro Militar', 'Bairro Militar e proximidades', 600, '35-55 min'),
('Cuntum', 'Cuntum Madina e arredores', 900, '50-80 min'),
('Mindara', 'Mindara e zona oeste', 750, '40-65 min'),
('Cupelon', 'Cupelon de Cima / de Baixo', 850, '45-70 min'),
('Bôr', 'Bôr e arredores', 1000, '60-90 min');

INSERT INTO produtos (nome, descricao, categoria, preco, preco_promocional, em_promocao, stock, stock_alerta, tamanhos, imagem) VALUES
('Camisa em tecido africano', 'Camisa leve com padrão wax, ideal para o clima da Guiné-Bissau.', 'ROUPA', 8500, 7650, 1, 25, 5, 'S,M,L,XL', NULL),
('Vestido crioulo moderno', 'Vestido fluido com cortes contemporâneos e tecidos locais.', 'ROUPA', 12000, NULL, 0, 15, 5, 'S,M,L', NULL),
('Calças cargo unissexo', 'Calças confortáveis para o dia a dia em Bissau.', 'ROUPA', 9500, NULL, 0, 20, 5, 'M,L,XL', NULL),
('T-shirt Nu Chao', 'T-shirt de algodão com logo da loja.', 'ROUPA', 4500, 4050, 1, 40, 8, 'S,M,L,XL', NULL),
('Saia midi wax', 'Saia midi em tecido africano, vários padrões.', 'ROUPA', 7800, NULL, 0, 18, 5, 'S,M,L', NULL),
('Bolsa de palha artesanal', 'Bolsa feita à mão por artesãos locais.', 'ACESSORIO', 6500, NULL, 0, 12, 4, 'Único', NULL),
('Colar de missangas', 'Colar artesanal com missangas coloridas.', 'ACESSORIO', 2500, 2000, 1, 30, 5, 'Único', NULL),
('Cinto de couro', 'Cinto em couro com fivela metálica.', 'ACESSORIO', 3500, NULL, 0, 22, 5, 'Único', NULL),
('Boné Nu Chao', 'Boné ajustável com bordado da marca.', 'ACESSORIO', 3000, NULL, 0, 35, 5, 'Único', NULL),
('Lenço headwrap', 'Lenço versátil para headwrap ou acessório de moda.', 'ACESSORIO', 2800, NULL, 0, 28, 5, 'Único', NULL);

INSERT INTO configuracoes (chave, valor) VALUES
('mostrar_stock', '1'),
('stock_alerta_global', '5'),
('alerta_promocao_activo', '1'),
('alerta_promocao_texto', 'Promoção activa: peças seleccionadas com desconto — pague na entrega!'),
('loja_aberta', '1'),
('mensagem_loja_fechada', 'Estamos temporariamente encerrados. Volte em breve.'),
('whatsapp_loja', '245955000000'),
('mostrar_precos', '1'),
('site_nome', 'Nu Chao'),
('site_tagline', 'Roupas e acessórios inspirados na Guiné-Bissau. Pagamento na entrega em Bissau e arredores.'),
('site_titulo_home', 'Nu Chao — Moda na Guiné-Bissau'),
('site_localizacao', 'Bissau, Guiné-Bissau'),
('site_horario', 'Seg–Sáb · 09:00–19:00'),
('site_email', ''),
('contacto_subtitulo', 'Fale connosco por WhatsApp, telefone ou formulário.'),
('footer_texto', 'Roupas e acessórios inspirados na Guiné-Bissau. Pagamento na entrega em Bissau e arredores.'),
('categorias_titulo', 'Categorias'),
('categorias_subtitulo', 'Deslize e escolha o que procura.'),
('colecao_titulo', 'Coleção'),
('colecao_subtitulo', 'Roupas e acessórios prontos a enviar para a sua zona.'),
('home_sobre_titulo', 'Sobre a Nu Chao'),
('home_sobre_subtitulo', 'Moda local, entrega em Bissau e pagamento só quando recebe.'),
('home_sobre_painel1_titulo', 'Pagamento na entrega'),
('home_sobre_painel1_texto', '1. Escolha as peças e finalize o pedido no site.\n2. Confirmamos e atribuímos um entregador da Nu Chao.\n3. Recebe em casa e paga em dinheiro, TPA ou mobile money.'),
('home_sobre_painel2_titulo', 'Entregas em Bissau'),
('home_sobre_painel2_texto', 'Cobertura nas principais zonas de Bissau.\nTaxa de entrega conforme a zona. Tempo típico: 30 a 90 minutos após confirmação.'),
('sobre_eyebrow', 'A nossa história'),
('sobre_titulo', 'Sobre a Nu Chao'),
('sobre_lead', 'Moda e acessórios inspirados na Guiné-Bissau — com entrega em casa e pagamento só quando recebe.'),
('sobre_quem_titulo', 'Quem somos'),
('sobre_quem_texto', 'A Nu Chao é uma loja de roupas e acessórios pensada para o dia a dia em Bissau. Unimos tecidos africanos, cortes modernos e peças práticas, com um serviço simples: encomenda online e pagamento na entrega.\n\nQueremos que comprar moda local seja fácil, seguro e próximo — sem complicações de pagamento antecipado.'),
('sobre_missao_titulo', 'A nossa missão'),
('sobre_missao_texto', 'Levar estilo guineense até à sua porta, apoiar entregadores locais e oferecer uma experiência clara do pedido à entrega.'),
('sobre_missao_itens', 'Peças seleccionadas (roupas e acessórios)\nEntrega nas principais zonas de Bissau\nPagamento em dinheiro, TPA ou mobile money\nApoio por WhatsApp'),
('sobre_passos_titulo', 'Como funciona'),
('sobre_passos_subtitulo', 'Do site até à sua casa, em poucos passos.'),
('sobre_passo1_titulo', 'Escolha'),
('sobre_passo1_texto', 'Navegue na coleção, escolha tamanho e adicione ao carrinho.'),
('sobre_passo2_titulo', 'Encomende'),
('sobre_passo2_texto', 'Indique a zona, a morada e como prefere pagar na entrega.'),
('sobre_passo3_titulo', 'Receba'),
('sobre_passo3_texto', 'Um entregador Nu Chao leva o pedido e confirma o pagamento no local.'),
('sobre_zonas_titulo', 'Zonas de entrega'),
('sobre_zonas_subtitulo', 'Cobertura actual em Bissau e arredores. Taxas em XOF.'),
('sobre_cta_titulo', 'Pronto para vestir Nu Chao?'),
('sobre_cta_texto', 'Explore a coleção ou fale connosco — estamos em Bissau.');

INSERT INTO hero_slides (titulo, texto, nota, cta_texto, cta_href, imagem, ordem, activo) VALUES
('Nu Chao', 'Roupas e acessórios com alma da Guiné-Bissau. Encomende online e pague na entrega.', 'Pagamento na entrega · Entregas em Bissau', 'Ver coleção', '#produtos', 'assets/hero-banner.png', 1, 1),
('Moda crioula moderna', 'Tecidos africanos, cortes actuais e peças para o dia a dia em Bissau.', 'Novidades todas as semanas', 'Explorar roupas', '#produtos', 'assets/hero-banner.png', 2, 1),
('Acessórios locais', 'Bolsas, colares e detalhes artesanais feitos com identidade guineense.', 'Entrega rápida nas zonas de Bissau', 'Ver acessórios', '#categorias', 'assets/hero-banner.png', 3, 1);

INSERT INTO promocoes (titulo, mensagem, tipo, desconto_percent, activo, data_inicio, data_fim) VALUES
('Lançamento Nu Chao', 'Descontos em peças seleccionadas — pague na entrega!', 'ALERTA', 10, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));
