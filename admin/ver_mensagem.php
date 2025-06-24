<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Verificar se o ID da mensagem foi fornecido
if (!isset($_GET['id'])) {
    header("Location: mensagens.php");
    exit;
}

$mensagem_id = (int)$_GET['id'];

// Buscar informações da mensagem
$sql = "SELECT * FROM mensagens_contato WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $mensagem_id);
$stmt->execute();
$mensagem = $stmt->fetch();

if (!$mensagem) {
    header("Location: mensagens.php");
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'marcar_lida':
                $sql = "UPDATE mensagens_contato SET status = 'lida' WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $mensagem_id]);
                $mensagem['status'] = 'lida';
                $mensagem_sucesso = "Mensagem marcada como lida.";
                break;
                
            case 'responder':
                $resposta = trim($_POST['resposta'] ?? '');
                if (!empty($resposta)) {
                    // Atualizar status e resposta no banco
                    $sql = "UPDATE mensagens_contato SET 
                            status = 'respondida', 
                            resposta = :resposta,
                            data_resposta = CURRENT_TIMESTAMP 
                            WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        'id' => $mensagem_id,
                        'resposta' => $resposta
                    ]);
                    
                    $mensagem['status'] = 'respondida';
                    $mensagem['resposta'] = $resposta;
                    $mensagem['data_resposta'] = date('Y-m-d H:i:s');
                    
                    $mensagem_sucesso = "Resposta enviada com sucesso.";
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Mensagem - Admin Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .mensagem-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .mensagem-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .mensagem-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .resposta-content {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ver Mensagem</h1>
                    <div>
                        <a href="mensagens.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Voltar
                        </a>
                    </div>
                </div>

                <?php if (isset($mensagem_sucesso)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $mensagem_sucesso; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="mensagem-card">
                    <div class="mensagem-header">
                        <div class="row">
                            <div class="col-md-8">
                                <h3><?php echo htmlspecialchars($mensagem['assunto']); ?></h3>
                                <p class="text-muted mb-0">
                                    <strong>De:</strong> <?php echo htmlspecialchars($mensagem['nome']); ?> 
                                    (<?php echo htmlspecialchars($mensagem['email']); ?>)
                                </p>
                                <?php if ($mensagem['telefone']): ?>
                                    <p class="text-muted mb-0">
                                        <strong>Telefone:</strong> <?php echo htmlspecialchars($mensagem['telefone']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-<?php 
                                    echo $mensagem['status'] == 'não lida' ? 'danger' : 
                                        ($mensagem['status'] == 'lida' ? 'warning' : 'success'); 
                                ?> fs-6">
                                    <?php echo ucfirst($mensagem['status']); ?>
                                </span>
                                <p class="text-muted mt-2">
                                    <?php echo date('d/m/Y H:i', strtotime($mensagem['data_envio'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mensagem-content">
                        <h5>Mensagem:</h5>
                        <p><?php echo nl2br(htmlspecialchars($mensagem['mensagem'])); ?></p>
                    </div>

                    <?php if ($mensagem['resposta']): ?>
                        <div class="resposta-content">
                            <h5>Sua Resposta:</h5>
                            <p><?php echo nl2br(htmlspecialchars($mensagem['resposta'])); ?></p>
                            <small class="text-muted">
                                Respondida em: <?php echo date('d/m/Y H:i', strtotime($mensagem['data_resposta'])); ?>
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Responder Mensagem</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="acao" value="responder">
                                    <div class="mb-3">
                                        <label class="form-label">Sua Resposta:</label>
                                        <textarea name="resposta" class="form-control" rows="6" required 
                                                  placeholder="Digite sua resposta aqui..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Enviar Resposta
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <?php if ($mensagem['status'] == 'não lida'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="acao" value="marcar_lida">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-check me-2"></i>Marcar como Lida
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="mensagens.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Voltar às Mensagens
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 