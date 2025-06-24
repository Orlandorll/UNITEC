<?php
require_once "config/database.php";

// Buscar slides ativos
$sql = "SELECT * FROM hero_images WHERE status = 1 ORDER BY ordem ASC";
$stmt = $conn->query($sql);
$slides = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Simples de Imagens</title>
    <style>
        .imagem-teste {
            width: 100%;
            max-width: 800px;
            height: 400px;
            object-fit: cover;
            margin: 20px 0;
            border: 2px solid #ccc;
        }
        .info {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1>Teste Simples de Imagens</h1>
        
        <?php foreach ($slides as $slide): ?>
            <?php 
            $imagem_path = $slide['imagem'];
            $imagem_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/UNITEC/' . $imagem_path;
            ?>
            
            <div class="info">
                <h3><?php echo htmlspecialchars($slide['titulo']); ?></h3>
                <p><strong>URL:</strong> <?php echo htmlspecialchars($imagem_url); ?></p>
            </div>
            
            <img src="<?php echo htmlspecialchars($imagem_url); ?>" 
                 class="imagem-teste" 
                 alt="<?php echo htmlspecialchars($slide['titulo']); ?>"
                 onerror="this.onerror=null; console.log('Erro ao carregar imagem: <?php echo htmlspecialchars($imagem_url); ?>');">
        <?php endforeach; ?>
    </div>

    <script>
        window.addEventListener('load', function() {
            document.querySelectorAll('img').forEach(function(img) {
                if (!img.complete || img.naturalHeight === 0) {
                    console.error('Erro ao carregar imagem:', img.src);
                } else {
                    console.log('Imagem carregada com sucesso:', img.src);
                }
            });
        });
    </script>
</body>
</html> 