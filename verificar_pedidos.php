<?php
require_once "config/database.php";

// Verificar se a tabela existe
$sql = "SHOW TABLES LIKE 'pedidos'";
$result = $conn->query($sql);
if ($result->rowCount() == 0) {
    echo "A tabela 'pedidos' não existe!<br>";
    echo "Vou tentar criar a tabela...<br>";
    
    // Criar a tabela
    $sql = file_get_contents('sql/criar_tabelas.sql');
    try {
        $conn->exec($sql);
        echo "Tabela criada com sucesso!<br>";
    } catch (PDOException $e) {
        echo "Erro ao criar tabela: " . $e->getMessage() . "<br>";
        exit;
    }
}

// Verificar a estrutura da tabela
$sql = "DESCRIBE pedidos";
$result = $conn->query($sql);
$colunas = $result->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Estrutura da tabela 'pedidos':</h2>";
echo "<pre>";
foreach ($colunas as $coluna) {
    echo "Coluna: " . $coluna['Field'] . "\n";
    echo "Tipo: " . $coluna['Type'] . "\n";
    echo "Nulo: " . $coluna['Null'] . "\n";
    echo "Chave: " . $coluna['Key'] . "\n";
    echo "Default: " . $coluna['Default'] . "\n";
    echo "Extra: " . $coluna['Extra'] . "\n";
    echo "-------------------\n";
}
echo "</pre>";

// Verificar se há dados na tabela
$sql = "SELECT COUNT(*) as total FROM pedidos";
$result = $conn->query($sql);
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "<br>Total de pedidos na tabela: " . $total;

// Verificar o pedido específico (ID 3)
if ($total > 0) {
    $sql = "SELECT p.*, 
            (SELECT SUM(ip.preco * ip.quantidade) 
             FROM itens_pedido ip 
             WHERE ip.pedido_id = p.id) as total_calculado
            FROM pedidos p 
            WHERE p.id = 3";
    $result = $conn->query($sql);
    $pedido = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($pedido) {
        echo "<h2>Detalhes do Pedido #3:</h2>";
        echo "<pre>";
        echo "Valor Total no banco: " . $pedido['valor_total'] . "\n";
        echo "Total Calculado: " . $pedido['total_calculado'] . "\n";
        echo "</pre>";
        
        // Atualizar o valor_total se necessário
        if ($pedido['valor_total'] != $pedido['total_calculado']) {
            $sql = "UPDATE pedidos SET valor_total = :total WHERE id = 3";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':total', $pedido['total_calculado']);
            $stmt->execute();
            echo "Valor total atualizado para: " . $pedido['total_calculado'];
        }
    }
}
?> 