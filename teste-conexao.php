<?php
try {
    // Tenta incluir o arquivo de configuração do banco
    require_once "config/database.php";
    
    echo "<h2>Teste de Conexão com o Banco de Dados</h2>";
    echo "<pre>";
    
    // Verifica se a conexão foi estabelecida
    if (isset($conn) && $conn instanceof PDO) {
        echo "✓ Conexão estabelecida com sucesso!\n\n";
        
        // Tenta executar uma consulta simples
        $stmt = $conn->query("SELECT DATABASE() as db");
        $db = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Banco de dados atual: " . $db['db'] . "\n\n";
        
        // Lista as tabelas disponíveis
        echo "Tabelas disponíveis:\n";
        $stmt = $conn->query("SHOW TABLES");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tabelas as $tabela) {
            echo "- " . $tabela . "\n";
        }
        
        // Verifica as configurações da conexão
        echo "\nConfigurações da conexão:\n";
        echo "Host: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
        echo "Versão do MySQL: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        echo "Versão do PHP: " . phpversion() . "\n";
        
    } else {
        echo "✗ Erro: Conexão não estabelecida\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Erro na conexão:\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    
    // Mostra informações adicionais de debug
    echo "\nInformações de Debug:\n";
    echo "Arquivo de configuração: " . realpath("config/database.php") . "\n";
    echo "Diretório atual: " . __DIR__ . "\n";
    
    // Tenta mostrar as configurações do banco (sem a senha)
    if (file_exists("config/database.php")) {
        echo "\nConteúdo do arquivo de configuração (sem senha):\n";
        $config = file_get_contents("config/database.php");
        // Remove a senha por segurança
        $config = preg_replace('/\'senha\'\s*=>\s*\'.*?\'/', "'senha' => '*****'", $config);
        echo htmlspecialchars($config);
    }
}
echo "</pre>";
?> 