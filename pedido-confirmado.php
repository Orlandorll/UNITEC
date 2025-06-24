<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o ID do pedido foi fornecido (aceita 'id' ou 'pedido_id')
if (!isset($_GET['id']) && !isset($_GET['pedido_id'])) {
    header("Location: index.php");
    exit;
}

$pedido_id = (int)($_GET['id'] ?? $_GET['pedido_id']);

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

// Se o pedido foi pago (vindo do simulador), atualiza o status e limpa o carrinho
// Isso só acontece uma vez, quando o status ainda está como pendente.
if ($pedido['status'] == 'pendente' || $pedido['status'] == 'aguardando pagamento') {
    // 1. Atualizar o status do pedido para 'Processando'
    $sql_update = "UPDATE pedidos SET status = 'processando' WHERE id = :pedido_id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $stmt_update->execute();

    // 2. Limpar o carrinho de compras
    unset($_SESSION['carrinho']);

    // 3. Recarregar os dados do pedido para mostrar o status atualizado na página
    $pedido['status'] = 'processando';
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

// Debug para verificar os valores
error_log("=== Debug Pedido #" . $pedido_id . " ===");
error_log("Subtotal calculado: " . $subtotal);
error_log("Frete: " . $frete);
error_log("Total calculado: " . $total);
error_log("Valor total no banco: " . $pedido['valor_total']);
error_log("Total calculado na query: " . $pedido['total_calculado']);

// Atualizar o valor_total no banco de dados se necessário
if (abs($total - $pedido['valor_total']) > 0.01) {
    error_log("Atualizando valor total no banco...");
    $sql = "UPDATE pedidos SET valor_total = :total WHERE id = :pedido_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':total', $total);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->execute();
    error_log("Valor total atualizado para: " . $total);
    
    // Atualizar o valor na variável $pedido
    $pedido['valor_total'] = $total;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .confirmation-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .confirmation-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .confirmation-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .confirmation-icon {
            font-size: 4rem;
            color: #34c759;
            margin-bottom: 20px;
        }
        .confirmation-title {
            font-size: 2rem;
            color: #1d1d1f;
            margin-bottom: 10px;
        }
        .confirmation-subtitle {
            color: #6e6e73;
            margin-bottom: 30px;
        }
        .order-info {
            margin-bottom: 30px;
        }
        .order-info-title {
            font-size: 1.2rem;
            color: #1d1d1f;
            margin-bottom: 20px;
        }
        .order-info-item {
            display: flex;
            margin-bottom: 10px;
        }
        .order-info-label {
            width: 150px;
            color: #6e6e73;
        }
        .order-info-value {
            color: #1d1d1f;
            font-weight: 500;
        }
        .order-items {
            margin-bottom: 30px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #d2d2d7;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item-image {
            width: 60px;
            height: 60px;
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
        .order-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #d2d2d7;
        }
        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .order-total-label {
            color: #6e6e73;
        }
        .order-total-value {
            font-weight: 600;
            color: #1d1d1f;
        }
        .next-steps {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .next-steps-title {
            font-size: 1.2rem;
            color: #1d1d1f;
            margin-bottom: 15px;
        }
        .next-steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .next-steps-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #6e6e73;
        }
        .next-steps-item i {
            margin-right: 10px;
            color: #0071e3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="confirmation-section">
        <div class="container">
            <div class="confirmation-container">
                <div class="confirmation-header">
                    <i class="fas fa-check-circle confirmation-icon"></i>
                    <h1 class="confirmation-title">Pedido Confirmado!</h1>
                    <p class="confirmation-subtitle">Obrigado por comprar na Unitec</p>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="order-info">
                            <h2 class="order-info-title">Informações do Pedido</h2>
                            
                            <div class="order-info-item">
                                <span class="order-info-label">Número do Pedido:</span>
                                <span class="order-info-value">#<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            
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

                        <div class="order-items">
                            <h2 class="order-info-title">Itens do Pedido</h2>
                            
                            <?php foreach ($itens as $item): ?>
                                <div class="order-item">
                                    <?php if ($item['imagem']): ?>
                                        <img src="<?php echo get_imagem_produto_segura($item['imagem']); ?>" 
                                             class="order-item-image" 
                                             alt="<?php echo htmlspecialchars($item['produto_nome']); ?>">
                                    <?php else: ?>
                                        <img src="assets/img/no-image.jpg" 
                                             class="order-item-image" 
                                             alt="<?php echo htmlspecialchars($item['produto_nome']); ?>">
                                    <?php endif; ?>
                                    
                                    <div class="order-item-info">
                                        <h4 class="order-item-title"><?php echo htmlspecialchars($item['produto_nome']); ?></h4>
                                        <div class="order-item-price">
                                            Kz <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                            x <?php echo $item['quantidade']; ?>
                                            <span class="float-end">
                                                Kz <?php echo number_format($item['subtotal_item'], 2, ',', '.'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="order-total">
                                <div class="order-total-row">
                                    <span class="order-total-label">Subtotal (<?php echo count($itens); ?> itens):</span>
                                    <span class="order-total-value">Kz <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                </div>
                                <div class="order-total-row">
                                    <span class="order-total-label">Frete:</span>
                                    <span class="order-total-value">Grátis</span>
                                </div>
                                <div class="order-total-row" style="font-size: 1.2em; font-weight: bold; border-top: 2px solid #d2d2d7; padding-top: 10px; margin-top: 10px;">
                                    <span class="order-total-label">Total a Pagar:</span>
                                    <span class="order-total-value" style="color: #0071e3;">Kz <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="next-steps">
                            <h3 class="next-steps-title">Próximos Passos</h3>
                            
                            <ul class="next-steps-list">
                                <li class="next-steps-item">
                                    <i class="fas fa-envelope"></i>
                                    Você receberá um e-mail com os detalhes do pedido
                                </li>
                                <li class="next-steps-item">
                                    <i class="fas fa-credit-card"></i>
                                    <?php if ($pedido['metodo_pagamento'] == 'express'): ?>
                                        O código PIX será enviado por e-mail
                                    <?php endif; ?>
                                </li>
                                <li class="next-steps-item">
                                    <i class="fas fa-truck"></i>
                                    Acompanhe o status do seu pedido em "Meus Pedidos"
                                </li>
                            </ul>

                            <a href="meus-pedidos.php" class="btn btn-primary w-100 mt-3">
                                Ver Meus Pedidos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 