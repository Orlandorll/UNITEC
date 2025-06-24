<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensagem = '';
$erro = '';

// Verificar se a coluna status existe, se não, criar
try {
    $check_column = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'status'");
    if ($check_column->rowCount() == 0) {
        // Remover a coluna se existir com configuração incorreta
        $conn->exec("ALTER TABLE usuarios DROP COLUMN IF EXISTS status");
        // Criar a coluna com a configuração correta
        $conn->exec("ALTER TABLE usuarios ADD COLUMN status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo'");
        // Atualizar registros existentes para 'ativo'
        $conn->exec("UPDATE usuarios SET status = 'ativo' WHERE status IS NULL");
    } else {
        // Verificar se a coluna está configurada corretamente
        $column_info = $check_column->fetch(PDO::FETCH_ASSOC);
        if ($column_info['Type'] !== "enum('ativo','inativo')") {
            // Recriar a coluna com a configuração correta
            $conn->exec("ALTER TABLE usuarios MODIFY COLUMN status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo'");
        }
    }
} catch (PDOException $e) {
    $erro = "Erro ao verificar/criar coluna status: " . $e->getMessage();
}

// Processar exclusão de administrador
if (isset($_POST['excluir_admin']) && isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];
    
    // Impedir auto-exclusão
    if ($admin_id == $_SESSION['usuario_id']) {
        $erro = "Você não pode excluir sua própria conta de administrador.";
    } else {
        $sql = "DELETE FROM usuarios WHERE id = :id AND tipo = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $admin_id);
        
        if ($stmt->execute()) {
            $mensagem = "Administrador excluído com sucesso!";
        } else {
            $erro = "Erro ao excluir administrador.";
        }
    }
}

// Processar alteração de status
if (isset($_POST['alterar_status']) && isset($_POST['admin_id']) && isset($_POST['status'])) {
    $admin_id = $_POST['admin_id'];
    $novo_status = $_POST['status'];
    
    // Impedir auto-desativação
    if ($admin_id == $_SESSION['usuario_id']) {
        $erro = "Você não pode alterar o status da sua própria conta.";
    } else {
        try {
            // Primeiro, verificar o status atual
            $check_sql = "SELECT status FROM usuarios WHERE id = :id AND tipo = 'admin'";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bindParam(':id', $admin_id);
            $check_stmt->execute();
            $current_status = $check_stmt->fetchColumn();

            // Atualizar o status
            $sql = "UPDATE usuarios SET status = :status WHERE id = :id AND tipo = 'admin'";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':status', $novo_status);
            $stmt->bindParam(':id', $admin_id);
            
            if ($stmt->execute()) {
                // Verificar se realmente foi atualizado
                $verify_sql = "SELECT status FROM usuarios WHERE id = :id AND tipo = 'admin'";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bindParam(':id', $admin_id);
                $verify_stmt->execute();
                $updated_status = $verify_stmt->fetchColumn();

                if ($updated_status === $novo_status) {
                    $mensagem = "Status do administrador atualizado com sucesso!";
                    // Forçar recarregamento da página
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                    exit;
                } else {
                    $erro = "Erro: Status não foi atualizado corretamente.";
                }
            } else {
                $erro = "Erro ao atualizar status do administrador.";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar status: " . $e->getMessage();
        }
    }
}

// Exibir mensagem de sucesso se existir
if (isset($_GET['success'])) {
    $mensagem = "Status do administrador atualizado com sucesso!";
}

// Buscar todos os administradores com status atualizado
$sql = "SELECT id, nome, email, status, data_criacao FROM usuarios WHERE tipo = 'admin' ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Administradores - Painel Administrativo</title>
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                    <h1 class="admin-title">Gerenciar Administradores</h1>
                        <div class="text-muted">
                            <i class="fas fa-user-circle me-1"></i>
                            Logado como: <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                        </div>
                    </div>
                    <a href="adicionar-usuario.php?tipo=admin" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Novo Administrador
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
                                <th>Status</th>
                                <th>Data de Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($administradores as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['nome']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <?php 
                                    $status = strtolower($admin['status']);
                                    $status_texto = $status === 'ativo' ? 'Ativo' : 'Inativo';
                                    $status_class = $status === 'ativo' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo $status_texto; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($admin['data_criacao'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar-usuario.php?id=<?php echo $admin['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($admin['id'] != $_SESSION['usuario_id']): ?>
                                            <button type="button" 
                                                    class="btn btn-sm <?php echo $admin['status'] === 'ativo' ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                                    title="<?php echo $admin['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?> Administrador"
                                                onclick="alterarStatus(<?php echo $admin['id']; ?>, '<?php echo $admin['status'] === 'ativo' ? 'inativo' : 'ativo'; ?>')">
                                                <i class="fas <?php echo $admin['status'] === 'ativo' ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    title="Excluir Administrador"
                                                onclick="confirmarExclusao(<?php echo $admin['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php else: ?>
                                            <span class="badge bg-info ms-2">
                                                <i class="fas fa-user me-1"></i>Você
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este administrador?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="admin_id" id="admin_id_excluir">
                        <input type="hidden" name="excluir_admin" value="1">
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(adminId) {
            if (confirm('Tem certeza que deseja excluir este administrador?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'admin_id';
                input.value = adminId;
                
                var inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'excluir_admin';
                inputAction.value = '1';
                
                form.appendChild(input);
                form.appendChild(inputAction);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function alterarStatus(adminId, novoStatus) {
            const acao = novoStatus === 'ativo' ? 'ativar' : 'desativar';
            if (confirm(`Deseja ${acao} este administrador?`)) {
                // Criar formulário
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;
                
                // Adicionar campos
                const campos = {
                    'admin_id': adminId,
                    'status': novoStatus,
                    'alterar_status': '1'
                };
                
                // Criar e adicionar inputs
                for (const [name, value] of Object.entries(campos)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                }
                
                // Adicionar formulário ao documento e enviar
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>