-- Adicionar coluna de destaque na tabela produtos
ALTER TABLE produtos ADD COLUMN destaque BOOLEAN DEFAULT FALSE;

-- Atualizar alguns produtos para destaque
UPDATE produtos SET destaque = TRUE WHERE id IN (1, 2, 3, 4);

-- Criar índice para melhorar a performance da busca por destaque
CREATE INDEX idx_produtos_destaque ON produtos(destaque); 