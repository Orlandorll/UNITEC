<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Verificar se a coluna status existe, se não, criar
try {
    $check_column = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'status'");
    if ($check_column->rowCount() == 0) {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'ativo'");
    }
} catch (PDOException $e) {
    $erro = "Erro ao verificar/criar coluna status: " . $e->getMessage();
}

$mensagem = '';
$erro = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'desativar':
                $usuario_id = (int)$_POST['usuario_id'];
                // Verificar se não é um administrador
                $check_sql = "SELECT tipo FROM usuarios WHERE id = :id";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bindParam(':id', $usuario_id);
                $check_stmt->execute();
                $usuario = $check_stmt->fetch();
                
                if ($usuario['tipo'] !== 'admin') {
                    $sql = "UPDATE usuarios SET status = 'inativo' WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $usuario_id);
                    if ($stmt->execute()) {
                        $mensagem = "Usuário desativado com sucesso!";
                    } else {
                        $erro = "Erro ao desativar usuário.";
                    }
                } else {
                    $erro = "Não é possível desativar um administrador!";
                }
                break;

            case 'ativar':
                $usuario_id = (int)$_POST['usuario_id'];
                $sql = "UPDATE usuarios SET status = 'ativo' WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $usuario_id);
                if ($stmt->execute()) {
                    $mensagem = "Usuário ativado com sucesso!";
                } else {
                    $erro = "Erro ao ativar usuário.";
                }
                break;
        }
    }
}

// Buscar todos os usuários
$sql = "SELECT id, nome, email, tipo, status, data_criacao FROM usuarios ORDER BY data_criacao DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll();

// Debug - Mostrar status dos usuários
echo "<!-- Debug: Status dos usuários -->\n";
foreach ($usuarios as $usuario) {
    echo "<!-- Usuário ID: " . $usuario['id'] . " - Status: " . $usuario['status'] . " -->\n";
}
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
        .user-card {
            border: 1px solid #d2d2d7;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .user-header {
            background: #f5f5f7;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-name {
            font-weight: 600;
            color: #1d1d1f;
        }
        .user-email {
            color: #6e6e73;
        }
        .user-body {
            padding: 20px;
        }
        .user-info {
            margin-bottom: 10px;
        }
        .user-info-label {
            font-weight: 500;
            color: #6e6e73;
        }
        .user-info-value {
            color: #1d1d1f;
        }
        .user-actions {
            display: flex;
            gap: 10px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .status-ativo { background: #d4edda; color: #155724; }
        .status-inativo { background: #f8d7da; color: #721c24; }
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

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Data de Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo ucfirst($usuario['tipo']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($usuario['status']); ?>">
                                            <?php echo ucfirst($usuario['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_criacao'])); ?></td>
                                    <td>
                                        <?php if ($usuario['tipo'] !== 'admin'): ?>
                                            <?php if (strtolower($usuario['status']) === 'ativo'): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja desativar este usuário?');">
                                                    <input type="hidden" name="acao" value="desativar">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-ban"></i> Desativar
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja ativar este usuário?');">
                                                    <input type="hidden" name="acao" value="ativar">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Ativar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 