<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Buscar categorias
$sql = "SELECT id, nome FROM categorias ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll();

$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', $_POST['preco'] ?? '');
    $estoque = (int)($_POST['estoque'] ?? 0);
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $imagem = null;

    // Validação
    if (empty($nome)) {
        $erros[] = "O nome do produto é obrigatório.";
    }
    if (empty($descricao)) {
        $erros[] = "A descrição do produto é obrigatória.";
    }
    if (!is_numeric($preco) || $preco <= 0) {
        $erros[] = "O preço deve ser um valor positivo.";
    }
    if ($estoque < 0) {
        $erros[] = "O estoque não pode ser negativo.";
    }

    // Upload da imagem (opcional)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['imagem'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Validação do tipo e tamanho
            if (!in_array($extensao, $extensoes_permitidas)) {
                $erros[] = "A imagem deve ser JPG, JPEG, PNG, GIF ou WebP.";
            } elseif ($arquivo['size'] > 5 * 1024 * 1024) {
                $erros[] = "A imagem deve ter no máximo 5MB.";
            } else {
                // Gera nome único para o arquivo
                $nome_arquivo = uniqid('', true) . '.' . $extensao;
                $diretorio = __DIR__ . '/../uploads/produtos/';
                
                // Cria o diretório se não existir
                if (!is_dir($diretorio)) {
                    if (!mkdir($diretorio, 0777, true)) {
                        $erros[] = "Erro ao criar diretório para upload.";
                    }
                }
                
                // Move o arquivo para o diretório
                if (empty($erros)) {
                    $caminho_completo = $diretorio . $nome_arquivo;
                    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                        $imagem = 'uploads/produtos/' . $nome_arquivo;
                    } else {
                        $erros[] = "Erro ao fazer upload da imagem.";
                    }
                }
            }
        } else {
            $erros[] = "Erro ao fazer upload da imagem.";
        }
    }

    // Cadastro do produto
    if (empty($erros)) {
        try {
            // Gerar slug único
            $slug_base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));
            $slug = $slug_base;
            $contador = 1;
            do {
                $sql = "SELECT COUNT(*) FROM produtos WHERE slug = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$slug]);
                $existe = $stmt->fetchColumn();
                if ($existe) {
                    $slug = $slug_base . '-' . $contador;
                    $contador++;
                }
            } while ($existe);

            $conn->beginTransaction();
            $sql = "INSERT INTO produtos (nome, slug, descricao, preco, estoque, categoria_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nome, $slug, $descricao, $preco, $estoque, $categoria_id]);
            $produto_id = $conn->lastInsertId();

            // Salvar imagem, se houver
            if ($imagem) {
                $sql = "INSERT INTO imagens_produtos (produto_id, caminho_imagem, imagem_principal) VALUES (?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$produto_id, $imagem]);
            }

            $conn->commit();
            $sucesso = true;
        } catch (PDOException $e) {
            $conn->rollBack();
            $erros[] = "Erro ao salvar produto: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-section { padding: 2rem 0; }
        .admin-container { background: white; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; }
        .admin-title { color: #2c3e50; font-size: 1.5rem; font-weight: 500; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #dee2e6; }
        .preview-imagem { max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px; display: none; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Adicionar Produto</h1>
                    <a href="produtos.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
                <?php if (!empty($erros)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erros as $erro): ?>
                                <li><?php echo $erro; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif ($sucesso): ?>
                    <div class="alert alert-success">Produto cadastrado com sucesso!</div>
                    <script>setTimeout(function(){ window.location.href = 'produtos.php'; }, 1500);</script>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto *</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição *</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="4" required><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="preco" class="form-label">Preço *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Kz</span>
                                            <input type="text" class="form-control" id="preco" name="preco" value="<?php echo htmlspecialchars($_POST['preco'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estoque" class="form-label">Estoque *</label>
                                        <input type="number" class="form-control" id="estoque" name="estoque" value="<?php echo (int)($_POST['estoque'] ?? 0); ?>" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">Categoria</label>
                                <select class="form-select" id="categoria_id" name="categoria_id">
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>" <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoria['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem do Produto</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="form-text">Formatos permitidos: JPG, JPEG, PNG, GIF, WebP<br>Tamanho máximo: 5MB</div>
                                <img id="preview" class="preview-imagem mt-2">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar Produto</button>
                        <a href="produtos.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview da imagem
        document.getElementById('imagem').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        // Formatação do preço
        document.getElementById('preco').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            e.target.value = value;
        });
        // Validação do formulário
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>