<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    $redirect = "pedido-detalhes.php";
    if (isset($_GET['id'])) {
        $redirect .= "?id=" . $_GET['id'];
    }
    header("Location: login.php?redirect=" . urlencode($redirect));
    exit;
}

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id'])) {
    header("Location: meus-pedidos.php");
    exit;
}

$pedido_id = $_GET['id'];

// Buscar informações do pedido
$sql = "SELECT p.*, u.nome as nome_usuario, u.email,
        (SELECT SUM(ip.preco * ip.quantidade) 
         FROM itens_pedido ip 
         WHERE ip.pedido_id = p.id) as total_calculado
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = :pedido_id AND p.usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$pedido = $stmt->fetch();

if (!$pedido) {
    header("Location: meus-pedidos.php");
    exit;
}

// Buscar itens do pedido
$sql = "SELECT ip.*, p.nome as produto_nome,
        (SELECT caminho_imagem FROM imagens_produtos 
         WHERE produto_id = p.id AND imagem_principal = 1 
         LIMIT 1) as imagem,
        (ip.preco * ip.quantidade) as subtotal_item
        FROM itens_pedido ip 
        JOIN produtos p ON ip.produto_id = p.id 
        WHERE ip.pedido_id = :pedido_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->execute();
$itens = $stmt->fetchAll();

// Calcular valores
$subtotal = 0;
foreach ($itens as $item) {
    $subtotal += ($item['preco'] * $item['quantidade']);
}
$frete = 0; // Frete grátis
$total = $subtotal + $frete;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?> - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .order-details-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .order-details-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .order-details-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
        }
        .order-info-card {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1d1d1f;
            margin-bottom: 15px;
        }
        .order-info-item {
            margin-bottom: 10px;
        }
        .order-info-label {
            font-weight: 500;
            color: #6e6e73;
        }
        .order-info-value {
            color: #1d1d1f;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-pendente {
            background: #fff3e0;
            color: #ef6c00;
        }
        .status-aprovado {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-enviado {
            background: #e3f2fd;
            color: #1976d2;
        }
        .status-entregue {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-cancelado {
            background: #ffebee;
            color: #c62828;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items-table th {
            background: #f5f5f7;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #1d1d1f;
        }
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #d2d2d7;
        }
        .order-items-table tr:last-child td {
            border-bottom: none;
        }
        .order-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .order-summary {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .order-summary-label {
            color: #6e6e73;
        }
        .order-summary-value {
            font-weight: 500;
            color: #1d1d1f;
        }
        .order-summary-total {
            border-top: 1px solid #d2d2d7;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: 600;
            color: #1d1d1f;
        }
        .back-button {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="order-details-section">
        <div class="container">
            <div class="order-details-container">
                <a href="meus-pedidos.php" class="btn btn-outline-primary back-button">
                    <i class="fas fa-arrow-left"></i> Voltar para Meus Pedidos
                </a>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Detalhes do Pedido #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></h1>
                    <div>
                        <a href="gerar-imagem-pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-info" target="_blank">
                            <i class="fas fa-eye"></i> Visualizar Pedido
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="order-info-card">
                            <h2 class="order-info-title">Informações do Pedido</h2>
                            <div class="order-info-item">
                                <span class="order-info-label">Data do Pedido:</span>
                                <span class="order-info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?></span>
                            </div>
                            <div class="order-info-item">
                                <span class="order-info-label">Status:</span>
                                <span class="order-info-value"><?php echo ucfirst($pedido['status']); ?></span>
                            </div>
                            <div class="order-info-item">
                                <span class="order-info-label">Forma de Pagamento:</span>
                                <span class="order-info-value"><?php echo ucfirst($pedido['metodo_pagamento']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="order-info-card">
                            <h2 class="order-info-title">Endereço de Entrega</h2>
                            <div class="order-info-item">
                                <span class="order-info-label">Endereço:</span>
                                <span class="order-info-value"><?php echo htmlspecialchars($pedido['endereco_entrega'] ?? 'Não informado'); ?></span>
                            </div>
                            <div class="order-info-item">
                                <span class="order-info-label">Cidade:</span>
                                <span class="order-info-value"><?php echo htmlspecialchars($pedido['cidade_entrega'] ?? 'Não informado'); ?></span>
                            </div>
                            <div class="order-info-item">
                                <span class="order-info-label">Estado:</span>
                                <span class="order-info-value"><?php echo htmlspecialchars($pedido['estado_entrega'] ?? 'Não informado'); ?></span>
                            </div>
                            <div class="order-info-item">
                                <span class="order-info-label">CEP:</span>
                                <span class="order-info-value"><?php echo htmlspecialchars($pedido['cep_entrega'] ?? 'Não informado'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-items-card">
                    <h2 class="order-info-title">Itens do Pedido</h2>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço Unitário</th>
                                <th>Quantidade</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo get_imagem_produto_segura($item['imagem']); ?>" 
                                                 class="order-item-image me-3" 
                                                 alt="<?php echo htmlspecialchars($item['produto_nome']); ?>">
                                            <span><?php echo htmlspecialchars($item['produto_nome']); ?></span>
                                        </div>
                                    </td>
                                    <td>Kz <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                    <td><?php echo $item['quantidade']; ?></td>
                                    <td>Kz <?php echo number_format($item['subtotal_item'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="order-summary">
                        <div class="order-summary-item">
                            <span class="order-summary-label">Subtotal:</span>
                            <span class="order-summary-value">Kz <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Frete:</span>
                            <span class="order-summary-value">Grátis</span>
                        </div>
                        <div class="order-summary-item order-summary-total">
                            <span>Total:</span>
                            <span class="order-summary-value" style="color: #0071e3;">Kz <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($pedido['status'] === 'pendente'): ?>
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="cancelarPedido(<?php echo $pedido['id']; ?>)">
                            Cancelar Pedido
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelarPedido(pedidoId) {
            if (confirm('Tem certeza que deseja cancelar este pedido?')) {
                const formData = new FormData();
                formData.append('pedido_id', pedidoId);

                fetch('cancelar-pedido.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'meus-pedidos.php';
                    } else {
                        alert(data.error || 'Erro ao cancelar pedido');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao cancelar pedido');
                });
            }
        }
    </script>
</body>
</html> 