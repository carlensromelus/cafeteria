CREATE DATABASE IF NOT EXISTS lumora_cafe_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lumora_cafe_v2;

CREATE TABLE IF NOT EXISTS usuarios (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL UNIQUE,
 senha VARCHAR(255) NOT NULL,
 role ENUM('user','admin') NOT NULL DEFAULT 'user',
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS produtos (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(150) NOT NULL,
 descricao TEXT DEFAULT NULL,
 preco DECIMAL(10,2) NOT NULL,
 imagem VARCHAR(255) DEFAULT NULL,
 icone VARCHAR(10) DEFAULT NULL,
 estoque INT NOT NULL DEFAULT 0,
 categoria VARCHAR(60) NOT NULL DEFAULT 'Cafés',
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mensagens (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) DEFAULT NULL,
 email VARCHAR(100) DEFAULT NULL,
 assunto VARCHAR(150) DEFAULT NULL,
 mensagem TEXT DEFAULT NULL,
 data_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT NOT NULL,
 total DECIMAL(10,2) NOT NULL,
 tipo_entrega ENUM('retirada','delivery') NOT NULL,
 endereco VARCHAR(255) DEFAULT NULL,
 observacoes VARCHAR(500) DEFAULT NULL,
 forma_pagamento ENUM('na_retirada','na_entrega') NOT NULL,
 status ENUM('recebido','em_preparo','pronto','entregue','cancelado') NOT NULL DEFAULT 'recebido',
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_pedidos_usuario (usuario_id),
 CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS itens_pedido (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 pedido_id INT NOT NULL,
 produto_id INT DEFAULT NULL,
 nome_produto VARCHAR(150) NOT NULL,
 quantidade INT NOT NULL,
 preco_unitario DECIMAL(10,2) NOT NULL,
 INDEX idx_itens_pedido (pedido_id),
 INDEX idx_itens_produto (produto_id),
 CONSTRAINT fk_itens_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
 CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO produtos (nome,descricao,preco,imagem,icone,estoque,categoria)
SELECT 'Espresso Tradicional','Café intenso e aromático.',7.00,'img/1.jpg','☕',20,'Cafés'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome='Espresso Tradicional');
INSERT INTO produtos (nome,descricao,preco,imagem,icone,estoque,categoria)
SELECT 'Cappuccino Cremoso','Espresso, leite e espuma.',12.00,'img/2.jpg','☕',20,'Cafés'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome='Cappuccino Cremoso');
INSERT INTO produtos (nome,descricao,preco,imagem,icone,estoque,categoria)
SELECT 'Croissant Artesanal','Massa leve e amanteigada.',11.00,'img/4.jpg','🥐',20,'Salgados'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome='Croissant Artesanal');
INSERT INTO produtos (nome,descricao,preco,imagem,icone,estoque,categoria)
SELECT 'Brownie de Chocolate','Brownie com cobertura crocante.',11.00,'img/6.jpg','🍫',20,'Doces'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome='Brownie de Chocolate');
