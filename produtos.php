<?php
session_start();
require_once "config/database.php";

// Parâmetros de filtro e paginação
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'recente';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itens_por_pagina = 12;

// Construir a query base
$sql = "SELECT p.*, c.nome as categoria_nome, 
        (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = p.id AND imagem_principal = 1 LIMIT 1) as imagem
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.status = 1";

$params = [];

// Adicionar filtros
if ($categoria_id) {
    $sql .= " AND p.categoria_id = :categoria_id";
    $params[':categoria_id'] = $categoria_id;
}

if ($busca) {
    $sql .= " AND (p.nome LIKE :busca OR p.descricao LIKE :busca)";
    $params[':busca'] = "%$busca%";
}

// Ordenação
switch ($ordem) {
    case 'preco_menor':
        $sql .= " ORDER BY p.preco ASC";
        break;
    case 'preco_maior':
        $sql .= " ORDER BY p.preco DESC";
        break;
    case 'nome':
        $sql .= " ORDER BY p.nome ASC";
        break;
    default: // recente
        $sql .= " ORDER BY p.data_criacao DESC";
}

// Buscar categorias para o filtro
$stmt_categorias = $conn->query("SELECT * FROM categorias WHERE status = 1 ORDER BY nome");
$categorias = $stmt_categorias->fetchAll();

// Calcular total de produtos para paginação
$stmt_total = $conn->prepare(str_replace("p.*, c.nome as categoria_nome", "COUNT(*)", $sql));
foreach ($params as $key => $value) {
    $stmt_total->bindValue($key, $value);
}
$stmt_total->execute();
$total_produtos = $stmt_total->fetchColumn();
$total_paginas = ceil($total_produtos / $itens_por_pagina);

// Adicionar paginação
$offset = ($pagina - 1) * $itens_por_pagina;
$sql .= " LIMIT :offset, :limit";
$params[':offset'] = (int)$offset;
$params[':limit'] = (int)$itens_por_pagina;

// Buscar produtos
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$produtos = $stmt->fetchAll();
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
            background: #f8f9fa;
        }
        .filter-sidebar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
            height: 100%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .product-card:hover {
            transform: translateY(-5px);
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
            <div class="row">
                <!-- Filtros -->
                <div class="col-lg-3 mb-4">
                    <div class="filter-sidebar">
                        <h5 class="mb-3">Filtros</h5>
                        
                        <!-- Busca -->
                        <form action="" method="GET" class="mb-4">
                            <div class="search-box">
                                <input type="text" class="form-control" name="busca" 
                                       placeholder="Buscar produtos..." value="<?php echo htmlspecialchars($busca); ?>">
                                <i class="fas fa-search"></i>
                            </div>
                            
                            <!-- Categorias -->
                            <div class="mt-3">
                                <label class="form-label">Categorias</label>
                                <div class="list-group">
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['categoria' => null])); ?>" 
                                       class="list-group-item list-group-item-action <?php echo !$categoria_id ? 'active' : ''; ?>">
                                        Todas as categorias
                                    </a>
                                    <?php foreach ($categorias as $cat): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['categoria' => $cat['id']])); ?>" 
                                           class="list-group-item list-group-item-action <?php echo $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Ordenação -->
                            <div class="mt-3">
                                <label class="form-label">Ordenar por</label>
                                <select class="form-select" name="ordem" onchange="this.form.submit()">
                                    <option value="recente" <?php echo $ordem == 'recente' ? 'selected' : ''; ?>>Mais recentes</option>
                                    <option value="preco_menor" <?php echo $ordem == 'preco_menor' ? 'selected' : ''; ?>>Menor preço</option>
                                    <option value="preco_maior" <?php echo $ordem == 'preco_maior' ? 'selected' : ''; ?>>Maior preço</option>
                                    <option value="nome" <?php echo $ordem == 'nome' ? 'selected' : ''; ?>>Nome A-Z</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Produtos -->
                <div class="col-lg-9">
                    <?php if (empty($produtos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h4>Nenhum produto encontrado</h4>
                            <p class="text-muted">Tente ajustar seus filtros ou buscar por outro termo.</p>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($produtos as $produto): ?>
                                <div class="col">
                                    <div class="product-card">
                                        <a href="produto.php?id=<?php echo $produto['id']; ?>" class="text-decoration-none">
                                            <img src="<?php echo $produto['imagem'] ?: 'assets/img/no-image.jpg'; ?>" 
                                                 class="product-image" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                            <div class="product-info">
                                                <h6 class="mb-2 text-dark"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                                <p class="mb-2 text-muted small"><?php echo htmlspecialchars($produto['categoria_nome']); ?></p>
                                                <?php if ($produto['preco_promocional']): ?>
                                                    <span class="product-price-promo">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                    <span class="product-price">R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                                <?php else: ?>
                                                    <span class="product-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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