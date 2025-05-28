<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Acesso não autorizado']);
    exit;
}

// Verificar se o ID do usuário e a ação foram fornecidos
if (!isset($_POST['usuario_id']) || !isset($_POST['acao'])) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Parâmetros inválidos']);
    exit;
}

$usuario_id = (int)$_POST['usuario_id'];
$acao = $_POST['acao'];

// Validar ação
if (!in_array($acao, ['ativar', 'desativar'])) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Ação inválida']);
    exit;
}

try {
    // Verificar se o usuário existe
    $sql = "SELECT id FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('Usuário não encontrado');
    }

    // Atualizar status do usuário
    $novo_status = $acao === 'ativar' ? 1 : 0;
    $sql = "UPDATE usuarios SET ativo = :ativo WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ativo', $novo_status);
    $stmt->bindParam(':id', $usuario_id);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => true,
            'mensagem' => $acao === 'ativar' ? 'Usuário ativado com sucesso!' : 'Usuário desativado com sucesso!'
        ]);
    } else {
        throw new Exception('Erro ao atualizar status do usuário');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'erro' => $e->getMessage()
    ]);
} 