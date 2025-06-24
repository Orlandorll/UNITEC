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
    <title>Teste do Carousel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos básicos do carousel */
        .carousel-item {
            height: 500px;
            background-color: #000;
        }
        
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
        
        .carousel-caption {
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
        }
        
        .carousel-caption h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .carousel-caption h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .carousel-caption p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            opacity: 0.8;
        }
        
        .carousel-indicators {
            margin-bottom: 1rem;
        }
        
        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <!-- Indicadores -->
            <div class="carousel-indicators">
                <?php foreach ($slides as $index => $slide): ?>
                    <button type="button" 
                            data-bs-target="#heroCarousel" 
                            data-bs-slide-to="<?php echo $index; ?>" 
                            <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                <?php endforeach; ?>
            </div>
            
            <!-- Slides -->
            <div class="carousel-inner">
                <?php foreach ($slides as $index => $slide): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($slide['imagem']); ?>" 
                             class="d-block w-100" 
                             alt="<?php echo htmlspecialchars($slide['titulo']); ?>">
                        <div class="carousel-caption">
                            <?php if ($slide['subtitulo']): ?>
                                <h3><?php echo htmlspecialchars($slide['subtitulo']); ?></h3>
                            <?php endif; ?>
                            
                            <h1><?php echo htmlspecialchars($slide['titulo']); ?></h1>
                            
                            <?php if ($slide['descricao']): ?>
                                <p><?php echo htmlspecialchars($slide['descricao']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($slide['link_botao'] && $slide['texto_botao']): ?>
                                <a href="<?php echo htmlspecialchars($slide['link_botao']); ?>" 
                                   class="btn btn-primary btn-lg">
                                    <?php echo htmlspecialchars($slide['texto_botao']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Controles -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicialização do carousel
        document.addEventListener('DOMContentLoaded', function() {
            var myCarousel = new bootstrap.Carousel(document.getElementById('heroCarousel'), {
                interval: 5000,
                wrap: true,
                keyboard: true,
                pause: 'hover'
            });
        });
    </script>
</body>
</html> 