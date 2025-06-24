<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Verificar se o ID do produto foi fornecido
if (!isset($_GET['id'])) {
    header("Location: produtos.php");
    exit;
}

$produto_id = (int)$_GET['id'];

// Buscar dados do produto
$sql = "SELECT * FROM produtos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $produto_id);
$stmt->execute();
$produto = $stmt->fetch();

if (!$produto) {
    header("Location: produtos.php");
    exit;
}

// Buscar todas as categorias
$sql = "SELECT * FROM categorias WHERE status = 1 ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll();

// Buscar imagens do produto
$sql = "SELECT * FROM imagens_produtos WHERE produto_id = :produto_id ORDER BY imagem_principal DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':produto_id', $produto_id);
$stmt->execute();
$imagens = $stmt->fetchAll();

// Processar o formulário
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = (float)$_POST['preco'];
    $preco_promocional = !empty($_POST['preco_promocional']) ? (float)$_POST['preco_promocional'] : null;
    $estoque = (int)$_POST['estoque'];
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $status = isset($_POST['status']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $nova_imagem = null;

    // Processar nova imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['imagem'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
        if (!in_array($extensao, $extensoes_permitidas)) {
            $mensagem = "A imagem deve ser JPG, JPEG, PNG, GIF ou WebP.";
        } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
            $mensagem = "A imagem deve ter no máximo 5MB.";
        } else {
            $nome_arquivo = uniqid() . '.' . $extensao;
            $diretorio = "../uploads/produtos/";
    
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }
    
            if (move_uploaded_file($arquivo['tmp_name'], $diretorio . $nome_arquivo)) {
                $nova_imagem = $nome_arquivo;
            } else {
                $mensagem = "Erro ao fazer upload da imagem.";
            }
        }
    }

    if (empty($mensagem)) {
        try {
            $conn->beginTransaction();

            // Atualizar produto
            $sql = "UPDATE produtos SET 
                    nome = :nome,
                    descricao = :descricao,
                    preco = :preco,
                    preco_promocional = :preco_promocional,
                    estoque = :estoque,
                    categoria_id = :categoria_id,
                    status = :status,
                    destaque = :destaque
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':preco', $preco);
            $stmt->bindParam(':preco_promocional', $preco_promocional);
            $stmt->bindParam(':estoque', $estoque);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':destaque', $destaque);
            $stmt->bindParam(':id', $produto_id);
            $stmt->execute();

            // Se houver nova imagem, atualizar na tabela imagens_produtos
            if ($nova_imagem) {
                // Desativar imagem principal atual
                $sql = "UPDATE imagens_produtos SET imagem_principal = 0 WHERE produto_id = :produto_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':produto_id', $produto_id);
                $stmt->execute();

                // Inserir nova imagem como principal
                $sql = "INSERT INTO imagens_produtos (produto_id, caminho_imagem, imagem_principal) 
                        VALUES (:produto_id, :caminho_imagem, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':produto_id', $produto_id);
                $stmt->bindParam(':caminho_imagem', 'uploads/produtos/' . $nova_imagem);
                $stmt->execute();
            }

            $conn->commit();
            $mensagem = "Produto atualizado com sucesso!";

            // Atualizar dados do produto e imagens
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $produto_id);
            $stmt->execute();
            $produto = $stmt->fetch();

            $sql = "SELECT * FROM imagens_produtos WHERE produto_id = :produto_id ORDER BY imagem_principal DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->execute();
            $imagens = $stmt->fetchAll();

        } catch (PDOException $e) {
            $conn->rollBack();
            $mensagem = "Erro ao atualizar o produto: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Painel Administrativo</title>
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Editar Produto</h1>
                    <a href="produtos.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto *</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="4"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
    <label for="preco" class="form-label">Preço *</label>
    <div class="input-group">
        <span class="input-group-text">KZ</span>
        <input type="number" class="form-control" id="preco" name="preco" step="0.01" value="<?php echo number_format($produto['preco'], 2, ',', '.'); ?>" required>
    </div>
</div>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="preco_promocional" class="form-label">Preço Promocional</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Kz</span>
                                            <input type="number" class="form-control" id="preco_promocional" name="preco_promocional" step="0.01" value="<?php echo $produto['preco_promocional']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">Categoria</label>
                                <select class="form-select" id="categoria_id" name="categoria_id">
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>" <?php echo $produto['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($categoria['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="estoque" class="form-label">Estoque *</label>
                                <input type="number" class="form-control" id="estoque" name="estoque" value="<?php echo $produto['estoque']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="status" name="status" 
                                           <?php echo $produto['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Produto Ativo</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="destaque" name="destaque" 
                                           <?php echo $produto['destaque'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="destaque">Produto em Destaque</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem do Produto</label>
                                <?php if (!empty($imagens)): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo get_imagem_produto_segura($imagens[0]['caminho_imagem']); ?>" 
                                             alt="Imagem atual" 
                                             style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="imagem" name="imagem" 
                                       accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="form-text">
                                    Formatos permitidos: JPG, JPEG, PNG, GIF, WebP<br>
                                    Tamanho máximo: 5MB
                                </div>
                                <img id="preview" class="preview-imagem mt-2">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

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