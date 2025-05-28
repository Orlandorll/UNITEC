<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Processar atualização de status
if (isset($_POST['atualizar_status']) && isset($_POST['id']) && isset($_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $status_permitidos = ['pendente', 'aprovado', 'enviado', 'entregue', 'cancelado'];

    if (in_array($status, $status_permitidos)) {
        $sql = "UPDATE pedidos SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$status, $id]);
        header("Location: pedidos.php?msg=atualizado");
        exit;
    }
}

// Buscar pedidos com paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Filtros
$where = [];
$params = [];

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where[] = "p.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $where[] = "(u.nome LIKE ? OR p.id LIKE ?)";
    $params[] = "%{$_GET['busca']}%";
    $params[] = "%{$_GET['busca']}%";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Buscar total de pedidos
$sql = "SELECT COUNT(*) as total 
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        $where_clause";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$total_pedidos = $stmt->fetch()['total'];
$total_paginas = ceil($total_pedidos / $por_pagina);

// Buscar pedidos
$sql = "SELECT p.*, u.nome as nome_usuario, u.email as email_usuario 
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        $where_clause 
        ORDER BY p.data_pedido DESC 
        LIMIT $por_pagina OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos - UNITEC</title>
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
        .table th {
            font-weight: 500;
            color: #2c3e50;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                <h1 class="admin-title">Gerenciar Pedidos</h1>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        $msg = $_GET['msg'];
                        if ($msg === 'atualizado') {
                            echo 'Status do pedido atualizado com sucesso!';
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" name="busca" class="form-control" 
                                       value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>"
                                       placeholder="Nome do cliente ou ID do pedido...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos os status</option>
                                    <option value="pendente" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pendente') ? 'selected' : ''; ?>>
                                        Pendente
                                    </option>
                                    <option value="aprovado" <?php echo (isset($_GET['status']) && $_GET['status'] === 'aprovado') ? 'selected' : ''; ?>>
                                        Aprovado
                                    </option>
                                    <option value="enviado" <?php echo (isset($_GET['status']) && $_GET['status'] === 'enviado') ? 'selected' : ''; ?>>
                                        Enviado
                                    </option>
                                    <option value="entregue" <?php echo (isset($_GET['status']) && $_GET['status'] === 'entregue') ? 'selected' : ''; ?>>
                                        Entregue
                                    </option>
                                    <option value="cancelado" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelado') ? 'selected' : ''; ?>>
                                        Cancelado
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search me-1"></i>
                                    Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pedidos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        Nenhum pedido encontrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($pedido['nome_usuario']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($pedido['email_usuario']); ?></small>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
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
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary btn-action"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalDetalhes<?php echo $pedido['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary btn-action"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalStatus<?php echo $pedido['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>

                                            <!-- Modal de Detalhes -->
                                            <div class="modal fade" id="modalDetalhes<?php echo $pedido['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                Detalhes do Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            // Buscar itens do pedido
                                                            $sql = "SELECT i.*, p.nome as produto_nome, p.preco 
                                                                    FROM itens_pedido i 
                                                                    JOIN produtos p ON i.produto_id = p.id 
                                                                    WHERE i.pedido_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->execute([$pedido['id']]);
                                                            $itens = $stmt->fetchAll();
                                                            ?>
                                                            <div class="mb-4">
                                                                <h6>Informações do Cliente</h6>
                                                                <p class="mb-1">
                                                                    <strong>Nome:</strong> <?php echo htmlspecialchars($pedido['nome_usuario']); ?>
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Email:</strong> <?php echo htmlspecialchars($pedido['email_usuario']); ?>
                                                                </p>
                                                                <p class="mb-0">
                                                                    <strong>Data do Pedido:</strong> 
                                                                    <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                                                                </p>
                                                            </div>

                                                            <h6>Itens do Pedido</h6>
                                                            <div class="table-responsive">
                                                                <table class="table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Produto</th>
                                                                            <th>Quantidade</th>
                                                                            <th>Preço Unit.</th>
                                                                            <th>Subtotal</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($itens as $item): ?>
                                                                            <tr>
                                                                                <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                                                                <td><?php echo $item['quantidade']; ?></td>
                                                                                <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                                                                <td>R$ <?php echo number_format($item['quantidade'] * $item['preco'], 2, ',', '.'); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                                            <td><strong>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal de Status -->
                                            <div class="modal fade" id="modalStatus<?php echo $pedido['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Atualizar Status</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?php echo $pedido['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status do Pedido</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="pendente" <?php echo $pedido['status'] === 'pendente' ? 'selected' : ''; ?>>
                                                                            Pendente
                                                                        </option>
                                                                        <option value="aprovado" <?php echo $pedido['status'] === 'aprovado' ? 'selected' : ''; ?>>
                                                                            Aprovado
                                                                        </option>
                                                                        <option value="enviado" <?php echo $pedido['status'] === 'enviado' ? 'selected' : ''; ?>>
                                                                            Enviado
                                                                        </option>
                                                                        <option value="entregue" <?php echo $pedido['status'] === 'entregue' ? 'selected' : ''; ?>>
                                                                            Entregue
                                                                        </option>
                                                                        <option value="cancelado" <?php echo $pedido['status'] === 'cancelado' ? 'selected' : ''; ?>>
                                                                            Cancelado
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="atualizar_status" class="btn btn-primary">Atualizar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_paginas > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['busca']) ? '&busca=' . urlencode($_GET['busca']) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['busca']) ? '&busca=' . urlencode($_GET['busca']) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagina < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['busca']) ? '&busca=' . urlencode($_GET['busca']) : ''; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 