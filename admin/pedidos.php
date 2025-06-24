<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || !is_admin($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Processar atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = $_POST['novo_status'];
    
    $sql = "UPDATE pedidos SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $novo_status);
    $stmt->bindParam(':id', $pedido_id);
    
    if ($stmt->execute()) {
        $mensagem = "Status do pedido atualizado com sucesso!";
        $mensagem_tipo = "success";
    } else {
        $mensagem = "Erro ao atualizar status do pedido.";
        $mensagem_tipo = "danger";
    }
}

// Processar apagar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'apagar') {
    $pedido_id = (int)$_POST['pedido_id'];
    try {
        // Primeiro apaga os itens do pedido
        $sql = "DELETE FROM itens_pedido WHERE pedido_id = :pedido_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pedido_id', $pedido_id);
        $stmt->execute();

        // Depois apaga o pedido
        $sql = "DELETE FROM pedidos WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $pedido_id);
        $stmt->execute();

        $mensagem = "Pedido apagado com sucesso!";
        $mensagem_tipo = "success";
    } catch (PDOException $e) {
        $mensagem = "Erro ao apagar o pedido: " . $e->getMessage();
        $mensagem_tipo = "danger";
    }
}

// Buscar todos os pedidos
$sql = "SELECT p.*, u.nome as nome_usuario, u.email 
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.data_criacao DESC";
$stmt = $conn->query($sql);
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .pedido-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            padding: 20px;
        }
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .pedido-info {
            margin-bottom: 15px;
        }
        .pedido-items {
            margin-top: 15px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-aprovado { background: #d4edda; color: #155724; }
        .status-em_andamento { background: #cce5ff; color: #004085; }
        .status-entregue { background: #d1e7dd; color: #0f5132; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Gerenciar Pedidos</h1>

        <?php if (isset($mensagem)): ?>
            <div class="alert alert-<?php echo $mensagem_tipo; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (count($pedidos) === 0): ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> Não há pedidos cadastrados no sistema.
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div>
                            <h3 class="h5 mb-0">Pedido #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></h3>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?>
                            </small>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $pedido['status']; ?>">
                                <?php echo ucfirst($pedido['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="pedido-info">
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nome_usuario']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?></p>
                        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($pedido['endereco_entrega']); ?></p>
                        <p><strong>Cidade:</strong> <?php echo htmlspecialchars($pedido['cidade_entrega']); ?></p>
                        <p><strong>Estado:</strong> <?php echo htmlspecialchars($pedido['estado_entrega']); ?></p>
                        <p><strong>NIF:</strong> <?php echo htmlspecialchars($pedido['cep_entrega']); ?></p>
                        <p><strong>Forma de Pagamento:</strong> <?php echo htmlspecialchars($pedido['metodo_pagamento']); ?></p>
                        <p><strong>Total:</strong> Kz <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></p>
                        <p><strong>Status do Pedido:</strong> <?php echo ucfirst($pedido['status']); ?></p>
                        <p><strong>Status do Pagamento:</strong> <?php echo ucfirst($pedido['status_pagamento']); ?></p>
                        <?php if ($pedido['codigo_rastreio']): ?>
                            <p><strong>Código de Rastreio:</strong> <?php echo htmlspecialchars($pedido['codigo_rastreio']); ?></p>
                        <?php endif; ?>
                        <?php if ($pedido['observacoes']): ?>
                            <p><strong>Observações:</strong> <?php echo nl2br(htmlspecialchars($pedido['observacoes'])); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="pedido-items">
                        <h4 class="h6 mb-3">Itens do Pedido</h4>
                        <?php
                        $sql = "SELECT ip.*, p.nome as produto_nome 
                                FROM itens_pedido ip 
                                JOIN produtos p ON ip.produto_id = p.id 
                                WHERE ip.pedido_id = :pedido_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':pedido_id', $pedido['id']);
                        $stmt->execute();
                        $itens = $stmt->fetchAll();
                        
                        foreach ($itens as $item):
                        ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <?php echo htmlspecialchars($item['produto_nome']); ?>
                                    <small class="text-muted">x<?php echo $item['quantidade']; ?></small>
                                </div>
                                <div>
                                    Kz <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3">
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                            <select name="novo_status" class="form-select">
                                <option value="pendente" <?php echo $pedido['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="processando" <?php echo $pedido['status'] == 'processando' ? 'selected' : ''; ?>>Processando</option>
                                <option value="enviado" <?php echo $pedido['status'] == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                <option value="entregue" <?php echo $pedido['status'] == 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                <option value="cancelado" <?php echo $pedido['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <button type="submit" name="atualizar_status" class="btn btn-primary">
                                Atualizar Status
                            </button>
                        </form>
                    </div>

                    <div class="mt-3">
                        <div class="btn-group">
                            <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja apagar este pedido?');">
                                <input type="hidden" name="acao" value="apagar">
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 