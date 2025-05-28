<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Verificar se o ID do usuário foi fornecido
if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$usuario_id = (int)$_GET['id'];

// Buscar informações do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $usuario_id);
$stmt->execute();
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: usuarios.php");
    exit;
}

// Buscar pedidos do usuário
$sql = "SELECT p.*, COUNT(i.id) as total_itens 
        FROM pedidos p 
        LEFT JOIN itens_pedido i ON p.id = i.pedido_id 
        WHERE p.usuario_id = :usuario_id 
        GROUP BY p.id 
        ORDER BY p.data_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Usuário - Painel Administrativo</title>
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
        .info-label {
            font-weight: 500;
            color: #1d1d1f;
        }
        .info-value {
            color: #666;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Detalhes do Usuário</h1>
                    <div>
                        <a href="editar-usuario.php?id=<?php echo $usuario_id; ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="usuarios.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3">Informações Pessoais</h5>
                        <div class="mb-2">
                            <span class="info-label">Nome:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nome']); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="info-label">E-mail:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="info-label">Telefone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['telefone'] ?? 'Não informado'); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="info-label">NIF:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nif'] ?? 'Não informado'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">Informações da Conta</h5>
                        <div class="mb-2">
                            <span class="info-label">Tipo de Usuário:</span>
                            <span class="info-value">
                                <?php echo $usuario['tipo_usuario'] === 'pessoa' ? 'Pessoa Física' : 'Pessoa Jurídica'; ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="info-label">Tipo de Acesso:</span>
                            <span class="info-value">
                                <?php echo $usuario['tipo'] === 'admin' ? 'Administrador' : 'Cliente'; ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="info-label">Data de Cadastro:</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <?php if ($usuario['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inativo</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <h5 class="mb-3">Histórico de Pedidos</h5>
                    <?php if (empty($pedidos)): ?>
                        <div class="alert alert-info">
                            Este usuário ainda não realizou nenhum pedido.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nº do Pedido</th>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Itens</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                            <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                            <td><?php echo $pedido['total_itens']; ?> itens</td>
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
                                            <td>
                                                <a href="pedido-detalhes.php?id=<?php echo $pedido['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
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
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 