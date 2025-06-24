<?php
require_once "config/database.php";

// Verificar se a tabela existe
$sql = "SHOW TABLES LIKE 'itens_pedido'";
$result = $conn->query($sql);
if ($result->rowCount() == 0) {
    echo "A tabela 'itens_pedido' não existe!<br>";
    echo "Vou tentar criar a tabela...<br>";
    
    // Criar a tabela
    $sql = file_get_contents('sql/criar_tabela_itens_pedido.sql');
    try {
        $conn->exec($sql);
        echo "Tabela criada com sucesso!<br>";
    } catch (PDOException $e) {
        echo "Erro ao criar tabela: " . $e->getMessage() . "<br>";
        exit;
    }
}

// Verificar a estrutura da tabela
$sql = "DESCRIBE itens_pedido";
$result = $conn->query($sql);
$colunas = $result->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Estrutura da tabela 'itens_pedido':</h2>";
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
$sql = "SELECT COUNT(*) as total FROM itens_pedido";
$result = $conn->query($sql);
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "<br>Total de itens de pedidos na tabela: " . $total;
?> 