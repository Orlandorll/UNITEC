<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Buscar estatísticas
$stats = [];

// Total de usuários
$sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'cliente'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['usuarios'] = $stmt->fetch()['total'];

// Total de produtos
$sql = "SELECT COUNT(*) as total FROM produtos WHERE status = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['produtos'] = $stmt->fetch()['total'];

// Total de pedidos
$sql = "SELECT COUNT(*) as total FROM pedidos WHERE status != 'cancelado'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['pedidos'] = $stmt->fetch()['total'];

// Total de vendas
$sql = "SELECT COALESCE(SUM(total), 0) as total FROM pedidos WHERE status != 'cancelado'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['vendas'] = $stmt->fetch()['total'];

// Pedidos recentes
$sql = "SELECT p.*, u.nome as nome_usuario 
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.data_pedido DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos_recentes = $stmt->fetchAll();

// Produtos mais vendidos
$sql = "SELECT p.nome, SUM(i.quantidade) as total_vendido 
        FROM itens_pedido i 
        JOIN produtos p ON i.produto_id = p.id 
        JOIN pedidos pd ON i.pedido_id = pd.id 
        WHERE pd.status != 'cancelado' 
        GROUP BY p.id 
        ORDER BY total_vendido DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos_mais_vendidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-section {
            padding: 2rem 0;
        }
        .admin-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .admin-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #dee2e6;
        }
        .stat-card {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .quick-access {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .quick-access .btn {
            margin: 0.25rem;
        }
        .table th {
            font-weight: 500;
            color: #2c3e50;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 3px;
        }
        .status-pendente { background-color: #f39c12; color: white; }
        .status-aprovado { background-color: #27ae60; color: white; }
        .status-enviado { background-color: #3498db; color: white; }
        .status-entregue { background-color: #27ae60; color: white; }
        .status-cancelado { background-color: #e74c3c; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <h1 class="admin-title">Painel Administrativo</h1>

                <!-- Acesso Rápido -->
                <div class="quick-access mb-4">
                    <h5 class="mb-3">Acesso Rápido</h5>
                    <div class="d-flex flex-wrap">
                        <a href="produtos.php" class="btn btn-primary">
                            <i class="fas fa-box me-1"></i>
                            Gerenciar Produtos
                        </a>
                        <a href="adicionar-produto.php" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            Novo Produto
                        </a>
                        <a href="categorias.php" class="btn btn-info text-white">
                            <i class="fas fa-tags me-1"></i>
                            Categorias
                        </a>
                        <a href="pedidos.php" class="btn btn-warning text-white">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Pedidos
                        </a>
                        <a href="usuarios.php" class="btn btn-secondary">
                            <i class="fas fa-users me-1"></i>
                            Usuários
                        </a>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['usuarios']; ?></div>
                            <div class="stat-label">Total de Usuários</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['produtos']; ?></div>
                            <div class="stat-label">Total de Produtos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-warning">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['pedidos']; ?></div>
                            <div class="stat-label">Total de Pedidos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-value">R$ <?php echo number_format($stats['vendas'], 2, ',', '.'); ?></div>
                            <div class="stat-label">Total de Vendas</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Pedidos Recentes -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Pedidos Recentes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Cliente</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pedidos_recentes as $pedido): ?>
                                                <tr>
                                                    <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                    <td><?php echo htmlspecialchars($pedido['nome_usuario']); ?></td>
                                                    <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo $pedido['status']; ?>">
                                                            <?php
                                                            $status_labels = [
                                                                'pendente' => 'Pendente',
                                                                'aprovado' => 'Aprovado',
                                                                'enviado' => 'Enviado',
                                                                'entregue' => 'Entregue',
                                                                'cancelado' => 'Cancelado'
                                                            ];
                                                            echo $status_labels[$pedido['status']] ?? ucfirst($pedido['status']);
                                                            ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="pedidos.php" class="btn btn-sm btn-outline-primary">
                                        Ver Todos os Pedidos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Produtos Mais Vendidos -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Produtos Mais Vendidos</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Total Vendido</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($produtos_mais_vendidos as $produto): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                                    <td><?php echo $produto['total_vendido']; ?> unidades</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="produtos.php" class="btn btn-sm btn-outline-primary">
                                        Ver Todos os Produtos
                                    </a>
                                </div>
                            </div>
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