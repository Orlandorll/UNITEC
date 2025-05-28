<?php
session_start();
require_once "config/database.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?redirect=meus-pedidos.php");
    exit;
}

// Buscar pedidos do usuário
$sql = "SELECT * FROM pedidos WHERE usuario_id = :usuario_id ORDER BY data_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .orders-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .orders-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .orders-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
        }
        .order-card {
            border: 1px solid #d2d2d7;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            background: #f5f5f7;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-number {
            font-weight: 600;
            color: #1d1d1f;
        }
        .order-date {
            color: #6e6e73;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
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
        .order-body {
            padding: 20px;
        }
        .order-items {
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #d2d2d7;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #1d1d1f;
        }
        .order-item-price {
            font-size: 0.9rem;
            color: #6e6e73;
        }
        .order-footer {
            padding: 15px 20px;
            background: #f5f5f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-total {
            font-weight: 600;
            color: #1d1d1f;
        }
        .order-actions {
            display: flex;
            gap: 10px;
        }
        .empty-orders {
            text-align: center;
            padding: 40px 0;
        }
        .empty-orders i {
            font-size: 4rem;
            color: #d2d2d7;
            margin-bottom: 20px;
        }
        .empty-orders h3 {
            color: #1d1d1f;
            margin-bottom: 10px;
        }
        .empty-orders p {
            color: #6e6e73;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-section">
        <div class="container">
            <div class="orders-container">
                <h1 class="orders-title">Meus Pedidos</h1>

                <?php if (empty($pedidos)): ?>
                    <div class="empty-orders">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Você ainda não fez nenhum pedido</h3>
                        <p>Explore nossa loja e encontre produtos incríveis.</p>
                        <a href="produtos.php" class="btn btn-primary">Ver Produtos</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-number">Pedido #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></span>
                                    <span class="order-date ms-3"><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></span>
                                </div>
                                <span class="order-status status-<?php echo $pedido['status']; ?>">
                                    <?php
                                    $status_labels = [
                                        'pendente' => 'Pendente',
                                        'aprovado' => 'Aprovado',
                                        'enviado' => 'Enviado',
                                        'entregue' => 'Entregue',
                                        'cancelado' => 'Cancelado'
                                    ];
                                    echo $status_labels[$pedido['status']];
                                    ?>
                                </span>
                            </div>

                            <div class="order-body">
                                <?php
                                // Buscar itens do pedido
                                $sql = "SELECT ip.*, pr.nome as produto_nome, 
                                        (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = pr.id AND imagem_principal = 1 LIMIT 1) as imagem
                                        FROM itens_pedido ip 
                                        JOIN produtos pr ON ip.produto_id = pr.id 
                                        WHERE ip.pedido_id = :pedido_id";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':pedido_id', $pedido['id']);
                                $stmt->execute();
                                $itens = $stmt->fetchAll();
                                ?>

                                <div class="order-items">
                                    <?php foreach ($itens as $item): ?>
                                        <div class="order-item">
                                            <img src="<?php echo $item['imagem'] ?: 'assets/img/no-image.jpg'; ?>" 
                                                 class="order-item-image" 
                                                 alt="<?php echo htmlspecialchars($item['produto_nome']); ?>">
                                            
                                            <div class="order-item-info">
                                                <h4 class="order-item-title"><?php echo htmlspecialchars($item['produto_nome']); ?></h4>
                                                <div class="order-item-price">
                                                    R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>
                                                    x <?php echo $item['quantidade']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="order-footer">
                                    <div class="order-total">
                                        Total: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                                    </div>
                                    <div class="order-actions">
                                        <a href="pedido-detalhes.php?id=<?php echo $pedido['id']; ?>" class="btn btn-outline-primary">
                                            Ver Detalhes
                                        </a>
                                        <?php if ($pedido['status'] === 'pendente'): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="cancelarPedido(<?php echo $pedido['id']; ?>)">
                                                Cancelar Pedido
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                        window.location.reload();
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