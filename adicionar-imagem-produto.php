<?php
require_once "config/database.php";
require_once "includes/functions.php";

echo "<h2>Adicionar Imagem ao Produto</h2>";
echo "<pre>";

try {
    // Verificar se o produto existe
    $sql = "SELECT id, nome FROM produtos WHERE id = 9";
    $stmt = $conn->query($sql);
    $produto = $stmt->fetch();

    if (!$produto) {
        throw new Exception("Produto não encontrado!");
    }

    echo "Produto: {$produto['nome']}\n\n";

    // Verificar se já tem imagem
    $sql = "SELECT COUNT(*) FROM imagens_produtos WHERE produto_id = 9";
    $stmt = $conn->query($sql);
    $tem_imagem = $stmt->fetchColumn() > 0;

    if ($tem_imagem) {
        echo "Este produto já tem imagem!\n";
        exit;
    }

    // Verificar se foi enviada uma imagem
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
        $arquivo = $_FILES['imagem'];
        
        // Validar o arquivo
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($extensao, $extensoes_permitidas)) {
                throw new Exception("A imagem deve ser JPG, JPEG, PNG, GIF ou WebP.");
            }
            
            if ($arquivo['size'] > 5 * 1024 * 1024) {
                throw new Exception("A imagem deve ter no máximo 5MB.");
            }

            // Gerar nome único para o arquivo
            $nome_arquivo = uniqid('', true) . '.' . $extensao;
            $diretorio = __DIR__ . '/uploads/produtos/';
            $caminho_completo = $diretorio . $nome_arquivo;
            
            // Mover o arquivo
            if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                // Salvar no banco
                $conn->beginTransaction();
                
                $sql = "INSERT INTO imagens_produtos (produto_id, caminho_imagem, imagem_principal) VALUES (?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([9, 'uploads/produtos/' . $nome_arquivo]);
                
                $conn->commit();
                echo "✓ Imagem adicionada com sucesso!\n";
                echo "Nome do arquivo: {$nome_arquivo}\n";
                echo "Caminho: uploads/produtos/{$nome_arquivo}\n";
                
                // Redirecionar após 2 segundos
                echo "\nRedirecionando...\n";
                header("refresh:2;url=produtos.php");
                exit;
            } else {
                throw new Exception("Erro ao fazer upload da imagem.");
            }
        } else {
            throw new Exception("Erro no upload: " . $arquivo['error']);
        }
    }

    // Exibir formulário
    echo "</pre>";
    ?>
    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label for="imagem" class="form-label">Selecione uma imagem:</label>
            <input type="file" class="form-control" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <div class="form-text">Formatos permitidos: JPG, JPEG, PNG, GIF, WebP<br>Tamanho máximo: 5MB</div>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Imagem</button>
        <a href="produtos.php" class="btn btn-outline-secondary">Cancelar</a>
    </form>
    <style>
        body { padding: 20px; }
        .form-label { font-weight: bold; }
        .form-text { color: #666; }
        .btn { margin-top: 10px; }
    </style>
    <?php

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "\n✗ Erro: " . $e->getMessage() . "\n";
}

echo "</pre>";
?> 