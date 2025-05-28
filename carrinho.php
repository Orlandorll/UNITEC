<?php
session_start();
require_once "config/database.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?redirect=carrinho.php");
    exit;
}

// Processar remoção de item
if (isset($_POST['remover_item'])) {
    $item_id = (int)$_POST['item_id'];
    $sql = "DELETE FROM carrinho WHERE id = :id AND usuario_id = :usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $item_id);
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt->execute();
}

// Processar atualização de quantidade
if (isset($_POST['atualizar_quantidade'])) {
    $item_id = (int)$_POST['item_id'];
    $quantidade = (int)$_POST['quantidade'];
    
    if ($quantidade > 0) {
        $sql = "UPDATE carrinho SET quantidade = :quantidade 
                WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':id', $item_id);
        $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
        $stmt->execute();
    }
}

// Buscar itens do carrinho
$sql = "SELECT c.*, p.nome, p.preco, p.preco_promocional, p.estoque, 
        (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = p.id AND imagem_principal = 1 LIMIT 1) as imagem
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id 
        WHERE c.usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$itens = $stmt->fetchAll();

// Calcular totais
$subtotal = 0;
$total_itens = 0;

foreach ($itens as $item) {
    $preco = $item['preco_promocional'] ? $item['preco_promocional'] : $item['preco'];
    $subtotal += $preco * $item['quantidade'];
    $total_itens += $item['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .cart-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #d2d2d7;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-title {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #1d1d1f;
        }
        .cart-item-price {
            font-weight: 600;
            color: #0071e3;
        }
        .cart-item-price-promo {
            text-decoration: line-through;
            color: #86868b;
            font-size: 0.9rem;
            margin-right: 10px;
        }
        .cart-item-quantity {
            width: 80px;
        }
        .cart-summary {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 20px;
        }
        .cart-summary-title {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #1d1d1f;
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #6e6e73;
        }
        .cart-summary-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1d1d1f;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #d2d2d7;
        }
        .empty-cart {
            text-align: center;
            padding: 40px 0;
        }
        .empty-cart i {
            font-size: 4rem;
            color: #d2d2d7;
            margin-bottom: 20px;
        }
        .empty-cart h3 {
            color: #1d1d1f;
            margin-bottom: 10px;
        }
        .empty-cart p {
            color: #6e6e73;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="cart-section">
        <div class="container">
            <h1 class="mb-4">Carrinho de Compras</h1>
            
            <?php if (empty($itens)): ?>
                <div class="cart-container">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Seu carrinho está vazio</h3>
                        <p>Adicione produtos ao seu carrinho para continuar comprando.</p>
                        <a href="produtos.php" class="btn btn-primary">Continuar Comprando</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="cart-container">
                            <?php foreach ($itens as $item): ?>
                                <div class="cart-item">
                                    <img src="<?php echo $item['imagem'] ?: 'assets/img/no-image.jpg'; ?>" 
                                         class="cart-item-image" 
                                         alt="<?php echo htmlspecialchars($item['nome']); ?>">
                                    
                                    <div class="cart-item-info">
                                        <h3 class="cart-item-title"><?php echo htmlspecialchars($item['nome']); ?></h3>
                                        <div class="cart-item-price">
                                            <?php if ($item['preco_promocional']): ?>
                                                <span class="cart-item-price-promo">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                                R$ <?php echo number_format($item['preco_promocional'], 2, ',', '.'); ?>
                                            <?php else: ?>
                                                R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="cart-item-actions">
                                        <form method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" 
                                                   min="1" max="<?php echo $item['estoque']; ?>" 
                                                   class="form-control cart-item-quantity me-2">
                                            <button type="submit" name="atualizar_quantidade" class="btn btn-outline-primary me-2">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <button type="submit" name="remover_item" class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h3 class="cart-summary-title">Resumo do Pedido</h3>
                            
                            <div class="cart-summary-row">
                                <span>Subtotal (<?php echo $total_itens; ?> itens)</span>
                                <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                            </div>
                            
                            <div class="cart-summary-row">
                                <span>Frete</span>
                                <span>Grátis</span>
                            </div>
                            
                            <div class="cart-summary-total">
                                <div class="cart-summary-row">
                                    <span>Total</span>
                                    <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                </div>
                            </div>

                            <a href="checkout.php" class="btn btn-primary w-100 mt-3">
                                Finalizar Compra
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 