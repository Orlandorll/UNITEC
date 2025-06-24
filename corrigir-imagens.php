<?php
require_once "config/database.php";
require_once "includes/functions.php";

echo "<h2>Correção de Imagens</h2>";
echo "<pre>";

try {
    // 1. Verificar se o diretório de uploads existe
    $diretorio_uploads = __DIR__ . '/uploads/produtos';
    if (!is_dir($diretorio_uploads)) {
        echo "Criando diretório de uploads...\n";
        if (!mkdir($diretorio_uploads, 0777, true)) {
            throw new Exception("Erro ao criar diretório de uploads");
        }
        echo "✓ Diretório criado com sucesso\n";
    } else {
        echo "✓ Diretório de uploads já existe\n";
    }

    // 2. Verificar se há imagens na coluna 'imagem' da tabela produtos
    $sql = "SELECT id, nome, imagem FROM produtos WHERE imagem IS NOT NULL AND imagem != ''";
    $stmt = $conn->query($sql);
    $produtos_com_imagem = $stmt->fetchAll();

    if (!empty($produtos_com_imagem)) {
        echo "\nEncontrados " . count($produtos_com_imagem) . " produtos com imagem na coluna 'imagem'\n";
        
        $conn->beginTransaction();
        
        foreach ($produtos_com_imagem as $produto) {
            echo "\nProcessando produto ID {$produto['id']} - {$produto['nome']}\n";
            
            // Verificar se já existe na tabela imagens_produtos
            $sql = "SELECT COUNT(*) FROM imagens_produtos WHERE produto_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$produto['id']]);
            $tem_imagem = $stmt->fetchColumn() > 0;
            
            if (!$tem_imagem) {
                // Mover a imagem para a tabela imagens_produtos
                $sql = "INSERT INTO imagens_produtos (produto_id, caminho_imagem, imagem_principal) VALUES (?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$produto['id'], $produto['imagem']]);
                echo "✓ Imagem movida para a tabela imagens_produtos\n";
            } else {
                echo "! Produto já tem imagem na tabela imagens_produtos\n";
            }
        }
        
        $conn->commit();
        echo "\n✓ Todas as imagens foram processadas com sucesso\n";
    } else {
        echo "\nNenhum produto encontrado com imagem na coluna 'imagem'\n";
    }

    // 3. Verificar produtos sem imagens
    $sql = "SELECT p.id, p.nome 
            FROM produtos p 
            LEFT JOIN imagens_produtos ip ON p.id = ip.produto_id 
            WHERE ip.id IS NULL 
            ORDER BY p.id DESC";
    $stmt = $conn->query($sql);
    $produtos_sem_imagem = $stmt->fetchAll();

    if (!empty($produtos_sem_imagem)) {
        echo "\nProdutos sem imagens:\n";
        foreach ($produtos_sem_imagem as $produto) {
            echo "- ID: {$produto['id']}, Nome: {$produto['nome']}\n";
        }
        echo "\nPara adicionar imagens a estes produtos, use o formulário de edição de produto.\n";
    } else {
        echo "\n✓ Todos os produtos têm imagens cadastradas\n";
    }

    // 4. Verificar imagens físicas
    echo "\nVerificando imagens físicas:\n";
    $sql = "SELECT ip.*, p.nome as produto_nome 
            FROM imagens_produtos ip 
            JOIN produtos p ON ip.produto_id = p.id";
    $stmt = $conn->query($sql);
    $imagens = $stmt->fetchAll();

    foreach ($imagens as $img) {
        $caminho_fisico = __DIR__ . '/' . $img['caminho_imagem'];
        echo "\nProduto: {$img['produto_nome']}\n";
        echo "Caminho: {$img['caminho_imagem']}\n";
        echo "Existe fisicamente: " . (file_exists($caminho_fisico) ? "✓ Sim" : "✗ Não") . "\n";
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "\n✗ Erro: " . $e->getMessage() . "\n";
}

echo "</pre>";
?> 