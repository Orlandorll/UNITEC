<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
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

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $nif = trim($_POST['nif']);
    $tipo = $_POST['tipo'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // Validações
    if (empty($nome) || empty($email)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (!empty($senha) && $senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Verificar se o email já existe (exceto para o próprio usuário)
        $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $usuario_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $erro = "Este email já está cadastrado.";
        } else {
            // Atualizar usuário
            if (!empty($senha)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, 
                        telefone = :telefone, nif = :nif, tipo = :tipo, tipo_usuario = :tipo_usuario 
                        WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':senha', $senha_hash);
            } else {
                $sql = "UPDATE usuarios SET nome = :nome, email = :email, 
                        telefone = :telefone, nif = :nif, tipo = :tipo, tipo_usuario = :tipo_usuario 
                        WHERE id = :id";
                $stmt = $conn->prepare($sql);
            }

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':nif', $nif);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            $stmt->bindParam(':id', $usuario_id);

            if ($stmt->execute()) {
                $mensagem = "Usuário atualizado com sucesso!";
                // Atualizar dados do usuário na sessão
                $usuario['nome'] = $nome;
                $usuario['email'] = $email;
                $usuario['telefone'] = $telefone;
                $usuario['nif'] = $nif;
                $usuario['tipo'] = $tipo;
                $usuario['tipo_usuario'] = $tipo_usuario;
            } else {
                $erro = "Erro ao atualizar usuário. Por favor, tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Painel Administrativo</title>
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
        .form-label {
            font-weight: 500;
            color: #1d1d1f;
        }
        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Editar Usuário</h1>
                    <a href="usuarios.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nome" class="form-label required-field">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label required-field">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="senha" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha">
                            <small class="text-muted">Deixe em branco para manter a senha atual</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" 
                                   value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nif" class="form-label">NIF</label>
                            <input type="text" class="form-control" id="nif" name="nif" 
                                   value="<?php echo htmlspecialchars($usuario['nif'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label required-field">Tipo de Acesso</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="cliente" <?php echo $usuario['tipo'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                <option value="admin" <?php echo $usuario['tipo'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipo_usuario" class="form-label required-field">Tipo de Usuário</label>
                            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                <option value="pessoa" <?php echo $usuario['tipo_usuario'] === 'pessoa' ? 'selected' : ''; ?>>Pessoa Física</option>
                                <option value="empresa" <?php echo $usuario['tipo_usuario'] === 'empresa' ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            if (value.length > 2) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
            }
            if (value.length > 9) {
                value = value.substring(0, 10) + '-' + value.substring(10);
            }
            e.target.value = value;
        });
    </script>
</body>
</html> 