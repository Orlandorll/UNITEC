-- Não precisamos remover a coluna já que ela não existe
-- Vamos direto adicionar a nova coluna status como ENUM
ALTER TABLE usuarios
ADD COLUMN status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo';

-- Garantir que todos os usuários tenham o status 'ativo'
UPDATE usuarios SET status = 'ativo';

-- Atualizar todos os usuários admin para status ativo
UPDATE usuarios SET status = 'ativo' WHERE tipo = 'admin';