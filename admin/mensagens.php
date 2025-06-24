<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['acao'])) {
        $mensagem_id = $_POST['mensagem_id'] ?? 0;
        
        switch ($_POST['acao']) {
            case 'marcar_lida':
                $sql = "UPDATE mensagens_contato SET status = 'lida' WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $mensagem_id]);
                $mensagem = "Mensagem marcada como lida.";
                $tipo_mensagem = "success";
                break;
                
            case 'responder':
                $resposta = trim($_POST['resposta'] ?? '');
                if (!empty($resposta)) {
                    // Buscar dados da mensagem original
                    $sql = "SELECT nome, email, assunto, mensagem FROM mensagens_contato WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['id' => $mensagem_id]);
                    $dados_mensagem = $stmt->fetch(PDO::FETCH_ASSOC);

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

                    // Enviar email para o cliente
                    $to = $dados_mensagem['email'];
                    $subject = "Re: " . $dados_mensagem['assunto'] . " - UNITEC";
                    
                    // Preparar o corpo do email
                    $message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                            .content { padding: 20px; background: #f9f9f9; }
                            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                            .original-message { background: #eee; padding: 15px; margin: 20px 0; border-left: 4px solid #007bff; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>UNITEC - Resposta ao seu contato</h2>
                            </div>
                            <div class='content'>
                                <p>Olá " . htmlspecialchars($dados_mensagem['nome']) . ",</p>
                                <p>Recebemos sua mensagem e estamos respondendo conforme solicitado:</p>
                                
                                <div class='original-message'>
                                    <p><strong>Sua mensagem:</strong></p>
                                    <p>" . nl2br(htmlspecialchars($dados_mensagem['mensagem'])) . "</p>
                                </div>
                                
                                <p><strong>Nossa resposta:</strong></p>
                                <p>" . nl2br(htmlspecialchars($resposta)) . "</p>
                                
                                <p>Agradecemos seu contato!</p>
                            </div>
                            <div class='footer'>
                                <p>Esta é uma mensagem automática. Por favor, não responda este email.</p>
                                <p>UNITEC - Soluções Tecnológicas</p>
                            </div>
                        </div>
                    </body>
                    </html>";

                    // Headers para envio de email HTML
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= 'From: UNITEC <noreply@unitec.com>' . "\r\n";

                    // Tentar enviar o email
                    if(mail($to, $subject, $message, $headers)) {
                        $mensagem = "Resposta enviada com sucesso e email enviado ao cliente.";
                        $tipo_mensagem = "success";
                    } else {
                        $mensagem = "Resposta salva, mas houve um erro ao enviar o email.";
                        $tipo_mensagem = "warning";
                    }
                }
                break;
                
            case 'excluir':
                $sql = "DELETE FROM mensagens_contato WHERE id = :id";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute(['id' => $mensagem_id])) {
                    $mensagem = "Mensagem excluída com sucesso.";
                    $tipo_mensagem = "success";
                } else {
                    $mensagem = "Erro ao excluir mensagem.";
                    $tipo_mensagem = "danger";
                }
                break;
        }
    }
}

// Buscar mensagens com paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itens_por_pagina = 10;
$offset = ($pagina - 1) * $itens_por_pagina;

// Contar total de mensagens
$total_mensagens = $conn->query("SELECT COUNT(*) FROM mensagens_contato")->fetchColumn();
$total_paginas = ceil($total_mensagens / $itens_por_pagina);

// Buscar mensagens da página atual
$sql = "SELECT * FROM mensagens_contato ORDER BY data_envio DESC LIMIT :offset, :limit";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar mensagens não lidas
$nao_lidas = $conn->query("SELECT COUNT(*) FROM mensagens_contato WHERE status = 'não lida'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Mensagens - Admin Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .mensagem-nao-lida {
            background-color: #fff3cd;
        }
        .mensagem-respondida {
            background-color: #d4edda;
        }
        .pagination {
            margin-top: 20px;
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
                    <h1 class="h2">Gerenciar Mensagens</h1>
                    <?php if ($nao_lidas > 0): ?>
                        <span class="badge bg-danger"><?php echo $nao_lidas; ?> não lidas</span>
                    <?php endif; ?>
                </div>

                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($mensagens)): ?>
                    <div class="alert alert-info">
                        Nenhuma mensagem recebida ainda.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Assunto</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mensagens as $msg): ?>
                                    <tr class="<?php 
                                        echo $msg['status'] == 'não lida' ? 'mensagem-nao-lida' : 
                                            ($msg['status'] == 'respondida' ? 'mensagem-respondida' : ''); 
                                    ?>">
                                        <td><?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?></td>
                                        <td><?php echo htmlspecialchars($msg['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['assunto']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $msg['status'] == 'não lida' ? 'danger' : 
                                                    ($msg['status'] == 'lida' ? 'warning' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($msg['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#verMensagem<?php echo $msg['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($msg['status'] == 'não lida'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="mensagem_id" value="<?php echo $msg['id']; ?>">
                                                    <input type="hidden" name="acao" value="marcar_lida">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta mensagem?');">
                                                <input type="hidden" name="mensagem_id" value="<?php echo $msg['id']; ?>">
                                                <input type="hidden" name="acao" value="excluir">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal Ver Mensagem -->
                                    <div class="modal fade" id="verMensagem<?php echo $msg['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Mensagem de <?php echo htmlspecialchars($msg['nome']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <strong>De:</strong> <?php echo htmlspecialchars($msg['nome']); ?> 
                                                        (<?php echo htmlspecialchars($msg['email']); ?>)
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Assunto:</strong> <?php echo htmlspecialchars($msg['assunto']); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Mensagem:</strong>
                                                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($msg['mensagem'])); ?></p>
                                                    </div>
                                                    <?php if ($msg['resposta']): ?>
                                                        <div class="mb-3">
                                                            <strong>Sua Resposta:</strong>
                                                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($msg['resposta'])); ?></p>
                                                            <small class="text-muted">
                                                                Respondida em: <?php echo date('d/m/Y H:i', strtotime($msg['data_resposta'])); ?>
                                                            </small>
                                                        </div>
                                                    <?php else: ?>
                                                        <form method="POST">
                                                            <input type="hidden" name="mensagem_id" value="<?php echo $msg['id']; ?>">
                                                            <input type="hidden" name="acao" value="responder">
                                                            <div class="mb-3">
                                                                <label class="form-label">Responder:</label>
                                                                <textarea name="resposta" class="form-control" rows="4" required></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-paper-plane me-2"></i>Enviar Resposta
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                        <nav aria-label="Navegação de páginas">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagina < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>">Próxima</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 