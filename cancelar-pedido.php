<?php
session_start();
require_once "config/database.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

// Verificar se o ID do pedido foi fornecido
if (!isset($_POST['pedido_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do pedido não fornecido']);
    exit;
}

$pedido_id = $_POST['pedido_id'];

try {
    // Iniciar transação
    $conn->beginTransaction();

    // Verificar se o pedido existe e pertence ao usuário
    $sql = "SELECT * FROM pedidos WHERE id = :pedido_id AND usuario_id = :usuario_id AND status = 'pendente'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt->execute();
    $pedido = $stmt->fetch();

    if (!$pedido) {
        throw new Exception('Pedido não encontrado ou não pode ser cancelado');
    }

    // Buscar itens do pedido para atualizar o estoque
    $sql = "SELECT produto_id, quantidade FROM itens_pedido WHERE pedido_id = :pedido_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->execute();
    $itens = $stmt->fetchAll();

    // Atualizar o estoque dos produtos
    foreach ($itens as $item) {
        $sql = "UPDATE produtos SET estoque = estoque + :quantidade WHERE id = :produto_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantidade', $item['quantidade']);
        $stmt->bindParam(':produto_id', $item['produto_id']);
        $stmt->execute();
    }

    // Atualizar o status do pedido para cancelado
    $sql = "UPDATE pedidos SET status = 'cancelado' WHERE id = :pedido_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->execute();

    // Confirmar transação
    $conn->commit();

    // Retornar sucesso
    echo json_encode(['success' => true, 'message' => 'Pedido cancelado com sucesso']);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    $conn->rollBack();
    
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 