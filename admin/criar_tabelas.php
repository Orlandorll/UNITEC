<?php
require_once "../config/database.php";

try {
    // Criar tabela de mensagens
    $sql = "CREATE TABLE IF NOT EXISTS mensagens_contato (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefone VARCHAR(20),
        assunto VARCHAR(200) NOT NULL,
        mensagem TEXT NOT NULL,
        status ENUM('não lida', 'lida', 'respondida') DEFAULT 'não lida',
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_resposta TIMESTAMP NULL,
        resposta TEXT NULL,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Tabela 'mensagens_contato' criada com sucesso!";
} catch (PDOException $e) {
 