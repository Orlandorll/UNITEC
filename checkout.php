<?php
session_start();
require_once "config/database.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// Buscar informações do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->fetch();

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

// Processar o pedido
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    // Validar dados do formulário
    $endereco = trim($_POST['endereco']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    $cep = trim($_POST['cep']);
    $forma_pagamento = $_POST['forma_pagamento'];

    if (empty($endereco) || empty($cidade) || empty($estado) || empty($cep)) {
        $mensagem = "Por favor, preencha todos os campos do endereço.";
    } else {
        try {
            $conn->beginTransaction();

            // Criar pedido
            $sql = "INSERT INTO pedidos (usuario_id, endereco, cidade, estado, cep, forma_pagamento, total, status) 
                    VALUES (:usuario_id, :endereco, :cidade, :estado, :cep, :forma_pagamento, :total, 'pendente')";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':cep', $cep);
            $stmt->bindParam(':forma_pagamento', $forma_pagamento);
            $stmt->bindParam(':total', $subtotal);
            $stmt->execute();
            $pedido_id = $conn->lastInsertId();

            // Adicionar itens do pedido
            foreach ($itens as $item) {
                $preco = $item['preco_promocional'] ? $item['preco_promocional'] : $item['preco'];
                
                $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) 
                        VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':pedido_id', $pedido_id);
                $stmt->bindParam(':produto_id', $item['produto_id']);
                $stmt->bindParam(':quantidade', $item['quantidade']);
                $stmt->bindParam(':preco_unitario', $preco);
                $stmt->execute();

                // Atualizar estoque
                $sql = "UPDATE produtos SET estoque = estoque - :quantidade WHERE id = :produto_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':quantidade', $item['quantidade']);
                $stmt->bindParam(':produto_id', $item['produto_id']);
                $stmt->execute();
            }

            // Limpar carrinho
            $sql = "DELETE FROM carrinho WHERE usuario_id = :usuario_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $stmt->execute();

            $conn->commit();
            header("Location: pedido-confirmado.php?id=" . $pedido_id);
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $mensagem = "Erro ao processar o pedido. Por favor, tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .checkout-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .checkout-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
        }
        .order-summary {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 20px;
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
        .order-summary-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #d2d2d7;
        }
        .payment-method {
            margin-bottom: 20px;
        }
        .payment-method label {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #d2d2d7;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method label:hover {
            border-color: #0071e3;
        }
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        .payment-method-icon {
            margin-right: 10px;
            font-size: 1.2rem;
            color: #6e6e73;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-section">
        <div class="container">
            <?php if ($mensagem): ?>
                <div class="alert alert-danger"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <?php if (empty($itens)): ?>
                <div class="checkout-container">
                    <div class="text-center">
                        <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                        <h3>Seu carrinho está vazio</h3>
                        <p>Adicione produtos ao seu carrinho para continuar com a compra.</p>
                        <a href="produtos.php" class="btn btn-primary">Continuar Comprando</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-container">
                            <h2 class="checkout-title">Informações de Entrega</h2>
                            
                            <form method="POST" id="checkout-form">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="endereco" class="form-label">Endereço</label>
                                    <input type="text" class="form-control" id="endereco" name="endereco" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cidade" class="form-label">Cidade</label>
                                        <input type="text" class="form-control" id="cidade" name="cidade" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <input type="text" class="form-control" id="estado" name="estado" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="cep" class="form-label">CEP</label>
                                        <input type="text" class="form-control" id="cep" name="cep" required>
                                    </div>
                                </div>

                                <h3 class="checkout-title mt-4">Forma de Pagamento</h3>
                                
                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="cartao" required>
                                        <i class="fas fa-credit-card payment-method-icon"></i>
                                        Cartão de Crédito
                                    </label>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="boleto">
                                        <i class="fas fa-barcode payment-method-icon"></i>
                                        Boleto Bancário
                                    </label>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="pix">
                                        <i class="fas fa-qrcode payment-method-icon"></i>
                                        PIX
                                    </label>
                                </div>

                                <button type="submit" name="finalizar_pedido" class="btn btn-primary btn-lg w-100 mt-4">
                                    Finalizar Pedido
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h3 class="checkout-title">Resumo do Pedido</h3>
                            
                            <?php foreach ($itens as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo $item['imagem'] ?: 'assets/img/no-image.jpg'; ?>" 
                                         class="order-item-image" 
                                         alt="<?php echo htmlspecialchars($item['nome']); ?>">
                                    
                                    <div class="order-item-info">
                                        <h4 class="order-item-title"><?php echo htmlspecialchars($item['nome']); ?></h4>
                                        <div class="order-item-price">
                                            <?php if ($item['preco_promocional']): ?>
                                                <span class="text-decoration-line-through">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                                R$ <?php echo number_format($item['preco_promocional'], 2, ',', '.'); ?>
                                            <?php else: ?>
                                                R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                            <?php endif; ?>
                                            x <?php echo $item['quantidade']; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="order-summary-total">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal (<?php echo $total_itens; ?> itens)</span>
                                    <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Frete</span>
                                    <span>Grátis</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para CEP
        document.getElementById('cep').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 8);
            }
            e.target.value = value;
        });
    </script>
</body>
</html> 