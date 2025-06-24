<?php
require_once "config/database.php";
require_once "includes/functions.php";

echo "<h2>Limpeza de Imagens</h2>";
echo "<pre>";

try {
    // 1. Listar todas as imagens no diretório
    $diretorio = __DIR__ . '/uploads/produtos';
    $arquivos = scandir($diretorio);
    $imagens_diretorio = array_filter($arquivos, function($arquivo) {
        return $arquivo != "." && $arquivo != ".." && in_array(strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    });

    echo "Imagens encontradas no diretório: " . count($imagens_diretorio) . "\n";
    foreach ($imagens_diretorio as $img) {
        echo "- {$img}\n";
    }

    // 2. Listar todas as imagens no banco
    $sql = "SELECT ip.*, p.nome as produto_nome 
            FROM imagens_produtos ip 
            JOIN produtos p ON ip.produto_id = p.id";
    $stmt = $conn->query($sql);
    $imagens_banco = $stmt->fetchAll();

    echo "\nImagens no banco de dados: " . count($imagens_banco) . "\n";
    foreach ($imagens_banco as $img) {
        echo "- {$img['caminho_imagem']} (Produto: {$img['produto_nome']})\n";
    }

    // 3. Encontrar imagens não utilizadas
    $imagens_banco_nomes = array_column($imagens_banco, 'caminho_imagem');
    $imagens_nao_utilizadas = array_diff($imagens_diretorio, $imagens_banco_nomes);

    echo "\nImagens não utilizadas (não estão no banco):\n";
    foreach ($imagens_nao_utilizadas as $img) {
        echo "- {$img}\n";
    }

    // 4. Verificar se a ação foi confirmada via GET
    if (isset($_GET['acao']) && $_GET['acao'] === 'limpar') {
        $conn->beginTransaction();
        
        // Remover imagens não utilizadas
        foreach ($imagens_nao_utilizadas as $img) {
            $caminho_completo = $diretorio . '/' . $img;
            if (unlink($caminho_completo)) {
                echo "✓ Removida: {$img}\n";
            } else {
                echo "✗ Erro ao remover: {$img}\n";
            }
        }

        // Verificar produtos sem imagem
        $sql = "SELECT p.id, p.nome 
                FROM produtos p 
                LEFT JOIN imagens_produtos ip ON p.id = ip.produto_id 
                WHERE ip.id IS NULL";
        $stmt = $conn->query($sql);
        $produtos_sem_imagem = $stmt->fetchAll();

        if (!empty($produtos_sem_imagem)) {
            echo "\nProdutos sem imagem:\n";
            foreach ($produtos_sem_imagem as $produto) {
                echo "- ID: {$produto['id']}, Nome: {$produto['nome']}\n";
            }
        }

        $conn->commit();
        echo "\n✓ Limpeza concluída!\n";
    } else {
        echo "\nPara remover as imagens não utilizadas, clique no link abaixo:\n";
        echo "<a href='?acao=limpar'>Remover imagens não utilizadas</a>\n";
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "\n✗ Erro: " . $e->getMessage() . "\n";
}

echo "</pre>";
?> 