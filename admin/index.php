<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Buscar estatísticas
$total_mensagens = $conn->query("SELECT COUNT(*) FROM mensagens_contato")->fetchColumn();
$total_sobre = $conn->query("SELECT COUNT(*) FROM sobre_conteudo")->fetchColumn();
$total_hero = $conn->query("SELECT COUNT(*) FROM hero_images")->fetchColumn();
$total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_produtos = $conn->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$total_categorias = $conn->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
$total_pedidos = $conn->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();

// Buscar últimas mensagens
$stmt = $conn->query("SELECT * FROM mensagens_contato ORDER BY data_envio DESC LIMIT 5");
$ultimas_mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar últimos pedidos
$stmt = $conn->query("SELECT p.*, u.nome as cliente_nome, 
                     COALESCE((SELECT SUM(ip.quantidade * ip.preco) 
                      FROM itens_pedido ip 
                      WHERE ip.pedido_id = p.id), 0) as total 
                     FROM pedidos p 
                     LEFT JOIN usuarios u ON p.usuario_id = u.id 
                     ORDER BY p.data_criacao DESC LIMIT 5");
$ultimos_pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="produtos.php">
                                <i class="fas fa-box"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categorias.php">
                                <i class="fas fa-tags"></i> Categorias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pedidos.php">
                                <i class="fas fa-shopping-cart"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mensagens.php">
                                <i class="fas fa-envelope"></i> Mensagens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sobre.php">
                                <i class="fas fa-info-circle"></i> Sobre
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gerenciar-hero.php">
                                <i class="fas fa-images"></i> Hero Section
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Conteúdo Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../index.php" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Ver Site
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Usuários</h5>
                                <p class="card-text display-4"><?php echo $total_usuarios; ?></p>
                                <a href="usuarios.php" class="btn btn-primary">Gerenciar Usuários</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Produtos</h5>
                                <p class="card-text display-4"><?php echo $total_produtos; ?></p>
                                <a href="produtos.php" class="btn btn-primary">Gerenciar Produtos</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Categorias</h5>
                                <p class="card-text display-4"><?php echo $total_categorias; ?></p>
                                <a href="categorias.php" class="btn btn-primary">Gerenciar Categorias</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Pedidos</h5>
                                <p class="card-text display-4"><?php echo $total_pedidos; ?></p>
                                <a href="pedidos.php" class="btn btn-primary">Ver Pedidos</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Mensagens</h5>
                                <p class="card-text display-4"><?php echo $total_mensagens; ?></p>
                                <a href="mensagens.php" class="btn btn-primary">Ver Mensagens</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Conteúdo Sobre</h5>
                                <p class="card-text display-4"><?php echo $total_sobre; ?></p>
                                <a href="sobre.php" class="btn btn-primary">Gerenciar Sobre</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Hero Section</h5>
                                <p class="card-text display-4"><?php echo $total_hero; ?></p>
                                <a href="hero.php" class="btn btn-primary">Gerenciar Hero</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimas Mensagens -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Últimas Mensagens</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Assunto</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_mensagens as $mensagem): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mensagem['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($mensagem['email']); ?></td>
                                        <td><?php echo htmlspecialchars($mensagem['assunto']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($mensagem['data_envio'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $mensagem['status'] == 'não lida' ? 'danger' : ($mensagem['status'] == 'lida' ? 'warning' : 'success'); ?>">
                                                <?php echo ucfirst($mensagem['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="ver_mensagem.php?id=<?php echo $mensagem['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Últimos Pedidos -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Últimos Pedidos</h5>
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
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_pedidos as $pedido): ?>
                                    <tr>
                                        <td>#<?php echo $pedido['id']; ?></td>
                                        <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                                        <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $pedido['status'] == 'pendente' ? 'warning' : 
                                                    ($pedido['status'] == 'aprovado' ? 'success' : 
                                                    ($pedido['status'] == 'cancelado' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($pedido['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?></td>
                                        <td>
                                            <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 