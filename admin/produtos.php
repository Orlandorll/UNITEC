<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Processar ações
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'desativar':
                $produto_id = (int)$_POST['produto_id'];
                $sql = "UPDATE produtos SET status = 0 WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $produto_id);
                $stmt->execute();
                $mensagem = "Produto desativado com sucesso!";
                break;

            case 'ativar':
                $produto_id = (int)$_POST['produto_id'];
                $sql = "UPDATE produtos SET status = 1 WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $produto_id);
                $stmt->execute();
                $mensagem = "Produto ativado com sucesso!";
                break;
        }
    }
}

// Buscar todos os produtos com suas categorias
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.data_criacao DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Painel Administrativo</title>
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
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .status-ativo {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-inativo {
            background: #ffebee;
            color: #c62828;
        }
        .preco {
            font-weight: 600;
            color: #1d1d1f;
        }
        .preco-promocional {
            color: #c62828;
            text-decoration: line-through;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Gerenciar Produtos</h1>
                    <a href="adicionar-produto.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Produto
                    </a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <?php if (empty($produtos)): ?>
                    <div class="alert alert-info">
                        Nenhum produto cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Estoque</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($produto['categoria_nome'] ?? 'Sem categoria'); ?></td>
                                        <td>
                                            <?php if ($produto['preco_promocional']): ?>
                                                <span class="preco-promocional">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                <span class="preco">R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $produto['estoque']; ?></td>
                                        <td>
                                            <span class="badge status-badge status-<?php echo $produto['status'] ? 'ativo' : 'inativo'; ?>">
                                                <?php echo $produto['status'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="editar-produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($produto['status']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="acao" value="desativar">
                                                        <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja desativar este produto?')">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="acao" value="ativar">
                                                        <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
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

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 