USE unitec_bd2;

-- Adicionar coluna status se ela não existir
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1;

-- Atualizar todos os usuários admin para status 1
UPDATE usuarios SET status = 1 WHERE tipo = 'admin'; 