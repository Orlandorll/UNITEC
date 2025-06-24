<?php
require_once "../config/database.php";

try {
    // Criar tabela sobre
    $sql = "CREATE TABLE IF NOT EXISTS sobre (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        conteudo TEXT NOT NULL,
        missao TEXT NOT NULL,
        visao TEXT NOT NULL,
        valores TEXT NOT NULL,
        data_criacao DATETIME NOT NULL,
        data_atualizacao DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $conn->exec($sql);
    echo "Tabela 'sobre' criada com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage();
}
?> 