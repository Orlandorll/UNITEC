<?php
session_start();
require_once "config/database.php";

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Debug - Log dos dados recebidos
    error_log("Login - Tentativa de login para email: " . $email);

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $usuario = $stmt->fetch();
            error_log("Login - Usuário encontrado: " . print_r($usuario, true));
            
            if (password_verify($senha, $usuario['senha'])) {
                error_log("Login - Senha verificada com sucesso");
                
                // Limpar sessão anterior
                session_unset();
                session_destroy();
                session_start();
                
                // Definir novas variáveis de sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = $usuario['tipo']; // Corrigido: usando usuario_tipo em vez de tipo
                $_SESSION['usuario_email'] = $usuario['email'];

                // Debug - Log das variáveis de sessão
                error_log("Login - Sessão após login: " . print_r($_SESSION, true));
                error_log("Login - Tipo de usuário: " . $usuario['tipo']);

                // Redirecionar para a página apropriada
                if (strtolower(trim($usuario['tipo'])) === 'admin') {
                    error_log("Login - Redirecionando para admin/index.php");
                    header("Location: admin/index.php");
                    exit();
                } else {
                    error_log("Login - Redirecionando para página apropriada");
                    if (isset($_GET['redirect'])) {
                        $redirect = urldecode($_GET['redirect']);
                        header("Location: " . $redirect);
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                }
            } else {
                error_log("Login - Senha incorreta");
                $erro = "Senha incorreta.";
            }
        } else {
            error_log("Login - Email não encontrado");
            $erro = "Email não encontrado.";
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
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: white;
            padding: 30px 30px 20px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
            font-weight: 500;
        }
        
        .login-header p {
            color: #666;
            margin: 10px 0 0;
            font-size: 14px;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating input {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.75rem;
            height: auto;
            font-size: 14px;
        }
        
        .form-floating input:focus {
            border-color: #007bff;
            box-shadow: none;
        }
        
        .form-floating label {
            padding: 0.75rem;
            font-size: 14px;
            color: #666;
        }
        
        .btn-login {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            background: #007bff;
            border: none;
            color: white;
            transition: background-color 0.2s ease;
        }
        
        .btn-login:hover {
            background: #0056b3;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .login-footer p {
            margin: 0 0 10px;
            color: #666;
            font-size: 14px;
        }
        
        .login-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            padding: 12px 15px;
        }
        
        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="login-section">
        <div class="login-card">
            <div class="login-header">
                <h1>Bem-vindo de volta!</h1>
                <p class="mb-0">Entre com sua conta Unitec</p>
            </div>
            <div class="login-body">
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                        <label for="senha">Senha</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Entrar
                        </button>
                    </div>
                </form>
            </div>
            <div class="login-footer">
                <p class="mb-2">Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
                <a href="recuperar-senha.php">Esqueceu sua senha?</a>
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