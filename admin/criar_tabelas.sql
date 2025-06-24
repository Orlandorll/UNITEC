USE unitec_bd2;

-- Tabela de Mensagens de Contato
CREATE TABLE IF NOT EXISTS mensagens_contato (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('não lida', 'lida', 'respondida') DEFAULT 'não lida',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Conteúdo Sobre
CREATE TABLE IF NOT EXISTS sobre_conteudo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    imagem VARCHAR(255),
    ordem INT DEFAULT 0,
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir alguns dados de exemplo para teste
INSERT INTO mensagens_contato (nome, email, telefone, assunto, mensagem) VALUES
('João Silva', 'joao@email.com', '(11) 99999-9999', 'Dúvida sobre serviços', 'Gostaria de saber mais sobre os serviços oferecidos.'),
('Maria Santos', 'maria@email.com', '(11) 88888-8888', 'Orçamento', 'Preciso de um orçamento para um projeto.');

INSERT INTO sobre_conteudo (titulo, descricao) VALUES
('Nossa Missão', 'Fornecer soluções tecnológicas inovadoras que transformam negócios.'),
('Nossa Visão', 'Ser referência em tecnologia e inovação no mercado.'); 