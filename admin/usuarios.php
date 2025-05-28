<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Buscar todos os usuários
$sql = "SELECT * FROM usuarios ORDER BY data_cadastro DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Painel Administrativo</title>
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
        .table th {
            font-weight: 500;
            color: #1d1d1f;
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
        }
        .status-ativo { background-color: #28a745; color: #fff; }
        .status-inativo { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Gerenciar Usuários</h1>
                    <a href="adicionar-usuario.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Usuário
                    </a>
                </div>

                <?php if (empty($usuarios)): ?>
                    <div class="alert alert-info">
                        Nenhum usuário cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Data de Cadastro</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <?php
                                            $tipo_labels = [
                                                'admin' => 'Administrador',
                                                'cliente' => 'Cliente'
                                            ];
                                            echo $tipo_labels[$usuario['tipo']];
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                                        <td>
                                            <span class="badge status-badge status-<?php echo $usuario['ativo'] ? 'ativo' : 'inativo'; ?>">
                                                <?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <?php foreach ($usuarios as $usuario): ?>
                    <div class="user-card">
                        <div class="user-header">
                            <div>
                                <h3 class="user-name"><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                                <div class="user-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
                            </div>
                            <span class="status-badge <?php echo $usuario['status'] ? 'status-ativo' : 'status-inativo'; ?>">
                                <?php echo $usuario['status'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </div>

                        <div class="user-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="user-info">
                                        <span class="user-info-label">Tipo:</span>
                                        <span class="user-info-value">
                                            <?php echo $usuario['tipo'] === 'admin' ? 'Administrador' : 'Cliente'; ?>
                                        </span>
                                    </div>
                                    <div class="user-info">
                                        <span class="user-info-label">Telefone:</span>
                                        <span class="user-info-value"><?php echo htmlspecialchars($usuario['telefone'] ?? 'Não informado'); ?></span>
                                    </div>
                                    <div class="user-info">
                                        <span class="user-info-label">NIF:</span>
                                        <span class="user-info-value"><?php echo htmlspecialchars($usuario['nif'] ?? 'Não informado'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="user-info">
                                        <span class="user-info-label">Data de Cadastro:</span>
                                        <span class="user-info-value">
                                            <?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?>
                                        </span>
                                    </div>
                                    <div class="user-info">
                                        <span class="user-info-label">Última Atualização:</span>
                                        <span class="user-info-value">
                                            <?php echo date('d/m/Y H:i', strtotime($usuario['data_atualizacao'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="user-actions mt-3">
                                <a href="editar-usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php if ($usuario['status']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="acao" value="desativar">
                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Tem certeza que deseja desativar este usuário?')">
                                            <i class="fas fa-ban"></i> Desativar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="acao" value="ativar">
                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="btn btn-outline-success">
                                            <i class="fas fa-check"></i> Ativar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 