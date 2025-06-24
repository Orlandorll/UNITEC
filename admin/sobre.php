<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Verificar se a tabela existe
try {
    $sql = "SHOW TABLES LIKE 'sobre'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $tabela_existe = $stmt->fetch();

    if (!$tabela_existe) {
        // Criar a tabela se não existir
        $sql = "CREATE TABLE IF NOT EXISTS sobre (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            conteudo TEXT NOT NULL,
            missao TEXT NOT NULL,
            visao TEXT NOT NULL,
            valores TEXT NOT NULL,
            ceo1_nome VARCHAR(255) NOT NULL,
            ceo1_cargo VARCHAR(255) NOT NULL,
            ceo1_descricao TEXT NOT NULL,
            ceo1_imagem VARCHAR(255) NOT NULL,
            ceo2_nome VARCHAR(255) NOT NULL,
            ceo2_cargo VARCHAR(255) NOT NULL,
            ceo2_descricao TEXT NOT NULL,
            ceo2_imagem VARCHAR(255) NOT NULL,
            video_empresa VARCHAR(255) NOT NULL DEFAULT '',
            imagem_empresa VARCHAR(255) NOT NULL DEFAULT '',
            data_criacao DATETIME NOT NULL,
            data_atualizacao DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $conn->exec($sql);
        $mensagem = "Tabela 'sobre' criada com sucesso!";
        $tipo_mensagem = "success";
    } else {
        // Verificar e adicionar colunas dos CEOs e mídia se não existirem
        try {
            $colunas = [
                'ceo1_nome' => "ALTER TABLE sobre ADD COLUMN ceo1_nome VARCHAR(255) NOT NULL DEFAULT ''",
                'ceo1_cargo' => "ALTER TABLE sobre ADD COLUMN ceo1_cargo VARCHAR(255) NOT NULL DEFAULT ''",
                'ceo1_descricao' => "ALTER TABLE sobre ADD COLUMN ceo1_descricao TEXT NOT NULL",
                'ceo1_imagem' => "ALTER TABLE sobre ADD COLUMN ceo1_imagem VARCHAR(255) NOT NULL DEFAULT ''",
                'ceo2_nome' => "ALTER TABLE sobre ADD COLUMN ceo2_nome VARCHAR(255) NOT NULL DEFAULT ''",
                'ceo2_cargo' => "ALTER TABLE sobre ADD COLUMN ceo2_cargo VARCHAR(255) NOT NULL DEFAULT ''",
                'ceo2_descricao' => "ALTER TABLE sobre ADD COLUMN ceo2_descricao TEXT NOT NULL",
                'ceo2_imagem' => "ALTER TABLE sobre ADD COLUMN ceo2_imagem VARCHAR(255) NOT NULL DEFAULT ''",
                'video_empresa' => "ALTER TABLE sobre ADD COLUMN video_empresa VARCHAR(255) NOT NULL DEFAULT ''",
                'imagem_empresa' => "ALTER TABLE sobre ADD COLUMN imagem_empresa VARCHAR(255) NOT NULL DEFAULT ''"
            ];

            foreach ($colunas as $coluna => $sql) {
                try {
                    $stmt = $conn->prepare("SHOW COLUMNS FROM sobre WHERE Field = :coluna");
                    $stmt->bindParam(':coluna', $coluna);
                    $stmt->execute();
                    if (!$stmt->fetch()) {
                        $conn->exec($sql);
                    }
                } catch (PDOException $e) {
                    // Ignora erro se a coluna já existir
                    if ($e->getCode() != '42S21') {
                        throw $e;
                    }
                }
            }
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar tabela: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao verificar/criar tabela: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Inicializar variáveis
$sobre = [
    'titulo' => '',
    'conteudo' => '',
    'missao' => '',
    'visao' => '',
    'valores' => '',
    'ceo1_nome' => '',
    'ceo1_cargo' => '',
    'ceo1_descricao' => '',
    'ceo1_imagem' => '',
    'ceo2_nome' => '',
    'ceo2_cargo' => '',
    'ceo2_descricao' => '',
    'ceo2_imagem' => '',
    'video_empresa' => '',
    'imagem_empresa' => ''
];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $missao = trim($_POST['missao'] ?? '');
    $visao = trim($_POST['visao'] ?? '');
    $valores = trim($_POST['valores'] ?? '');
    $ceo1_nome = trim($_POST['ceo1_nome'] ?? '');
    $ceo1_cargo = trim($_POST['ceo1_cargo'] ?? '');
    $ceo1_descricao = trim($_POST['ceo1_descricao'] ?? '');
    $ceo2_nome = trim($_POST['ceo2_nome'] ?? '');
    $ceo2_cargo = trim($_POST['ceo2_cargo'] ?? '');
    $ceo2_descricao = trim($_POST['ceo2_descricao'] ?? '');
    $video_empresa = trim($_POST['video_empresa'] ?? '');

    // Processar upload de imagens e vídeos
    $upload_dir = "../uploads/";
    $upload_dir_ceos = $upload_dir . "ceos/";
    $upload_dir_videos = $upload_dir . "videos/";

    // Criar diretórios se não existirem
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    if (!file_exists($upload_dir_ceos)) {
        mkdir($upload_dir_ceos, 0777, true);
    }
    if (!file_exists($upload_dir_videos)) {
        mkdir($upload_dir_videos, 0777, true);
    }

    $ceo1_imagem = $sobre['ceo1_imagem'] ?? ''; // Manter imagem existente por padrão
    $ceo2_imagem = $sobre['ceo2_imagem'] ?? ''; // Manter imagem existente por padrão
    $imagem_empresa = $sobre['imagem_empresa'] ?? ''; // Manter imagem existente por padrão
    $video_empresa = $sobre['video_empresa'] ?? ''; // Manter vídeo existente por padrão

    // Processar upload do vídeo da empresa
    if (isset($_FILES['video_empresa']) && $_FILES['video_empresa']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['video_empresa']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['mp4', 'webm', 'ogg'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'empresa_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir_videos . $new_filename;
            
            if (move_uploaded_file($_FILES['video_empresa']['tmp_name'], $upload_path)) {
                // Remover vídeo antigo se existir
                if (!empty($sobre['video_empresa']) && file_exists("../" . $sobre['video_empresa'])) {
                    unlink("../" . $sobre['video_empresa']);
                }
                $video_empresa = 'uploads/videos/' . $new_filename;
            } else {
                $mensagem = "Erro ao fazer upload do vídeo. Verifique as permissões do diretório.";
                $tipo_mensagem = "danger";
            }
        } else {
            $mensagem = "Formato de arquivo não permitido para o vídeo. Use apenas MP4, WEBM ou OGG.";
            $tipo_mensagem = "danger";
        }
    }

    // Processar upload da imagem do CEO 1
    if (isset($_FILES['ceo1_imagem']) && $_FILES['ceo1_imagem']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['ceo1_imagem']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'ceo1_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir_ceos . $new_filename;
            
            if (move_uploaded_file($_FILES['ceo1_imagem']['tmp_name'], $upload_path)) {
                // Remover imagem antiga se existir
                if (!empty($sobre['ceo1_imagem']) && file_exists("../" . $sobre['ceo1_imagem'])) {
                    unlink("../" . $sobre['ceo1_imagem']);
                }
                $ceo1_imagem = 'uploads/ceos/' . $new_filename;
            } else {
                $mensagem = "Erro ao fazer upload da imagem do CEO 1. Verifique as permissões do diretório.";
                $tipo_mensagem = "danger";
            }
        } else {
            $mensagem = "Formato de arquivo não permitido para a imagem do CEO 1. Use apenas JPG, JPEG, PNG ou WEBP.";
            $tipo_mensagem = "danger";
        }
    }

    // Processar upload da imagem do CEO 2
    if (isset($_FILES['ceo2_imagem']) && $_FILES['ceo2_imagem']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['ceo2_imagem']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'ceo2_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir_ceos . $new_filename;
            
            if (move_uploaded_file($_FILES['ceo2_imagem']['tmp_name'], $upload_path)) {
                // Remover imagem antiga se existir
                if (!empty($sobre['ceo2_imagem']) && file_exists("../" . $sobre['ceo2_imagem'])) {
                    unlink("../" . $sobre['ceo2_imagem']);
                }
                $ceo2_imagem = 'uploads/ceos/' . $new_filename;
            } else {
                $mensagem = "Erro ao fazer upload da imagem do CEO 2. Verifique as permissões do diretório.";
                $tipo_mensagem = "danger";
            }
        } else {
            $mensagem = "Formato de arquivo não permitido para a imagem do CEO 2. Use apenas JPG, JPEG, PNG ou WEBP.";
            $tipo_mensagem = "danger";
        }
    }

    // Processar upload da imagem da empresa
    if (isset($_FILES['imagem_empresa']) && $_FILES['imagem_empresa']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['imagem_empresa']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'empresa_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir_ceos . $new_filename;
            
            if (move_uploaded_file($_FILES['imagem_empresa']['tmp_name'], $upload_path)) {
                // Remover imagem antiga se existir
                if (!empty($sobre['imagem_empresa']) && file_exists("../" . $sobre['imagem_empresa'])) {
                    unlink("../" . $sobre['imagem_empresa']);
                }
                $imagem_empresa = 'uploads/ceos/' . $new_filename;
            } else {
                $mensagem = "Erro ao fazer upload da imagem da empresa. Verifique as permissões do diretório.";
                $tipo_mensagem = "danger";
            }
        } else {
            $mensagem = "Formato de arquivo não permitido para a imagem da empresa. Use apenas JPG, JPEG, PNG ou WEBP.";
            $tipo_mensagem = "danger";
        }
    }

    try {
        // Verificar se já existe conteúdo
        $sql = "SELECT id FROM sobre LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $existe = $stmt->fetch();

        if ($existe) {
            // Atualizar conteúdo existente
            $sql = "UPDATE sobre SET 
                    titulo = :titulo,
                    conteudo = :conteudo,
                    missao = :missao,
                    visao = :visao,
                    valores = :valores,
                    ceo1_nome = :ceo1_nome,
                    ceo1_cargo = :ceo1_cargo,
                    ceo1_descricao = :ceo1_descricao,
                    ceo1_imagem = :ceo1_imagem,
                    ceo2_nome = :ceo2_nome,
                    ceo2_cargo = :ceo2_cargo,
                    ceo2_descricao = :ceo2_descricao,
                    ceo2_imagem = :ceo2_imagem,
                    video_empresa = :video_empresa,
                    imagem_empresa = :imagem_empresa,
                    data_atualizacao = NOW()
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $existe['id']);
        } else {
            // Inserir novo conteúdo
            $sql = "INSERT INTO sobre (
                    titulo, conteudo, missao, visao, valores, 
                    ceo1_nome, ceo1_cargo, ceo1_descricao, ceo1_imagem,
                    ceo2_nome, ceo2_cargo, ceo2_descricao, ceo2_imagem,
                    video_empresa, imagem_empresa,
                    data_criacao, data_atualizacao
                ) VALUES (
                    :titulo, :conteudo, :missao, :visao, :valores,
                    :ceo1_nome, :ceo1_cargo, :ceo1_descricao, :ceo1_imagem,
                    :ceo2_nome, :ceo2_cargo, :ceo2_descricao, :ceo2_imagem,
                    :video_empresa, :imagem_empresa,
                    NOW(), NOW()
                )";
            $stmt = $conn->prepare($sql);
        }

        // Vincular todos os parâmetros
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':conteudo', $conteudo);
        $stmt->bindParam(':missao', $missao);
        $stmt->bindParam(':visao', $visao);
        $stmt->bindParam(':valores', $valores);
        $stmt->bindParam(':ceo1_nome', $ceo1_nome);
        $stmt->bindParam(':ceo1_cargo', $ceo1_cargo);
        $stmt->bindParam(':ceo1_descricao', $ceo1_descricao);
        $stmt->bindParam(':ceo1_imagem', $ceo1_imagem);
        $stmt->bindParam(':ceo2_nome', $ceo2_nome);
        $stmt->bindParam(':ceo2_cargo', $ceo2_cargo);
        $stmt->bindParam(':ceo2_descricao', $ceo2_descricao);
        $stmt->bindParam(':ceo2_imagem', $ceo2_imagem);
        $stmt->bindParam(':video_empresa', $video_empresa);
        $stmt->bindParam(':imagem_empresa', $imagem_empresa);

        if ($stmt->execute()) {
            $mensagem = "Conteúdo da página Sobre atualizado com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao salvar conteúdo: " . implode(" ", $stmt->errorInfo());
            $tipo_mensagem = "danger";
        }

        // Atualizar array $sobre com os novos valores
        $sobre = [
            'titulo' => $titulo,
            'conteudo' => $conteudo,
            'missao' => $missao,
            'visao' => $visao,
            'valores' => $valores,
            'ceo1_nome' => $ceo1_nome,
            'ceo1_cargo' => $ceo1_cargo,
            'ceo1_descricao' => $ceo1_descricao,
            'ceo1_imagem' => $ceo1_imagem,
            'ceo2_nome' => $ceo2_nome,
            'ceo2_cargo' => $ceo2_cargo,
            'ceo2_descricao' => $ceo2_descricao,
            'ceo2_imagem' => $ceo2_imagem,
            'video_empresa' => $video_empresa,
            'imagem_empresa' => $imagem_empresa
        ];
    } catch (PDOException $e) {
        $mensagem = "Erro ao salvar conteúdo: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
} else {
    // Buscar conteúdo atual apenas se não for POST
    try {
        $sql = "SELECT * FROM sobre LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch();
        if ($resultado) {
            $sobre = $resultado;
        }
    } catch (PDOException $e) {
        // Ignora erro se a tabela ainda não existir
        if ($e->getCode() != '42S02') {
            $mensagem = "Erro ao buscar conteúdo: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Página Sobre - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .admin-section {
            padding: 2rem 0;
        }
        .admin-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .admin-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #dee2e6;
        }
        .note-editor {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Gerenciar Página Sobre</h1>
                    <a href="../sobre.php" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> Ver Página
                    </a>
                </div>

                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="titulo" class="form-label">Título Principal</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($sobre['titulo'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="conteudo" class="form-label">Conteúdo Principal</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="6" required><?php echo htmlspecialchars($sobre['conteudo'] ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label for="missao" class="form-label">Missão</label>
                                <textarea class="form-control" id="missao" name="missao" rows="4" required><?php echo htmlspecialchars($sobre['missao'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label for="visao" class="form-label">Visão</label>
                                <textarea class="form-control" id="visao" name="visao" rows="4" required><?php echo htmlspecialchars($sobre['visao'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label for="valores" class="form-label">Valores</label>
                                <textarea class="form-control" id="valores" name="valores" rows="4" required><?php echo htmlspecialchars($sobre['valores'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Seção CEOs e Mídia -->
                    <div class="row mt-5">
                        <!-- CEOs -->
                        <div class="col-12 mb-5">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h2 class="h4 mb-0 text-primary">Nossa Liderança</h2>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- CEO 1 -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center p-4">
                                                    <div class="position-relative mb-4" style="width: 200px; height: 200px; margin: 0 auto;">
                                                        <?php if (!empty($sobre['ceo1_imagem'])): ?>
                                                            <img src="../<?php echo htmlspecialchars($sobre['ceo1_imagem']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($sobre['ceo1_nome']); ?>" 
                                                                 class="rounded-circle img-fluid position-absolute w-100 h-100"
                                                                 style="object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                                        <?php else: ?>
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center position-absolute w-100 h-100"
                                                                 style="border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                                                <i class="fas fa-user fa-4x text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h3 class="h5 mb-2"><?php echo htmlspecialchars($sobre['ceo1_nome']); ?></h3>
                                                    <p class="text-primary mb-3"><?php echo htmlspecialchars($sobre['ceo1_cargo']); ?></p>
                                                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($sobre['ceo1_descricao'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- CEO 2 -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center p-4">
                                                    <div class="position-relative mb-4" style="width: 200px; height: 200px; margin: 0 auto;">
                                                        <?php if (!empty($sobre['ceo2_imagem'])): ?>
                                                            <img src="../<?php echo htmlspecialchars($sobre['ceo2_imagem']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($sobre['ceo2_nome']); ?>" 
                                                                 class="rounded-circle img-fluid position-absolute w-100 h-100"
                                                                 style="object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                                        <?php else: ?>
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center position-absolute w-100 h-100"
                                                                 style="border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                                                <i class="fas fa-user fa-4x text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h3 class="h5 mb-2"><?php echo htmlspecialchars($sobre['ceo2_nome']); ?></h3>
                                                    <p class="text-primary mb-3"><?php echo htmlspecialchars($sobre['ceo2_cargo']); ?></p>
                                                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($sobre['ceo2_descricao'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mídia da Empresa -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h2 class="h4 mb-0 text-primary">Mídia da Empresa</h2>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Vídeo -->
                                        <div class="col-lg-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body p-4">
                                                    <h3 class="h5 mb-4">Vídeo Institucional</h3>
                                                    <?php if (!empty($sobre['video_empresa'])): ?>
                                                        <div class="ratio ratio-16x9 mb-3">
                                                            <video controls class="rounded shadow-sm">
                                                                <source src="../<?php echo htmlspecialchars($sobre['video_empresa']); ?>" type="video/mp4">
                                                                Seu navegador não suporta vídeos HTML5.
                                                            </video>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-video text-primary"></i>
                                                        </span>
                                                        <input type="file" class="form-control" id="video_empresa" name="video_empresa" 
                                                               accept="video/mp4,video/webm,video/ogg">
                                                    </div>
                                                    <small class="text-muted d-block mt-2">
                                                        Formatos aceitos: MP4, WEBM ou OGG
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Imagem -->
                                        <div class="col-lg-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body p-4">
                                                    <h3 class="h5 mb-4">Imagem da Empresa</h3>
                                                    <?php if (!empty($sobre['imagem_empresa'])): ?>
                                                        <div class="mb-3">
                                                            <img src="../<?php echo htmlspecialchars($sobre['imagem_empresa']); ?>" 
                                                                 alt="Imagem da empresa" 
                                                                 class="img-fluid rounded shadow-sm w-100"
                                                                 style="max-height: 300px; object-fit: cover;">
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-image text-primary"></i>
                                                        </span>
                                                        <input type="file" class="form-control" id="imagem_empresa" name="imagem_empresa" 
                                                               accept="image/*">
                                                    </div>
                                                    <small class="text-muted d-block mt-2">
                                                        Formatos aceitos: JPG, JPEG, PNG ou WEBP
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de edição -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title h5 mb-0">Informações dos CEOs</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- CEO 1 -->
                                <div class="col-md-6">
                                    <h4 class="h6 mb-3">CEO 1</h4>
                                    <div class="mb-3">
                                        <label for="ceo1_nome" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="ceo1_nome" name="ceo1_nome" 
                                               value="<?php echo htmlspecialchars($sobre['ceo1_nome']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceo1_cargo" class="form-label">Cargo</label>
                                        <input type="text" class="form-control" id="ceo1_cargo" name="ceo1_cargo" 
                                               value="<?php echo htmlspecialchars($sobre['ceo1_cargo']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceo1_descricao" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="ceo1_descricao" name="ceo1_descricao" 
                                                  rows="4" required><?php echo htmlspecialchars($sobre['ceo1_descricao']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceo1_imagem" class="form-label">Imagem</label>
                                        <?php if (!empty($sobre['ceo1_imagem'])): ?>
                                            <div class="mb-2">
                                                <img src="../<?php echo htmlspecialchars($sobre['ceo1_imagem']); ?>" 
                                                     alt="Imagem atual" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="ceo1_imagem" name="ceo1_imagem" 
                                               accept="image/*">
                                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                                    </div>
                                </div>
                                
                                <!-- CEO 2 -->
                                <div class="col-md-6">
                                    <h4 class="h6 mb-3">CEO 2</h4>
                                    <div class="mb-3">
                                        <label for="ceo2_nome" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="ceo2_nome" name="ceo2_nome" 
                                               value="<?php echo htmlspecialchars($sobre['ceo2_nome']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceo2_cargo" class="form-label">Cargo</label>
                                        <input type="text" class="form-control" id="ceo2_cargo" name="ceo2_cargo" 
                                               value="<?php echo htmlspecialchars($sobre['ceo2_cargo']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceo2_descricao" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="ceo2_descricao" name="ceo2_descricao" 
                                                  rows="4" required><?php echo htmlspecialchars($sobre['ceo2_descricao']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceo2_imagem" class="form-label">Imagem</label>
                                        <?php if (!empty($sobre['ceo2_imagem'])): ?>
                                            <div class="mb-2">
                                                <img src="../<?php echo htmlspecialchars($sobre['ceo2_imagem']); ?>" 
                                                     alt="Imagem atual" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="ceo2_imagem" name="ceo2_imagem" 
                                               accept="image/*">
                                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#conteudo').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html> 