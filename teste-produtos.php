<?php
require_once "config/database.php";

echo "<h2>Teste de Produtos</h2>";
echo "<pre>";

// 1. Verificar total de produtos
$sql = "SELECT COUNT(*) as total FROM produtos";
$stmt = $conn->query($sql);
$total = $stmt->fetch()['total'];
echo "Total de produtos: " . $total . "\n\n";

// 2. Verificar produtos ativos
$sql = "SELECT COUNT(*) as total FROM produtos WHERE status = 1";
$stmt = $conn->query($sql);
$total_ativos = $stmt->fetch()['total'];
echo "Total de produtos ativos: " . $total_ativos . "\n\n";

// 3. Listar todos os produtos ativos com suas categorias
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
    echo "Categoria: " . $produto['categoria_nome'] . "\n";
    echo "Preço: Kz " . number_format($produto['preco'], 2, ',', '.') . "\n";
    echo "Status: " . ($produto['status'] ? 'Ativo' : 'Inativo') . "\n";
    echo "------------------------\n";
}

// 4. Testar uma busca específica
$termo_busca = "iPhone"; // Exemplo de termo de busca
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.status = 1 
        AND (p.nome LIKE :busca OR p.descricao LIKE :busca)";
$stmt = $conn->prepare($sql);
$stmt->execute([':busca' => "%$termo_busca%"]);
$resultados = $stmt->fetchAll();

echo "\nTeste de busca para o termo '$termo_busca':\n";
echo "------------------------\n";
foreach ($resultados as $produto) {
    echo "ID: " . $produto['id'] . "\n";
    echo "Nome: " . $produto['nome'] . "\n";
    echo "Categoria: " . $produto['categoria_nome'] . "\n";
    echo "------------------------\n";
}

echo "</pre>";
?> 