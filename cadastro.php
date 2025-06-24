<?php
session_start();
require_once "config/database.php";

$erro = '';
$sucesso = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $telefone = trim($_POST['telefone']);
    $nif = trim($_POST['nif']);
    $tipo_usuario = $_POST['tipo_usuario'];

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Por favor, insira um endereço de Gmail válido.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($tipo_usuario === 'empresa' && empty($nif)) {
        $erro = "O NIF é obrigatório para empresas.";
    } elseif (!empty($telefone)) {
        // Validar telefone angolano
        $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($telefone_limpo) !== 9) {
            $erro = "O telefone deve ter exatamente 9 dígitos (formato: 9XXXXXXXX).";
        } elseif (!preg_match('/^[9][0-9]{8}$/', $telefone_limpo)) {
            $erro = "O telefone deve começar com 9 e ter 9 dígitos (formato: 9XXXXXXXX).";
        } else {
            // Formatar telefone com +244
            $telefone = '+244' . $telefone_limpo;
        }
    }
    
    // Se não há erros até agora, verificar email duplicado e inserir usuário
    if (empty($erro)) {
        // Verificar se o email já existe
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $erro = "❌ Este Gmail já está registrado no sistema. Por favor, use outro Gmail ou faça login.";
        } else {
            // Inserir novo usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $tipo = 'cliente'; // Tipo padrão para novos usuários

            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, nif, tipo_usuario, tipo) 
                    VALUES (:nome, :email, :senha, :telefone, :nif, :tipo_usuario, :tipo)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':nif', $nif);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            $stmt->bindParam(':tipo', $tipo);

            if ($stmt->execute()) {
                $sucesso = "✅ Cadastro realizado com sucesso! Sua conta foi criada e você já pode fazer login.";
            } else {
                $erro = "❌ Erro ao realizar cadastro. Por favor, tente novamente.";
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
    <title>Cadastro - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-section {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
        }
        .register-header {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h1 {
            font-size: 1.8rem;
            margin: 0;
        }
        .register-body {
            padding: 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-floating input {
            border-radius: 8px;
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .register-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .register-footer a:hover {
            color: var(--secondary-color);
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .telefone-input {
            position: relative;
        }
        .telefone-prefix {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 500;
            z-index: 10;
            line-height: 1;
        }
        .telefone-input input {
            padding-left: 50px !important;
        }
        .telefone-input .form-floating > label {
            padding-left: 50px;
        }
        .telefone-help {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-section">
        <div class="register-card">
            <div class="register-header">
                <h1>Criar Conta</h1>
                <p class="mb-0">Junte-se à Unitec</p>
            </div>
            <div class="register-body">
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

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" required>
                                <label for="nome">Nome completo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                                <label for="email">Email</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                                <label for="senha">Senha</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar senha" required>
                                <label for="confirmar_senha">Confirmar senha</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating telefone-input">
                                <span class="telefone-prefix">+244</span>
                                <input type="tel" class="form-control" id="telefone" name="telefone" 
                                       placeholder="9XXXXXXXX" maxlength="9" pattern="[0-9]{9}" 
                                       title="Digite apenas 9 dígitos numéricos (formato angolano)">
                                <label for="telefone">Telefone</label>
                                <div class="telefone-help">
                                    <i class="fas fa-info-circle"></i> Digite apenas os 9 dígitos (ex: 912345678)
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                    <option value="pessoa">Pessoa Física</option>
                                    <option value="empresa">Empresa</option>
                                </select>
                                <label for="tipo_usuario">Tipo de Conta</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nif" name="nif" placeholder="NIF">
                                <label for="nif">NIF <span class="text-muted">(Obrigatório para empresas)</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-register">
                            <i class="fas fa-user-plus me-2"></i>Criar Conta
                        </button>
                    </div>
                </form>
            </div>
            <div class="register-footer">
                <p class="mb-0">Já tem uma conta? <a href="login.php">Faça login</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipoUsuario = document.getElementById('tipo_usuario');
            const nifInput = document.getElementById('nif');
            const telefoneInput = document.getElementById('telefone');

            // Validação do tipo de usuário
            tipoUsuario.addEventListener('change', function() {
                if (this.value === 'empresa') {
                    nifInput.required = true;
                } else {
                    nifInput.required = false;
                }
            });

            // Validação do telefone angolano
            telefoneInput.addEventListener('input', function(e) {
                // Remover todos os caracteres não numéricos
                let value = this.value.replace(/[^0-9]/g, '');
                
                // Limitar a exatamente 9 dígitos
                if (value.length > 9) {
                    value = value.substring(0, 9);
                }
                
                // Atualizar o valor do campo
                this.value = value;
                
                // Validar formato angolano (deve começar com 9 e ter exatamente 9 dígitos)
                if (value.length > 0 && value.charAt(0) !== '9') {
                    this.setCustomValidity('O telefone angolano deve começar com 9');
                } else if (value.length > 0 && value.length !== 9) {
                    this.setCustomValidity('O telefone deve ter exatamente 9 dígitos (formato: 9XXXXXXXX)');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Prevenir colagem de caracteres inválidos
            telefoneInput.addEventListener('paste', function(e) {
                e.preventDefault();
                let pastedText = (e.clipboardData || window.clipboardData).getData('text');
                let numericOnly = pastedText.replace(/[^0-9]/g, '');
                
                if (numericOnly.length > 9) {
                    numericOnly = numericOnly.substring(0, 9);
                }
                
                this.value = numericOnly;
            });

            // Prevenir teclas não numéricas
            telefoneInput.addEventListener('keypress', function(e) {
                const charCode = e.which ? e.which : e.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    e.preventDefault();
                }
            });

            // Validação de email em tempo real
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                if (email && !isValidEmail(email)) {
                    this.setCustomValidity('este email já está registrado no sistema, por favor use outro email ou faça login.');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });

            // Função para validar email
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Validação do formulário antes do envio
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                if (email && !isValidEmail(email)) {
                    e.preventDefault();
                    alert('Por favor, insira um endereço de Gmail válido.');
                    emailInput.focus();
                    return false;
                }
            });
        });
    </script>
</body>
</html> 