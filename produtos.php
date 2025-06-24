<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Buscar categorias ativas
$categorias = get_categorias_ativas();

// Parâmetros de filtro e paginação
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'recente';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itens_por_pagina = 12;

// Construir condições WHERE
$where_conditions = ["p.status = 1"];
$busca_conditions = []; // Inicializar variável

if ($categoria_id) {
    $where_conditions[] = "p.categoria_id = $categoria_id";
}

if ($busca) {
    $termos = preg_split('/\s+/', $busca);
    foreach ($termos as $termo) {
        $termo = trim($termo);
        if (strlen($termo) >= 2) {
            $termo_escaped = $conn->quote("%$termo%");
            $busca_conditions[] = "(LOWER(p.nome) LIKE LOWER($termo_escaped) OR LOWER(p.descricao) LIKE LOWER($termo_escaped))";
        }
    }
    if (!empty($busca_conditions)) {
        $where_conditions[] = '(' . implode(' OR ', $busca_conditions) . ')';
    }
}

$where_sql = implode(' AND ', $where_conditions);

// Ordenação
switch ($ordem) {
    case 'preco_menor':
        $order_sql = "ORDER BY p.preco ASC";
        break;
    case 'preco_maior':
        $order_sql = "ORDER BY p.preco DESC";
        break;
    case 'nome':
        $order_sql = "ORDER BY p.nome ASC";
        break;
    default:
        $order_sql = "ORDER BY p.data_criacao DESC";
}

// Paginação
$offset = ($pagina - 1) * $itens_por_pagina;

// Query principal
$sql = "SELECT DISTINCT p.*, c.nome as categoria_nome, 
        (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = p.id AND imagem_principal = 1 LIMIT 1) as imagem
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE $where_sql 
        $order_sql 
        LIMIT $offset, $itens_por_pagina";

// Query de contagem
$sql_count = "SELECT COUNT(*) 
              FROM produtos p 
              LEFT JOIN categorias c ON p.categoria_id = c.id 
              WHERE $where_sql";

// Buscar total de produtos
$stmt_total = $conn->query($sql_count);
$total_produtos = $stmt_total->fetchColumn();
$total_paginas = ceil($total_produtos / $itens_por_pagina);

// Buscar produtos
$stmt = $conn->query($sql);
$produtos = $stmt->fetchAll();

