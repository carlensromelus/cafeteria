USE lumora_cafe;
CREATE TABLE IF NOT EXISTS pedidos (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT NOT NULL,
 nome_cliente VARCHAR(100) NOT NULL,
 telefone VARCHAR(25) NOT NULL,
 cep VARCHAR(9) NOT NULL,
 endereco VARCHAR(200) NOT NULL,
 numero VARCHAR(20) NOT NULL,
 complemento VARCHAR(100) NOT NULL DEFAULT '',
 bairro VARCHAR(100) NOT NULL,
 cidade VARCHAR(100) NOT NULL,
 estado CHAR(2) NOT NULL,
 itens TEXT NOT NULL,
 total DECIMAL(10,2) NOT NULL,
 status ENUM('recebido','preparando','saiu_entrega','entregue','cancelado') NOT NULL DEFAULT 'recebido',
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_usuario (usuario_id),
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
