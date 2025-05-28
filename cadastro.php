<?php
session_start();
require_once "config/database.php";

// Se já estiver logado, redirecionar para a página inicial
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $telefone = trim($_POST['telefone']);
    $nif = trim($_POST['nif']);
    $tipo_usuario = $_POST['tipo_usuario'];

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Verificar se o email já existe
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $erro = "Este email já está cadastrado.";
        } else {
            // Inserir novo usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, nif, tipo_usuario, tipo, ativo, data_cadastro) 
                    VALUES (:nome, :email, :senha, :telefone, :nif, :tipo_usuario, 'cliente', 1, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':nif', $nif);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);

            if ($stmt->execute()) {
                $mensagem = "Cadastro realizado com sucesso! Você já pode fazer login.";
            } else {
                $erro = "Erro ao realizar cadastro. Por favor, tente novamente.";
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
    <title>Cadastro - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }
        .register-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .register-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
            text-align: center;
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
        .btn-register {
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <h1 class="register-title">Cadastro</h1>

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
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label required-field">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="senha" class="form-label required-field">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="confirmar_senha" class="form-label required-field">Confirmar Senha</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nif" class="form-label">NIF</label>
                        <input type="text" class="form-control" id="nif" name="nif">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tipo_usuario" class="form-label required-field">Tipo de Usuário</label>
                    <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                        <option value="pessoa">Pessoa Física</option>
                        <option value="empresa">Pessoa Jurídica</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-register">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </button>
            </form>

            <div class="login-link">
                <p class="mb-0">
                    Já tem uma conta? <a href="login.php">Faça login</a>
                </p>
            </div>
        </div>
    </div>

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