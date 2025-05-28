USE unitec_bd2;

-- Criar tabela de configurações se não existir
CREATE TABLE IF NOT EXISTS configuracoes (
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

-- Inserir configurações padrão se não existir
INSERT INTO configuracoes (nome_loja, email_contato) 
SELECT 'UNITEC', 'contato@unitec.com'
WHERE NOT EXISTS (SELECT 1 FROM configuracoes WHERE id = 1); 