<?php
session_start();
require_once "config/database.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$erro = '';
$sucesso = '';

// Buscar informações do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->fetch();

// Processar atualização do perfil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['atualizar_perfil'])) {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $nif = trim($_POST['nif']);
        $tipo_usuario = $_POST['tipo_usuario'];

        // Verificar se o email já existe para outro usuário
        $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $_SESSION['usuario_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $erro = "Este email já está em uso por outro usuário.";
        } elseif ($tipo_usuario === 'empresa' && empty($nif)) {
            $erro = "O NIF é obrigatório para empresas.";
        } else {
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone, nif = :nif, tipo_usuario = :tipo_usuario WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':nif', $nif);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            $stmt->bindParam(':id', $_SESSION['usuario_id']);

            if ($stmt->execute()) {
                $sucesso = "Perfil atualizado com sucesso!";
                $_SESSION['usuario_nome'] = $nome;
            } else {
                $erro = "Erro ao atualizar perfil.";
            }
        }
    } elseif (isset($_POST['alterar_senha'])) {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        if (!password_verify($senha_atual, $usuario['senha'])) {
            $erro = "Senha atual incorreta.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $erro = "As novas senhas não coincidem.";
        } elseif (strlen($nova_senha) < 6) {
            $erro = "A nova senha deve ter pelo menos 6 caracteres.";
        } else {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':id', $_SESSION['usuario_id']);

            if ($stmt->execute()) {
                $sucesso = "Senha alterada com sucesso!";
            } else {
                $erro = "Erro ao alterar senha.";
            }
        }
    }
}

