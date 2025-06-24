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
    <title>Teste de Imagens</title>
    <style>
        .image-test {
            border: 1px solid #ccc;
            margin: 10px;
            padding: 10px;
        }
        .image-test img {
            max-width: 300px;
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Teste de Carregamento de Imagens</h1>
    
    <?php foreach ($slides as $slide): ?>
        <div class="image-test">
            <h2><?php echo htmlspecialchars($slide['titulo']); ?></h2>
            <p><strong>Caminho:</strong> <?php echo htmlspecialchars($slide['imagem']); ?></p>
            <p><strong>URL Completa:</strong> <?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $slide['imagem']; ?></p>
            <p><strong>Existe:</strong> <?php echo file_exists($slide['imagem']) ? 'Sim' : 'Não'; ?></p>
            <img src="<?php echo htmlspecialchars($slide['imagem']); ?>" 
                 alt="<?php echo htmlspecialchars($slide['titulo']); ?>"
                 onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22300%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22300%22%20height%3D%22200%22%20fill%3D%22%23cccccc%22%2F%3E%3Ctext%20x%3D%22150%22%20y%3D%22100%22%20font-family%3D%22Arial%22%20font-size%3D%2216%22%20fill%3D%22%23666666%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EImagem%20n%C3%A3o%20encontrada%3C%2Ftext%3E%3C%2Fsvg%3E';">
        </div>
    <?php endforeach; ?>

    <script>
        // Verificar se as imagens foram carregadas
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