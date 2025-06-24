<?php
require_once "config/database.php";

// Verificar se a tabela existe
$sql = "SHOW TABLES LIKE 'pedidos'";
$result = $conn->query($sql);
if ($result->rowCount() == 0) {
    echo "A tabela 'pedidos' não existe!<br>";
    echo "Vou tentar criar a tabela...<br>";
    
    // Criar a tabela
    $sql = file_get_contents('sql/criar_tabela_pedidos.sql');
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

// Verificar se a coluna 'endereco' existe
$coluna_existe = false;
foreach ($colunas as $coluna) {
    if ($coluna['Field'] === 'endereco') {
        $coluna_existe = true;
        break;
    }
}

if (!$coluna_existe) {
    echo "<br>A coluna 'endereco' não existe! Vou tentar adicionar...<br>";
    
    try {
        $sql = "ALTER TABLE pedidos ADD COLUMN endereco VARCHAR(255) NOT NULL AFTER usuario_id";
        $conn->exec($sql);
        echo "Coluna 'endereco' adicionada com sucesso!<br>";
    } catch (PDOException $e) {
        echo "Erro ao adicionar coluna: " . $e->getMessage() . "<br>";
    }
}

// Verificar se há dados na tabela
$sql = "SELECT COUNT(*) as total FROM pedidos";
$result = $conn->query($sql);
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "<br>Total de pedidos na tabela: " . $total;
?> 