// Buscar pedidos do usuário
$sql = "SELECT p.*, COUNT(ip.id) as total_itens 
        FROM pedidos p 
        LEFT JOIN itens_pedido ip ON p.id = ip.pedido_id 
        WHERE p.usuario_id = :usuario_id 
        GROUP BY p.id 
        ORDER BY p.data_criacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-section {
            padding: 40px 0;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .profile-header {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
        }
        .profile-body {
            padding: 30px;
        }
        .nav-pills .nav-link {
            color: var(--primary-color);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-floating input {
            border-radius: 8px;
        }
        .btn-profile {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .order-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-card">
                        <div class="profile-header text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-4x"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($usuario['nome']); ?></h4>
                            <p class="mb-0"><?php echo htmlspecialchars($usuario['email']); ?></p>
                        </div>
                        <div class="profile-body">
                            <div class="nav flex-column nav-pills" role="tablist">
                                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#perfil" type="button">
                                    <i class="fas fa-user me-2"></i>Meu Perfil
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pedidos" type="button">
                                    <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#senha" type="button">
                                    <i class="fas fa-lock me-2"></i>Alterar Senha
                                </button>
                                <a href="logout.php" class="nav-link text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Sair
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="profile-card">
                        <div class="profile-body">
                            <?php if (!empty($erro)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $erro; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($sucesso)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo $sucesso; ?>
                                </div>
                            <?php endif; ?>

                            <div class="tab-content">
                                <!-- Perfil -->
                                <div class="tab-pane fade show active" id="perfil">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="mb-0">Informações Pessoais</h4>
                                        <button class="btn btn-outline-primary" id="btnEditarPerfil">
                                            <i class="fas fa-edit me-2"></i>Editar Informações
                                        </button>
                                    </div>

                                    <!-- Visualização das Informações -->
                                    <div id="infoPessoal" class="mb-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted">Nome Completo</label>
                                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($usuario['nome']); ?></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted">Email</label>
                                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($usuario['email']); ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted">Telefone</label>
                                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($usuario['telefone'] ?: 'Não informado'); ?></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted">Tipo de Conta</label>
                                                <p class="mb-0 fw-bold"><?php echo $usuario['tipo_usuario'] === 'empresa' ? 'Empresa' : 'Pessoa Física'; ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted">NIF</label>
                                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($usuario['nif'] ?: 'Não informado'); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Formulário de Edição -->
                                    <form method="POST" action="" id="formEditarPerfil" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="nome" name="nome" 
                                                           value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                                                    <label for="nome">Nome completo</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="email" class="form-control" id="email" name="email" 
                                                           value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                                    <label for="email">Email</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                                                           value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                                                    <label for="telefone">Telefone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                                        <option value="pessoa" <?php echo $usuario['tipo_usuario'] === 'pessoa' ? 'selected' : ''; ?>>Pessoa Física</option>
                                                        <option value="empresa" <?php echo $usuario['tipo_usuario'] === 'empresa' ? 'selected' : ''; ?>>Empresa</option>
                                                    </select>
                                                    <label for="tipo_usuario">Tipo de Conta</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="nif" name="nif" 
                                                           value="<?php echo htmlspecialchars($usuario['nif']); ?>">
                                                    <label for="nif">NIF <span class="text-muted">(Obrigatório para empresas)</span></label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" name="atualizar_perfil" class="btn btn-primary btn-profile">
                                                <i class="fas fa-save me-2"></i>Salvar Alterações
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-profile" id="btnCancelarEdicao">
                                                <i class="fas fa-times me-2"></i>Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Pedidos -->
                                <div class="tab-pane fade" id="pedidos">
                                    <h4 class="mb-4">Meus Pedidos</h4>
                                    <?php if (empty($pedidos)): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Você ainda não fez nenhum pedido.</p>
                                            <a href="produtos.php" class="btn btn-primary">Ver Produtos</a>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($pedidos as $pedido): ?>
                                            <div class="order-card">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h6 class="mb-1">Pedido #<?php echo $pedido['id']; ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                                                        </small>
                                                    </div>
                                                    <span class="status-badge bg-<?php 
                                                        echo $pedido['status'] == 'pendente' ? 'warning' : 
                                                            ($pedido['status'] == 'aprovado' ? 'success' : 
                                                            ($pedido['status'] == 'cancelado' ? 'danger' : 'info')); 
                                                    ?>">
                                                        <?php echo ucfirst($pedido['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="mb-0">
                                                            <strong>Total:</strong> R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                                                        </p>
                                                        <small class="text-muted">
                                                            <?php echo $pedido['total_itens']; ?> item(ns)
                                                        </small>
                                                    </div>
                                                    <a href="detalhes-pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                        Ver Detalhes
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Alterar Senha -->
                                <div class="tab-pane fade" id="senha">
                                    <h4 class="mb-4">Alterar Senha</h4>
                                    <form method="POST" action="">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                            <label for="senha_atual">Senha Atual</label>
                                        </div>
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                                            <label for="nova_senha">Nova Senha</label>
                                        </div>
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                                        </div>
                                        <div class="d-grid gap-2 mt-4">
                                            <button type="submit" name="alterar_senha" class="btn btn-primary btn-profile">
                                                <i class="fas fa-key me-2"></i>Alterar Senha
                                            </button>
                                        </div>
                                    </form>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnEditarPerfil = document.getElementById('btnEditarPerfil');
            const btnCancelarEdicao = document.getElementById('btnCancelarEdicao');
            const infoPessoal = document.getElementById('infoPessoal');
            const formEditarPerfil = document.getElementById('formEditarPerfil');
            const tipoUsuario = document.getElementById('tipo_usuario');
            const nifInput = document.getElementById('nif');

            btnEditarPerfil.addEventListener('click', function() {
                infoPessoal.style.display = 'none';
                formEditarPerfil.style.display = 'block';
                btnEditarPerfil.style.display = 'none';
            });

            btnCancelarEdicao.addEventListener('click', function() {
                infoPessoal.style.display = 'block';
                formEditarPerfil.style.display = 'none';
                btnEditarPerfil.style.display = 'block';
            });

            tipoUsuario.addEventListener('change', function() {
                if (this.value === 'empresa') {
                    nifInput.required = true;
                } else {
                    nifInput.required = false;
                }
            });
        });
    </script>
</body>
</html> 