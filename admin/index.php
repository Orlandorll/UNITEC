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
$sql = "SELECT COUNT(*) as total FROM usuarios";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['usuarios'] = $stmt->fetch()['total'];

// Total de produtos
$sql = "SELECT COUNT(*) as total FROM produtos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['produtos'] = $stmt->fetch()['total'];

// Total de pedidos
$sql = "SELECT COUNT(*) as total FROM pedidos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['pedidos'] = $stmt->fetch()['total'];

// Total de vendas
$sql = "SELECT SUM(total) as total FROM pedidos WHERE status != 'cancelado'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['vendas'] = $stmt->fetch()['total'] ?? 0;

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
        FROM produtos p 
        JOIN itens_pedido i ON p.id = i.produto_id 
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
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .admin-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .admin-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1d1d1f;
        }
        .stat-label {
            color: #6e6e73;
            font-size: 0.9rem;
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
        }
        .status-pendente { background-color: #ffc107; color: #000; }
        .status-aprovado { background-color: #28a745; color: #fff; }
        .status-enviado { background-color: #17a2b8; color: #fff; }
        .status-entregue { background-color: #28a745; color: #fff; }
        .status-cancelado { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <h1 class="admin-title">Dashboard</h1>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['usuarios']; ?></div>
                            <div class="stat-label">Usuários</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['produtos']; ?></div>
                            <div class="stat-label">Produtos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['pedidos']; ?></div>
                            <div class="stat-label">Pedidos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-value">R$ <?php echo number_format($stats['vendas'], 2, ',', '.'); ?></div>
                            <div class="stat-label">Vendas</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Pedidos Recentes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pedidos_recentes)): ?>
                                    <div class="alert alert-info">
                                        Nenhum pedido encontrado.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nº</th>
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
                                                            <span class="badge status-badge status-<?php echo $pedido['status']; ?>">
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
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Produtos Mais Vendidos</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($produtos_mais_vendidos)): ?>
                                    <div class="alert alert-info">
                                        Nenhum produto vendido ainda.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
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
                                <?php endif; ?>
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