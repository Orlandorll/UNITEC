<?php
require_once "config/database.php";

echo "<h2>Diagnóstico do Banco de Dados</h2>";
echo "<pre>";

// Verificar tabela produtos
echo "1. Verificando tabela 'produtos':\n";
$sql = "SELECT COUNT(*) as total FROM produtos";
$stmt = $conn->query($sql);
$total_produtos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total de produtos: " . $total_produtos . "\n\n";

if ($total_produtos > 0) {
    echo "Últimos 5 produtos cadastrados:\n";
    $sql = "SELECT id, nome, status FROM produtos ORDER BY id DESC LIMIT 5";
    $stmt = $conn->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($produtos as $produto) {
        echo "- ID: {$produto['id']}, Nome: {$produto['nome']}, Status: " . ($produto['status'] ? 'Ativo' : 'Inativo') . "\n";
    }
    echo "\n";
}

// Verificar tabela imagens_produtos
echo "2. Verificando tabela 'imagens_produtos':\n";
$sql = "SELECT COUNT(*) as total FROM imagens_produtos";
$stmt = $conn->query($sql);
$total_imagens = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total de imagens: " . $total_imagens . "\n\n";

if ($total_imagens > 0) {
    echo "Últimas 5 imagens cadastradas:\n";
    $sql = "SELECT ip.*, p.nome as produto_nome 
            FROM imagens_produtos ip 
            JOIN produtos p ON ip.produto_id = p.id 
            ORDER BY ip.id DESC 
            LIMIT 5";
    $stmt = $conn->query($sql);
    $imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($imagens as $img) {
        echo "- ID: {$img['id']}, Produto: {$img['produto_nome']}, Caminho: {$img['caminho_imagem']}, Principal: " . ($img['imagem_principal'] ? 'Sim' : 'Não') . "\n";
    }
    echo "\n";
}

// Verificar produtos sem imagens
echo "3. Produtos sem imagens:\n";
$sql = "SELECT p.id, p.nome 
        FROM produtos p 
        LEFT JOIN imagens_produtos ip ON p.id = ip.produto_id 
        WHERE ip.id IS NULL 
        ORDER BY p.id DESC 
        LIMIT 5";
$stmt = $conn->query($sql);
$produtos_sem_imagem = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($produtos_sem_imagem)) {
    echo "Todos os produtos têm imagens cadastradas.\n";
} else {
    echo "Produtos sem imagens:\n";
    foreach ($produtos_sem_imagem as $produto) {
        echo "- ID: {$produto['id']}, Nome: {$produto['nome']}\n";
    }
}

// Verificar estrutura das tabelas
echo "\n4. Estrutura das tabelas:\n";
$tabelas = ['produtos', 'imagens_produtos'];
foreach ($tabelas as $tabela) {
    echo "\nEstrutura da tabela '{$tabela}':\n";
    $sql = "DESCRIBE {$tabela}";
    $stmt = $conn->query($sql);
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']}: {$coluna['Type']} " . ($coluna['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
}

echo "</pre>";
?> 