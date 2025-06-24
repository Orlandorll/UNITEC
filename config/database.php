<?php
// Aumentar o limite de tempo de execução
set_time_limit(300); // 5 minutos
ini_set('max_execution_time', 300);

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'unitec_bd2');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            // Otimizações de performance
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        )
    );
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?> 