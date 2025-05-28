<?php
session_start();
require_once "config/database.php";

// Verificar se o ID do produto foi fornecido
if (!isset($_GET['id'])) {
    header("Location: produtos.php");
    exit;
}

$produto_id = (int)$_GET['id'];

// Buscar informações do produto
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.id = :id AND p.status = 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $produto_id);
$stmt->execute();
$produto = $stmt->fetch();

if (!$produto) {
    header("Location: produtos.php");
    exit;
}

// Buscar imagens do produto
$sql = "SELECT * FROM imagens_produtos WHERE produto_id = :produto_id ORDER BY imagem_principal DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':produto_id', $produto_id);
$stmt->execute();
$imagens = $stmt->fetchAll();

// Buscar atributos do produto
$sql = "SELECT * FROM atributos_produtos WHERE produto_id = :produto_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':produto_id', $produto_id);
$stmt->execute();
$atributos = $stmt->fetchAll();

// Processar adição ao carrinho
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_carrinho'])) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php?redirect=produto.php?id=" . $produto_id);
        exit;
    }

    $quantidade = (int)$_POST['quantidade'];
    
    if ($quantidade > 0 && $quantidade <= $produto['estoque']) {
        // Verificar se o produto já está no carrinho
        $sql = "SELECT * FROM carrinho WHERE usuario_id = :usuario_id AND produto_id = :produto_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
        $stmt->bindParam(':produto_id', $produto_id);
        $stmt->execute();
        $item_carrinho = $stmt->fetch();

        if ($item_carrinho) {
            // Atualizar quantidade
            $nova_quantidade = $item_carrinho['quantidade'] + $quantidade;
            if ($nova_quantidade <= $produto['estoque']) {
                $sql = "UPDATE carrinho SET quantidade = :quantidade WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':quantidade', $nova_quantidade);
                $stmt->bindParam(':id', $item_carrinho['id']);
                $stmt->execute();
                $mensagem = "Quantidade atualizada no carrinho!";
            } else {
                $mensagem = "Quantidade indisponível em estoque!";
            }
        } else {
            // Adicionar novo item
            $sql = "INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (:usuario_id, :produto_id, :quantidade)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->execute();
            $mensagem = "Produto adicionado ao carrinho!";
        }
    } else {
        $mensagem = "Quantidade inválida!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produto['nome']); ?> - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .product-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .product-gallery {
            position: relative;
            margin-bottom: 30px;
        }
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }
        .thumbnail:hover {
            border-color: #0071e3;
        }
        .product-info {
            padding: 20px;
        }
        .product-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #1d1d1f;
        }
        .product-category {
            color: #6e6e73;
            margin-bottom: 20px;
        }
        .product-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #0071e3;
            margin-bottom: 20px;
        }
        .product-price-promo {
            text-decoration: line-through;
            color: #86868b;
            font-size: 1.2rem;
            margin-right: 10px;
        }
        .product-description {
            color: #1d1d1f;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .product-attributes {
            margin-bottom: 30px;
        }
        .attribute-item {
            display: flex;
            margin-bottom: 10px;
        }
        .attribute-name {
            font-weight: 600;
            width: 120px;
            color: #6e6e73;
        }
        .attribute-value {
            color: #1d1d1f;
        }
        .stock-info {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .stock-info.in-stock {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .stock-info.low-stock {
            background: #fff3e0;
            color: #ef6c00;
        }
        .stock-info.out-of-stock {
            background: #ffebee;
            color: #c62828;
        }
        .add-to-cart-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .quantity-input {
            width: 100px;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="product-section">
        <div class="container">
            <?php if ($mensagem): ?>
                <div class="alert alert-info"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <div class="product-container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="product-gallery">
                            <img src="<?php echo $imagens[0]['caminho_imagem'] ?? 'assets/img/no-image.jpg'; ?>" 
                                 class="main-image" 
                                 id="mainImage"
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                            
                            <div class="thumbnail-container">
                                <?php foreach ($imagens as $imagem): ?>
                                    <img src="<?php echo $imagem['caminho_imagem']; ?>" 
                                         class="thumbnail" 
                                         onclick="changeImage(this.src)"
                                         alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="product-info">
                            <h1 class="product-title"><?php echo htmlspecialchars($produto['nome']); ?></h1>
                            <div class="product-category"><?php echo htmlspecialchars($produto['categoria_nome']); ?></div>

                            <div class="product-price">
                                <?php if ($produto['preco_promocional']): ?>
                                    <span class="product-price-promo">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                    R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?>
                                <?php else: ?>
                                    R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                <?php endif; ?>
                            </div>

                            <div class="product-description">
                                <?php echo nl2br(htmlspecialchars($produto['descricao'])); ?>
                            </div>

                            <?php if (!empty($atributos)): ?>
                                <div class="product-attributes">
                                    <?php foreach ($atributos as $atributo): ?>
                                        <div class="attribute-item">
                                            <span class="attribute-name"><?php echo htmlspecialchars($atributo['nome']); ?>:</span>
                                            <span class="attribute-value"><?php echo htmlspecialchars($atributo['valor']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="stock-info <?php echo $produto['estoque'] > 10 ? 'in-stock' : ($produto['estoque'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                <?php if ($produto['estoque'] > 10): ?>
                                    <i class="fas fa-check-circle"></i> Em estoque
                                <?php elseif ($produto['estoque'] > 0): ?>
                                    <i class="fas fa-exclamation-circle"></i> Últimas unidades
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i> Fora de estoque
                                <?php endif; ?>
                            </div>

                            <?php if ($produto['estoque'] > 0): ?>
                                <form method="POST" class="add-to-cart-form">
                                    <input type="number" name="quantidade" value="1" min="1" max="<?php echo $produto['estoque']; ?>" 
                                           class="form-control quantity-input">
                                    <button type="submit" name="adicionar_carrinho" class="btn btn-primary">
                                        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
</body>
</html> 