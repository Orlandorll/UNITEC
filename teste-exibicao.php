<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Buscar produtos com imagens
$sql = "SELECT p.id, p.nome, ip.caminho_imagem, ip.imagem_principal
        FROM produtos p 
        JOIN imagens_produtos ip ON p.id = ip.produto_id 
        ORDER BY p.id DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Exibição de Imagens - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .teste-container {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .imagem-teste {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #ddd;
            margin: 5px;
        }
        .info-produto {
            margin-bottom: 1rem;
        }
        .contexto {
            background: #f8f9fa;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Teste de Exibição de Imagens</h1>
        
        <?php if (empty($produtos)): ?>
            <div class="alert alert-warning">Nenhum produto com imagem encontrado.</div>
        <?php else: ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="teste-container">
                    <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                    <div class="info-produto">
                        <p><strong>ID:</strong> <?php echo $produto['id']; ?></p>
                        <p><strong>Caminho no banco:</strong> <?php echo htmlspecialchars($produto['caminho_imagem']); ?></p>
                        <p><strong>Imagem Principal:</strong> <?php echo $produto['imagem_principal'] ? 'Sim' : 'Não'; ?></p>
                    </div>

                    <div class="contexto">
                        <h4>1. Exibição na Lista de Produtos (produtos.php)</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card">
                                    <img src="<?php echo get_imagem_produto_segura($produto['caminho_imagem']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contexto">
                        <h4>2. Exibição na Página do Produto (produto.php)</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <img src="<?php echo get_imagem_produto_segura($produto['caminho_imagem']); ?>" 
                                     class="img-fluid" 
                                     alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                     style="max-height: 400px; object-fit: contain;">
                            </div>
                        </div>
                    </div>

                    <div class="contexto">
                        <h4>3. Exibição no Carrinho (carrinho.php)</h4>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo get_imagem_produto_segura($produto['caminho_imagem']); ?>" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                            <div class="ms-3">
                                <h5><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="contexto">
                        <h4>4. Exibição no Painel Admin (admin/produtos.php)</h4>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo get_imagem_produto_segura($produto['caminho_imagem']); ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                            <div class="ms-3">
                                <h5><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="contexto">
                        <h4>5. Teste de Caminhos</h4>
                        <p><strong>Caminho Original:</strong> <?php echo get_imagem_produto($produto['caminho_imagem']); ?></p>
                        <p><strong>Caminho Seguro:</strong> <?php echo get_imagem_produto_segura($produto['caminho_imagem']); ?></p>
                        <p><strong>Arquivo Existe:</strong> <?php echo file_exists(__DIR__ . '/' . $produto['caminho_imagem']) ? '✓ Sim' : '✗ Não'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 