// Debug temporário para identificar o problema
error_log("=== DEBUG BUSCA ===");
error_log("Busca: '$busca'");
error_log("Termos: " . print_r($termos ?? [], true));
error_log("Busca termos: " . print_r($busca_conditions, true));
error_log("SQL: $sql");
error_log("SQL COUNT: $sql_count");
error_log("==================");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .products-section {
            padding: 40px 0;
            background-color: var(--body-bg);
        }
        .filter-sidebar {
            background: var(--body-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .product-card {
            background: var(--body-bg);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
            height: 100%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .product-info {
            padding: 15px;
        }
        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .product-price-promo {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9rem;
        }
        .search-box {
            position: relative;
        }
        .search-box input {
            padding-right: 40px;
        }
        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        .pagination .page-link {
            color: var(--primary-color);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?> 
    <div class="products-section">
        <div class="container">
            <!-- Produtos em Destaque -->
            <?php
            // Buscar produtos em destaque
            $sql_destaque = "SELECT p.*, c.nome as categoria_nome,
                            (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = p.id AND imagem_principal = 1 LIMIT 1) as imagem
                            FROM produtos p 
                            LEFT JOIN categorias c ON p.categoria_id = c.id 
                            WHERE p.status = 1 AND p.destaque = 1 
                            ORDER BY p.data_criacao DESC 
                            LIMIT 4";
            $stmt_destaque = $conn->query($sql_destaque);
            $produtos_destaque = $stmt_destaque->fetchAll();

            if (!empty($produtos_destaque)): ?>
                <div class="mb-5">
                    <h2 class="text-center mb-4">Produtos em Destaque</h2>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                        <?php foreach ($produtos_destaque as $produto): ?>
                            <div class="col">
                                <div class="product-card h-100">
                                    <a href="produto.php?id=<?php echo $produto['id']; ?>" class="text-decoration-none">
                                        <div class="position-relative">
                                            <img src="<?php echo get_imagem_produto_segura($produto['imagem']); ?>" 
                                                 class="product-image" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">Destaque</span>
                                        </div>
                                        <div class="product-info">
                                            <h6 class="mb-2 text-dark"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                            <p class="mb-2 text-muted small"><?php echo htmlspecialchars($produto['categoria_nome']); ?></p>
                                            <?php if ($produto['preco_promocional']): ?>
                                                <span class="product-price-promo">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                <span class="product-price">Kz <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="product-price">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Filtros -->
                <div class="col-lg-3 mb-4">
                    <div class="filter-sidebar">
                        <h5 class="mb-3">Filtros</h5>
                        
                        <!-- Categorias -->
                        <div class="mb-4">
                            <label class="form-label">Categorias</label>
                            <div class="list-group">
                                <a href="produtos.php<?php echo $busca ? '?busca=' . urlencode($busca) : ''; ?>" 
                                   class="list-group-item list-group-item-action <?php echo !$categoria_id ? 'active' : ''; ?>">
                                    Todas as Categorias
                                </a>
                                <?php foreach ($categorias as $cat): ?>
                                    <a href="produtos.php?categoria=<?php echo $cat['id']; ?><?php echo $busca ? '&busca=' . urlencode($busca) : ''; ?>" 
                                       class="list-group-item list-group-item-action <?php echo $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($cat['nome']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Ordenação -->
                        <div class="mb-4">
                            <label class="form-label">Ordenar por</label>
                            <form id="filtro-ordem" method="GET" class="mb-0">
                                <?php if ($categoria_id): ?>
                                    <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                                <?php endif; ?>
                                <?php if ($busca): ?>
                                    <input type="hidden" name="busca" value="<?php echo htmlspecialchars($busca); ?>">
                                <?php endif; ?>
                                <select class="form-select" name="ordem" onchange="this.form.submit()">
                                    <option value="recente" <?php echo $ordem == 'recente' ? 'selected' : ''; ?>>Mais recentes</option>
                                    <option value="preco_menor" <?php echo $ordem == 'preco_menor' ? 'selected' : ''; ?>>Menor preço</option>
                                    <option value="preco_maior" <?php echo $ordem == 'preco_maior' ? 'selected' : ''; ?>>Maior preço</option>
                                    <option value="nome" <?php echo $ordem == 'nome' ? 'selected' : ''; ?>>Nome A-Z</option>
                                </select>
                            </form>
                        </div>

                        <!-- Limpar Filtros -->
                        <?php if ($categoria_id || $busca || $ordem != 'recente'): ?>
                            <div class="mt-4">
                                <a href="produtos.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-2"></i>Limpar Filtros
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de Produtos -->
                <div class="col-lg-9">
                    <?php if ($busca || $categoria_id): ?>
                        <div class="mb-4">
                            <h4>
                                <?php if ($busca): ?>
                                    Resultados para "<?php echo htmlspecialchars($busca); ?>"
                                <?php endif; ?>
                                <?php if ($categoria_id): ?>
                                    <?php 
                                    $categoria_atual = array_filter($categorias, function($cat) use ($categoria_id) {
                                        return $cat['id'] == $categoria_id;
                                    });
                                    $categoria_atual = reset($categoria_atual);
                                    if ($categoria_atual) {
                                        echo " em " . htmlspecialchars($categoria_atual['nome']);
                                    }
                                    ?>
                                <?php endif; ?>
                                <small class="text-muted">(<?php echo $total_produtos; ?> produtos)</small>
                            </h4>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($produtos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h4>Nenhum produto encontrado</h4>
                            <p class="text-muted">
                                <?php if ($busca): ?>
                                    Não encontramos produtos para "<?php echo htmlspecialchars($busca); ?>".
                                <?php elseif ($categoria_id): ?>
                                    Não há produtos nesta categoria no momento.
                                <?php else: ?>
                                    Não há produtos disponíveis no momento.
                                <?php endif; ?>
                            </p>
                            <?php if ($busca || $categoria_id): ?>
                                <a href="produtos.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-sync-alt me-2"></i>Ver todos os produtos
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($produtos as $produto): ?>
                                <div class="col">
                                    <div class="product-card">
                                        <a href="produto.php?id=<?php echo $produto['id']; ?>" class="text-decoration-none">
                                            <img src="<?php echo get_imagem_produto_segura($produto['imagem']); ?>" 
                                                 class="product-image" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                            <div class="product-info">
                                                <h6 class="mb-2 text-dark"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                                <p class="mb-2 text-muted small"><?php echo htmlspecialchars($produto['categoria_nome']); ?></p>
                                                <?php if ($produto['preco_promocional']): ?>
                                                    <span class="product-price-promo">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                    <span class="product-price">Kz <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                                <?php else: ?>
                                                    <span class="product-price">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="row mt-4 mb-5">
                            <div class="col-12 text-center">
                                <a href="carrinho.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-shopping-cart"></i> Finalizar Compra
                                </a>
                                <a href="produtos.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-sync-alt"></i> Continuar Comprando
                                </a>
                            </div>
                        </div>

                        <!-- Paginação -->
                        <?php if ($total_paginas > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 