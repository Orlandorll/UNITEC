<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Buscar mensagens do usuário
$sql = "SELECT * FROM mensagens_contato WHERE email = :email ORDER BY data_envio DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['email' => $_SESSION['usuario_email']]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Mensagens - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .mensagens-section {
            padding: 60px 0;
            background: #f5f5f7;
        }
        .mensagem-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .mensagem-header {
            background: #007bff;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mensagem-body {
            padding: 20px;
        }
        .mensagem-footer {
            background: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #dee2e6;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .status-nao-lida {
            background: #dc3545;
            color: white;
        }
        .status-lida {
            background: #ffc107;
            color: black;
        }
        .status-respondida {
            background: #28a745;
            color: white;
        }
        .resposta-box {
            background: #e9ecef;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="mensagens-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="text-center mb-4">Minhas Mensagens</h1>

                    <?php if (empty($mensagens)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Você ainda não enviou nenhuma mensagem.
                            <a href="contato.php" class="alert-link">Enviar uma mensagem</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($mensagens as $msg): ?>
                            <div class="mensagem-card">
                                <div class="mensagem-header">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($msg['assunto']); ?></h5>
                                    <span class="status-badge status-<?php echo str_replace(' ', '-', $msg['status']); ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </div>
                                <div class="mensagem-body">
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($msg['mensagem'])); ?></p>
                                    
                                    <?php if ($msg['resposta']): ?>
                                        <div class="resposta-box">
                                            <h6 class="mb-2">Resposta da UNITEC:</h6>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($msg['resposta'])); ?></p>
                                            <small class="text-muted">
                                                Respondida em: <?php echo date('d/m/Y H:i', strtotime($msg['data_resposta'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mensagem-footer">
                                    <small class="text-muted">
                                        Enviada em: <?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="contato.php" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Nova Mensagem
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 