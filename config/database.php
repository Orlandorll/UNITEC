<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'unitec';
$username = 'root';
$password = '';

try {
    // Criar conexão com o banco de dados
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar o PDO para lançar exceções em caso de erros
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar o PDO para retornar objetos anônimos
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Em caso de erro, exibir mensagem e encerrar script
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?> 