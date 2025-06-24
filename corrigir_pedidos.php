<?php
require_once "config/database.php";

try {
    // 1. Corrigir a estrutura da tabela
    $sql = "ALTER TABLE pedidos 
            DROP COLUMN IF EXISTS endereco,
            DROP COLUMN IF EXISTS cidade,
            DROP COLUMN IF EXISTS estado,
            DROP COLUMN IF EXISTS nif,
            MODIFY COLUMN valor_total DECIMAL(10,2) NOT NULL,
            MODIFY COLUMN status ENUM('pendente', 'aprovado', 'enviado', 'entregue', 'cancelado') NOT NULL DEFAULT 'pendente',
            MODIFY COLUMN status_pagamento ENUM('pendente', 'aprovado', 'cancelado') NOT NULL DEFAULT 'pendente'";
    
    $conn->exec($sql);
    echo "<div style='color: green;'>✓ Estrutura da tabela corrigida com sucesso!</div><br>";

    // 2. Corrigir os valores armazenados
    $sql = "UPDATE pedidos SET valor_total = REPLACE(REPLACE(valor_total, '.', ''), ',', '.')";
    $conn->exec($sql);
    echo "<div style='color: green;'>✓ Valores numéricos corrigidos!</div><br>";

    // 3. Verificar o pedido #3
    $sql = "SELECT p.*, 
            (SELECT SUM(ip.preco * ip.quantidade) 
             FROM itens_pedido ip 
             WHERE ip.pedido_id = p.id) as total_calculado
            FROM pedidos p 
            WHERE p.id = 3";
    $result = $conn->query($sql);
    $pedido = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($pedido) {
        echo "<h2>Detalhes do Pedido #3 após correção:</h2>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>Valor Total no banco:</strong> Kz " . number_format($pedido['valor_total'], 2, ',', '.') . "</p>";
        echo "<p><strong>Total Calculado:</strong> Kz " . number_format($pedido['total_calculado'], 2, ',', '.') . "</p>";
        echo "</div>";
        
        // Atualizar o valor_total se ainda houver diferença
        if (abs($pedido['valor_total'] - $pedido['total_calculado']) > 0.01) {
            $sql = "UPDATE pedidos SET valor_total = :total WHERE id = 3";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':total', $pedido['total_calculado']);
            $stmt->execute();
            echo "<div style='color: green; margin-top: 10px;'>✓ Valor total atualizado para: Kz " . number_format($pedido['total_calculado'], 2, ',', '.') . "</div>";
        } else {
            echo "<div style='color: green; margin-top: 10px;'>✓ O valor total já está correto!</div>";
        }
    } else {
        echo "<div style='color: red;'>Pedido #3 não encontrado!</div>";
    }

    echo "<br><br><div style='color: green; font-weight: bold;'>✓ Todas as operações foram concluídas com sucesso!</div>";
    echo "<br><a href='pedido-confirmado.php?id=3' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Verificar Pedido #3</a>";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 15px; background: #fff3f3; border: 1px solid #ffcdd2; border-radius: 5px;'>";
    echo "<strong>Erro:</strong> " . $e->getMessage();
    echo "</div>";
}
?> 