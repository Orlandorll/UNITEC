-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS unitec_bd2;
USE unitec_bd2;

-- Tabela de Usuários
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    nif VARCHAR(20),
    tipo_usuario ENUM('pessoa', 'empresa') DEFAULT 'pessoa',
    endereco TEXT,
    cidade VARCHAR(50),
    estado VARCHAR(50),
    cep VARCHAR(20),
    tipo ENUM('admin', 'cliente') DEFAULT 'cliente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1
);

-- Tabela de Categorias
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(50),
    categoria_pai_id INT,
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_pai_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabela de Produtos
CREATE TABLE produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2),
    estoque INT NOT NULL DEFAULT 0,
    codigo VARCHAR(50) UNIQUE,
    destaque BOOLEAN DEFAULT FALSE,
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabela de Imagens dos Produtos
CREATE TABLE imagens_produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    caminho_imagem VARCHAR(255) NOT NULL,
    imagem_principal BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de Atributos dos Produtos
CREATE TABLE atributos_produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    nome_atributo VARCHAR(50) NOT NULL,
    valor_atributo VARCHAR(100) NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de Pedidos
CREATE TABLE pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    metodo_pagamento VARCHAR(50),
    status_pagamento ENUM('pendente', 'pago', 'falhou') DEFAULT 'pendente',
    endereco_entrega TEXT NOT NULL,
    cidade_entrega VARCHAR(50) NOT NULL,
    estado_entrega VARCHAR(50) NOT NULL,
    cep_entrega VARCHAR(20) NOT NULL,
    codigo_rastreio VARCHAR(100),
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de Itens do Pedido
CREATE TABLE itens_pedido (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de Carrinho de Compras
CREATE TABLE carrinho (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de Avaliações de Produtos
CREATE TABLE avaliacoes_produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    avaliacao INT NOT NULL CHECK (avaliacao >= 1 AND avaliacao <= 5),
    comentario TEXT,
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de Cupons de Desconto
CREATE TABLE cupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    tipo_desconto ENUM('porcentagem', 'valor_fixo') NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL,
    valor_minimo DECIMAL(10,2),
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    limite_uso INT,
    uso_atual INT DEFAULT 0,
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Lista de Desejos
CREATE TABLE lista_desejos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de Configurações
CREATE TABLE configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_loja VARCHAR(100) NOT NULL,
    email_contato VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(50),
    estado VARCHAR(50),
    cep VARCHAR(20),
    descricao_loja TEXT,
    meta_keywords TEXT,
    meta_description TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir algumas categorias iniciais
INSERT INTO categorias (nome, slug, descricao, icone) VALUES
('Smartphones', 'smartphones', 'Smartphones e celulares', 'fas fa-mobile-alt'),
('Computadores', 'computadores', 'Notebooks e desktops', 'fas fa-laptop'),
('Tablets', 'tablets', 'Tablets e iPads', 'fas fa-tablet-alt'),
('Gaming', 'gaming', 'Produtos para games', 'fas fa-gamepad'),
('Acessórios', 'acessorios', 'Acessórios para eletrônicos', 'fas fa-headphones');

-- Inserir um usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@unitec.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir algumas categorias de exemplo
INSERT INTO categorias (nome, descricao) VALUES
('Smartphones', 'Smartphones e celulares'),
('Computadores', 'Notebooks e desktops'),
('Tablets', 'Tablets e iPads'),
('Acessórios', 'Acessórios para dispositivos móveis e computadores'),
('Áudio', 'Fones de ouvido e caixas de som'),
('Periféricos', 'Mouses, teclados e outros periféricos');

-- Inserir alguns produtos de exemplo
INSERT INTO produtos (categoria_id, nome, descricao, preco, preco_promocional, estoque) VALUES
(1, 'iPhone 13 Pro', 'iPhone 13 Pro 256GB', 8999.90, 8499.90, 10),
(1, 'Samsung Galaxy S21', 'Samsung Galaxy S21 128GB', 4999.90, 4499.90, 15),
(2, 'MacBook Pro M1', 'MacBook Pro com chip M1 13"', 12999.90, NULL, 8),
(2, 'Dell XPS 13', 'Dell XPS 13 Intel Core i7', 8999.90, 7999.90, 12),
(3, 'iPad Pro', 'iPad Pro 12.9" 256GB', 9999.90, 9499.90, 10),
(4, 'AirPods Pro', 'AirPods Pro com Estuche de Carregamento', 1999.90, 1799.90, 20);

-- Inserir imagens para os produtos
INSERT INTO imagens_produtos (produto_id, caminho_imagem, imagem_principal) VALUES
(1, 'assets/img/produtos/iphone13pro.jpg', 1),
(2, 'assets/img/produtos/galaxys21.jpg', 1),
(3, 'assets/img/produtos/macbookpro.jpg', 1),
(4, 'assets/img/produtos/dellxps13.jpg', 1),
(5, 'assets/img/produtos/ipadpro.jpg', 1),
(6, 'assets/img/produtos/airpodspro.jpg', 1);

-- Inserir atributos para os produtos
INSERT INTO atributos_produtos (produto_id, nome, valor) VALUES
(1, 'Cor', 'Grafite'),
(1, 'Armazenamento', '256GB'),
(2, 'Cor', 'Phantom Gray'),
(2, 'Armazenamento', '128GB'),
(3, 'Processador', 'Apple M1'),
(3, 'Memória', '16GB'),
(4, 'Processador', 'Intel Core i7'),
(4, 'Memória', '16GB'),
(5, 'Cor', 'Prata'),
(5, 'Armazenamento', '256GB'),
(6, 'Cor', 'Branco');

-- Inserir configurações padrão
INSERT INTO configuracoes (nome_loja, email_contato) VALUES ('UNITEC', 'contato@unitec.com'); 