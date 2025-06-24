<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Definir ambiente (development ou production)
define('ENVIRONMENT', 'development');

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem_texto = trim($_POST['mensagem'] ?? '');

    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem_texto)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios.";
        $tipo_mensagem = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Por favor, insira um email válido.";
        $tipo_mensagem = "danger";
    } else {
        try {
            // Verificar se a tabela existe
            $sql = "SHOW TABLES LIKE 'mensagens_contato'";
            $stmt = $conn->query($sql);
            if ($stmt->rowCount() == 0) {
                // Criar a tabela se não existir
                $sql = "CREATE TABLE IF NOT EXISTS mensagens_contato (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    nome VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    telefone VARCHAR(20),
                    assunto VARCHAR(200) NOT NULL,
                    mensagem TEXT NOT NULL,
                    status ENUM('não lida', 'lida', 'respondida') DEFAULT 'não lida',
                    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_resposta TIMESTAMP NULL,
                    resposta TEXT NULL,
                    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
                $conn->exec($sql);
            } else {
                // Verificar se a coluna telefone existe
                $sql = "SHOW COLUMNS FROM mensagens_contato LIKE 'telefone'";
                $stmt = $conn->query($sql);
                if ($stmt->rowCount() == 0) {
                    // Adicionar a coluna telefone se não existir
                    $sql = "ALTER TABLE mensagens_contato ADD COLUMN telefone VARCHAR(20) AFTER email";
                    $conn->exec($sql);
                }
            }

            // Salvar mensagem no banco de dados
            $sql = "INSERT INTO mensagens_contato (nome, email, telefone, assunto, mensagem) 
                    VALUES (:nome, :email, :telefone, :assunto, :mensagem)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'assunto' => $assunto,
                'mensagem' => $mensagem_texto
            ]);

            $mensagem = "Mensagem enviada com sucesso! Entraremos em contato em breve.";
            $tipo_mensagem = "success";

            // Limpar campos após envio bem-sucedido
            $nome = $email = $telefone = $assunto = $mensagem_texto = '';
        } catch (PDOException $e) {
            // Log do erro para debug
            error_log("Erro ao enviar mensagem: " . $e->getMessage());
            
            $mensagem = "Erro ao enviar mensagem. Por favor, tente novamente mais tarde.";
            $tipo_mensagem = "danger";
            
            // Em ambiente de desenvolvimento, mostrar detalhes do erro
            if (ENVIRONMENT === 'development') {
                $mensagem .= " Detalhes: " . $e->getMessage();
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
    <title>Contacte-nos - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-section {
            padding: 60px 0;
            background: #f5f5f7;
        }
        .contact-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .contact-header h1 {
            font-size: 2.5rem;
            color: #1d1d1f;
            margin-bottom: 20px;
        }
        .contact-header p {
            font-size: 1.2rem;
            color: #6e6e73;
            max-width: 800px;
            margin: 0 auto;
        }
        .contact-info-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .contact-info-card:hover {
            transform: translateY(-5px);
        }
        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        .contact-info-icon {
            width: 50px;
            height: 50px;
            background: #0071e3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #fff;
            font-size: 1.2rem;
        }
        .contact-info-text h4 {
            font-size: 1.1rem;
            color: #1d1d1f;
            margin-bottom: 5px;
        }
        .contact-info-text p {
            color: #6e6e73;
            margin: 0;
        }
        .contact-form-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-control {
            border: 2px solid #e0e0e0;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #0071e3;
            box-shadow: 0 0 0 0.2rem rgba(0,113,227,0.25);
        }
        .btn-contact {
            background: #0071e3;
            color: #fff;
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-contact:hover {
            background: #0077ed;
            transform: translateY(-2px);
        }
        .map-container {
            margin-top: 50px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .map-container iframe {
            width: 100%;
            height: 400px;
            border: 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="contact-section">
        <div class="container">
            <div class="contact-header">
                <h1>Contacte-nos</h1>
                <p>Estamos aqui para ajudar. Entre em contato conosco através dos canais abaixo ou preencha o formulário.</p>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Informações de Contato -->
                <div class="col-lg-4 mb-4">
                    <div class="contact-info-card">
                        <h3 class="mb-4">Informações de Contato</h3>
                        
                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-info-text">
                                <h4>Telefone</h4>
                                <p>(+244) 937 969 636</p>
                                <p>(+244) 923 000 123</p>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info-text">
                                <h4>Email</h4>
                                <p>unitec01@gmail.com</p>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-info-text">
                                <h4>Endereço</h4>
                                <p>Luanda, Angola</p>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-info-text">
                                <h4>Horário de Funcionamento</h4>
                                <p>Segunda a Sexta: 8h às 18h</p>
                                <p>Sábado: 9h às 15h</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Contato -->
                <div class="col-lg-8">
                    <div class="contact-form-card">
                        <h3 class="mb-4">Envie sua Mensagem</h3>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                                           value="<?php echo htmlspecialchars($telefone ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="assunto" class="form-label">Assunto *</label>
                                    <input type="text" class="form-control" id="assunto" name="assunto" 
                                           value="<?php echo htmlspecialchars($assunto ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="mensagem" class="form-label">Mensagem *</label>
                                <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required><?php echo htmlspecialchars($mensagem_texto ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mapa -->
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.0!2d13.2!3d-8.8!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOMKwNDgnMDAuMCJTIDEzwrAxMicwMC4wIkU!5e0!3m2!1spt-PT!2sao!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 