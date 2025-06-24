<?php
require_once "config/database.php";

echo "<h2>Teste Detalhado de Produtos e Busca</h2>";
echo "<pre>";

// 1. Verificar conexão com o banco
try {
    $conn->query("SELECT 1");
    echo "✓ Conexão com o banco de dados OK\n\n";
} catch (PDOException $e) {
    echo "✗ Erro na conexão: " . $e->getMessage() . "\n\n";
    exit;
}

// 2. Verificar tabela produtos
try {
    $sql = "SHOW TABLES LIKE 'produtos'";
    $stmt = $conn->query($sql);
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabela 'produtos' existe\n\n";
    } else {
        echo "✗ Tabela 'produtos' não existe!\n\n";
        exit;
    }
} catch (PDOException $e) {
    echo "✗ Erro ao verificar tabela: " . $e->getMessage() . "\n\n";
    exit;
}

// 3. Verificar estrutura da tabela produtos
try {
    $sql = "DESCRIBE produtos";
    $stmt = $conn->query($sql);
    echo "Estrutura da tabela produtos:\n";
    echo "------------------------\n";
    while ($coluna = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $coluna['Field'] . " - " . $coluna['Type'] . "\n";
    }
    echo "\n";
} catch (PDOException $e) {
    echo "✗ Erro ao verificar estrutura: " . $e->getMessage() . "\n\n";
}

// 4. Verificar total de produtos
try {
    $sql = "SELECT COUNT(*) as total FROM produtos";
    $stmt = $conn->query($sql);
    $total = $stmt->fetch()['total'];
    echo "Total de produtos: " . $total . "\n\n";
} catch (PDOException $e) {
    echo "✗ Erro ao contar produtos: " . $e->getMessage() . "\n\n";
}

// 5. Verificar produtos ativos
try {
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE status = 1";
    $stmt = $conn->query($sql);
    $total_ativos = $stmt->fetch()['total'];
    echo "Total de produtos ativos: " . $total_ativos . "\n\n";
} catch (PDOException $e) {
    echo "✗ Erro ao contar produtos ativos: " . $e->getMessage() . "\n\n";
}

// 6. Listar todos os produtos ativos
try {
    $sql = "SELECT p.*, c.nome as categoria_nome 
            FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.status = 1 
            ORDER BY p.id DESC";
    $stmt = $conn->query($sql);
    $produtos = $stmt->fetchAll();

    echo "Lista de produtos ativos:\n";
    echo "------------------------\n";
    foreach ($produtos as $produto) {
        echo "ID: " . $produto['id'] . "\n";
        echo "Nome: " . $produto['nome'] . "\n";
        echo "Descrição: " . substr($produto['descricao'], 0, 50) . "...\n";
        echo "Categoria: " . $produto['categoria_nome'] . "\n";
        echo "Preço: Kz " . number_format($produto['preco'], 2, ',', '.') . "\n";
        echo "Status: " . ($produto['status'] ? 'Ativo' : 'Inativo') . "\n";
        echo "------------------------\n";
    }
} catch (PDOException $e) {
    echo "✗ Erro ao listar produtos: " . $e->getMessage() . "\n\n";
}

// 7. Testar busca com diferentes termos
$termos_teste = ['iPhone', 'Samsung', 'MacBook', 'Pro', 'Gaming'];

echo "\nTeste de busca com diferentes termos:\n";
echo "------------------------\n";

foreach ($termos_teste as $termo) {
    try {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.status = 1 
                AND (LOWER(p.nome) LIKE LOWER(:busca) OR LOWER(p.descricao) LIKE LOWER(:busca))";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':busca' => "%$termo%"]);
        $resultados = $stmt->fetchAll();

        echo "\nBusca por '$termo':\n";
        echo "Encontrados: " . count($resultados) . " produtos\n";
        foreach ($resultados as $produto) {
            echo "- " . $produto['nome'] . "\n";
        }
    } catch (PDOException $e) {
        echo "✗ Erro na busca por '$termo': " . $e->getMessage() . "\n";
    }
}

echo "</pre>";
?> 