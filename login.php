<?php
session_start();
require_once "config/database.php";

// Se já estiver logado, redirecionar para a página apropriada
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['tipo'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Buscar usuário pelo email
        $sql = "SELECT * FROM usuarios WHERE email = :email AND ativo = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['tipo'] = $usuario['tipo'];

            // Redirecionar para a página apropriada
            if ($usuario['tipo'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $erro = "Email ou senha incorretos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .login-title {
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
        .btn-login {
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-title">Login</h1>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label required-field">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label required-field">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>

            <div class="register-link">
                <p class="mb-0">
                    Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
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
    </script>
</body>
</html> 