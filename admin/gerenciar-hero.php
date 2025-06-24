<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verificar se o usuário está logado e é admin
// if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
   // header('Location: ../admin\gerenciar-hero.php');
   // exit;
//}

$mensagem = '';
$erros = [];

// Processar formulário de adição/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $link_botao = trim($_POST['link_botao'] ?? '');
    $texto_botao = trim($_POST['texto_botao'] ?? '');
    $ordem = (int)($_POST['ordem'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // Validações
    if (empty($titulo)) {
        $erros[] = "O título é obrigatório.";
    }

    // Processar imagem
    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['imagem'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extensao, $extensoes_permitidas)) {
            $erros[] = "A imagem deve ser JPG, JPEG, PNG, GIF ou WebP.";
        } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
            $erros[] = "A imagem deve ter no máximo 5MB.";
        } else {
            $nome_arquivo = uniqid() . '.' . $extensao;
            $diretorio = "../uploads/hero/";
            
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }
            
            if (move_uploaded_file($arquivo['tmp_name'], $diretorio . $nome_arquivo)) {
                $imagem = 'uploads/hero/' . $nome_arquivo;
            } else {
                $erros[] = "Erro ao fazer upload da imagem.";
            }
        }
    } elseif (!$id) {
        $erros[] = "A imagem é obrigatória para novos slides.";
    }

    if (empty($erros)) {
        try {
            $conn->beginTransaction();

            if ($id) {
                // Atualizar slide existente
                $sql = "UPDATE hero_images SET 
                        titulo = :titulo,
                        subtitulo = :subtitulo,
                        descricao = :descricao,
                        link_botao = :link_botao,
                        texto_botao = :texto_botao,
                        ordem = :ordem,
                        status = :status";
                
                if ($imagem) {
                    $sql .= ", imagem = :imagem";
                }
                
                $sql .= " WHERE id = :id";
                
                $stmt = $conn->prepare($sql);
                $params = [
                    ':titulo' => $titulo,
                    ':subtitulo' => $subtitulo,
                    ':descricao' => $descricao,
                    ':link_botao' => $link_botao,
                    ':texto_botao' => $texto_botao,
                    ':ordem' => $ordem,
                    ':status' => $status,
                    ':id' => $id
                ];
                
                if ($imagem) {
                    $params[':imagem'] = $imagem;
                }
                
                $stmt->execute($params);
                $mensagem = "Slide atualizado com sucesso!";
            } else {
                // Inserir novo slide
                $sql = "INSERT INTO hero_images (titulo, subtitulo, descricao, imagem, link_botao, texto_botao, ordem, status) 
                        VALUES (:titulo, :subtitulo, :descricao, :imagem, :link_botao, :texto_botao, :ordem, :status)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':titulo' => $titulo,
                    ':subtitulo' => $subtitulo,
                    ':descricao' => $descricao,
                    ':imagem' => $imagem,
                    ':link_botao' => $link_botao,
                    ':texto_botao' => $texto_botao,
                    ':ordem' => $ordem,
                    ':status' => $status
                ]);
                
                $mensagem = "Slide adicionado com sucesso!";
            }

            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $erros[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Buscar slides existentes
try {
    $sql = "SELECT * FROM hero_images ORDER BY ordem ASC, data_criacao DESC";
    $stmt = $conn->query($sql);
    $slides = $stmt->fetchAll();
} catch (PDOException $e) {
    $erros[] = "Erro ao buscar slides: " . $e->getMessage();
    $slides = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Hero Section - Painel Administrativo</title>
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
        .slide-preview {
            position: relative;
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .slide-preview::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
        }
        .slide-info {
            position: relative;
            z-index: 1;
            color: white;
            padding: 15px;
        }
        .slide-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .slide-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .slide-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Gerenciar Hero Section</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#slideModal">
                        <i class="fas fa-plus"></i> Novo Slide
                    </button>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <?php if (!empty($erros)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erros as $erro): ?>
                                <li><?php echo $erro; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($slides as $slide): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="slide-card">
                                <div class="slide-preview" style="background-image: url('../<?php echo htmlspecialchars($slide['imagem']); ?>')">
                                    <div class="slide-actions">
                                        <button type="button" class="btn btn-sm btn-light" 
                                                onclick="editarSlide(<?php echo htmlspecialchars(json_encode($slide)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="excluirSlide(<?php echo $slide['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="slide-info">
                                        <h5><?php echo htmlspecialchars($slide['titulo']); ?></h5>
                                        <p class="mb-0">Ordem: <?php echo $slide['ordem']; ?></p>
                                        <p class="mb-0">Status: <?php echo $slide['status'] ? 'Ativo' : 'Inativo'; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Adição/Edição -->
    <div class="modal fade" id="slideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar/Editar Slide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="slide_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Título *</label>
                            <input type="text" class="form-control" name="titulo" id="slide_titulo" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subtítulo</label>
                            <input type="text" class="form-control" name="subtitulo" id="slide_subtitulo">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" id="slide_descricao" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Imagem *</label>
                            <input type="file" class="form-control" name="imagem" id="slide_imagem" accept="image/*">
                            <div id="imagem_preview" class="mt-2"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Link do Botão</label>
                                    <input type="text" class="form-control" name="link_botao" id="slide_link_botao">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Texto do Botão</label>
                                    <input type="text" class="form-control" name="texto_botao" id="slide_texto_botao">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ordem</label>
                                    <input type="number" class="form-control" name="ordem" id="slide_ordem" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" name="status" id="slide_status" checked>
                                        <label class="form-check-label">Ativo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Preview da imagem
    document.getElementById('slide_imagem').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagem_preview');
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">`;
            }
            reader.readAsDataURL(file);
        }
    });

    // Editar slide
    function editarSlide(slide) {
        document.getElementById('slide_id').value = slide.id;
        document.getElementById('slide_titulo').value = slide.titulo;
        document.getElementById('slide_subtitulo').value = slide.subtitulo;
        document.getElementById('slide_descricao').value = slide.descricao;
        document.getElementById('slide_link_botao').value = slide.link_botao;
        document.getElementById('slide_texto_botao').value = slide.texto_botao;
        document.getElementById('slide_ordem').value = slide.ordem;
        document.getElementById('slide_status').checked = slide.status == 1;
        
        // Preview da imagem atual
        const preview = document.getElementById('imagem_preview');
        preview.innerHTML = `<img src="../${slide.imagem}" class="img-fluid rounded" style="max-height: 200px;">`;
        
        // Abrir modal
        new bootstrap.Modal(document.getElementById('slideModal')).show();
    }

    // Excluir slide
    function excluirSlide(id) {
        if (confirm('Tem certeza que deseja excluir este slide?')) {
            window.location.href = `excluir-hero.php?id=${id}`;
        }
    }
    </script>
</body>
</html> 