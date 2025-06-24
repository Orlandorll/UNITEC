<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Processar exclusão de categoria
if (isset($_POST['excluir']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    // Verificar se existem produtos nesta categoria
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $total_produtos = $stmt->fetch()['total'];

    if ($total_produtos > 0) {
        $erro = "Não é possível excluir esta categoria pois existem produtos vinculados a ela.";
    } else {
        $sql = "DELETE FROM categorias WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        header("Location: categorias.php?msg=excluido");
        exit;
    }
}

// Processar adição/edição de categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = trim($_POST['nome']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if (empty($nome)) {
        $erro = "O nome da categoria é obrigatório.";
    } else {
        try {
            if ($id) {
                // Atualizar categoria existente
                $sql = "UPDATE categorias SET nome = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nome, $id]);
                header("Location: categorias.php?msg=atualizado");
            } else {
                // Adicionar nova categoria
                $sql = "INSERT INTO categorias (nome) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nome]);
                header("Location: categorias.php?msg=adicionado");
            }
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao salvar categoria: " . $e->getMessage();
        }
    }
}

// Buscar categorias
$sql = "SELECT c.*, COUNT(p.id) as total_produtos 
        FROM categorias c 
        LEFT JOIN produtos p ON c.id = p.categoria_id 
        GROUP BY c.id 
        ORDER BY c.nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - UNITEC</title>
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Gerenciar Categorias</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                        <i class="fas fa-plus me-1"></i>
                        Nova Categoria
                    </button>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger">
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        $msg = $_GET['msg'];
                        if ($msg === 'excluido') {
                            echo 'Categoria excluída com sucesso!';
                        } elseif ($msg === 'atualizado') {
                            echo 'Categoria atualizada com sucesso!';
                        } elseif ($msg === 'adicionado') {
                            echo 'Categoria adicionada com sucesso!';
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Total de Produtos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        Nenhuma categoria encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categorias as $categoria): ?>
                                    <tr>
                                        <td><?php echo $categoria['id']; ?></td>
                                        <td><?php echo htmlspecialchars($categoria['nome']); ?></td>
                                        <td><?php echo $categoria['total_produtos']; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary btn-action"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCategoria"
                                                        data-id="<?php echo $categoria['id']; ?>"
                                                        data-nome="<?php echo htmlspecialchars($categoria['nome']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($categoria['total_produtos'] == 0): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger btn-action"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalExcluir<?php echo $categoria['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Modal de Confirmação de Exclusão -->
                                            <div class="modal fade" id="modalExcluir<?php echo $categoria['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirmar Exclusão</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Tem certeza que deseja excluir a categoria 
                                                            <strong><?php echo htmlspecialchars($categoria['nome']); ?></strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
                                                                <button type="submit" name="excluir" class="btn btn-danger">Excluir</button>
                                                            </form>
                                                        </div>
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
            </div>
        </div>
    </div>

    <!-- Modal de Categoria -->
    <div class="modal fade" id="modalCategoria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Categoria</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="categoria_id">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome da Categoria</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Editar categoria
        const modalCategoria = document.getElementById('modalCategoria');
        modalCategoria.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');
            
            const modalTitle = this.querySelector('.modal-title');
            const idInput = this.querySelector('#categoria_id');
            const nomeInput = this.querySelector('#nome');
            
            if (id) {
                modalTitle.textContent = 'Editar Categoria';
                idInput.value = id;
                nomeInput.value = nome;
            } else {
                modalTitle.textContent = 'Nova Categoria';
                idInput.value = '';
                nomeInput.value = '';
            }
        });
    </script>
</body>
</html> 