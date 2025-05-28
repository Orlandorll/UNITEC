USE unitec_bd2;

-- Primeiro, fazer backup dos dados existentes
CREATE TABLE IF NOT EXISTS configuracoes_backup AS SELECT * FROM configuracoes;

-- Remover a tabela antiga
DROP TABLE IF EXISTS configuracoes;

-- Criar a nova tabela com a estrutura atualizada
CREATE TABLE configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_loja VARCHAR(100) NOT NULL,
    email_contato VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(50),
    provincia VARCHAR(50),
    nif VARCHAR(20),
    descricao_loja TEXT,
    meta_keywords TEXT,
    meta_description TEXT,
    whatsapp VARCHAR(20),
    facebook VARCHAR(100),
    instagram VARCHAR(100),
    twitter VARCHAR(100),
    horario_funcionamento TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir dados padrão
INSERT INTO configuracoes (nome_loja, email_contato) 
VALUES ('UNITEC', 'contato@unitec.com'); 