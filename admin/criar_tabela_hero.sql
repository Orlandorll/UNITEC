USE unitec_bd2;

-- Tabela de Imagens da Hero Section
CREATE TABLE IF NOT EXISTS hero_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    subtitulo VARCHAR(200),
    descricao TEXT,
    imagem VARCHAR(255) NOT NULL,
    link_botao VARCHAR(255),
    texto_botao VARCHAR(50),
    ordem INT DEFAULT 0,
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir alguns dados de exemplo para a hero section
INSERT INTO hero_images (titulo, subtitulo, descricao, imagem, link_botao, texto_botao, ordem) VALUES
('Bem-vindo à UNITEC', 'Soluções Tecnológicas Inovadoras', 'Transformando ideias em realidade digital com tecnologia de ponta.', 'hero1.jpg', '#servicos', 'Nossos Serviços', 1),
('Inovação e Tecnologia', 'Soluções Personalizadas', 'Desenvolvemos soluções sob medida para o seu negócio.', 'hero2.jpg', '#sobre', 'Saiba Mais', 2); 