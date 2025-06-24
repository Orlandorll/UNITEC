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
    <title>Teste Final do Carousel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .carousel-item {
            height: 500px;
            background-color: #000;
        }
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Teste Final do Carousel</h1>
        
        <!-- Informações de Debug -->
        <div class="debug-info">
            <h3>Informações do Servidor:</h3>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
            <p><strong>Script Filename:</strong> <?php echo $_SERVER['SCRIPT_FILENAME']; ?></p>
            <p><strong>Request URI:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
            <p><strong>Base URL:</strong> <?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?></p>
        </div>

        <!-- Carousel -->
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($slides as $index => $slide): ?>
                    <?php 
                    $imagem_path = $slide['imagem'];
                    $imagem_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/UNITEC/' . $imagem_path;
                    $imagem_exists = file_exists($imagem_path);
                    ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="debug-info">
                            <h4>Slide <?php echo $index + 1; ?>:</h4>
                            <p><strong>Título:</strong> <?php echo htmlspecialchars($slide['titulo']); ?></p>
                            <p><strong>Caminho Original:</strong> <?php echo htmlspecialchars($imagem_path); ?></p>
                            <p><strong>URL Completa:</strong> <?php echo htmlspecialchars($imagem_url); ?></p>
                            <p><strong>Arquivo Existe:</strong> <?php echo $imagem_exists ? 'Sim' : 'Não'; ?></p>
                            <?php if ($imagem_exists): ?>
                                <p><strong>Tamanho:</strong> <?php echo filesize($imagem_path); ?> bytes</p>
                                <p><strong>Permissões:</strong> <?php echo substr(sprintf('%o', fileperms($imagem_path)), -4); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <img src="<?php echo htmlspecialchars($imagem_url); ?>" 
                             class="d-block w-100" 
                             alt="<?php echo htmlspecialchars($slide['titulo']); ?>"
                             onerror="this.onerror=null; console.log('Erro ao carregar imagem: <?php echo htmlspecialchars($imagem_url); ?>');">
                        
                        <div class="carousel-caption">
                            <h3><?php echo htmlspecialchars($slide['subtitulo']); ?></h3>
                            <h1><?php echo htmlspecialchars($slide['titulo']); ?></h1>
                            <?php if ($slide['descricao']): ?>
                                <p><?php echo htmlspecialchars($slide['descricao']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Verificar carregamento das imagens
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