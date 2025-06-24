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
    <title>Hero Moderno - UNITEC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <style>
        .hero-section {
            position: relative;
            width: 100%;
            height: 100vh;
            min-height: 600px;
            max-height: 800px;
            overflow: hidden;
        }

        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #000;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }

        .slide-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
            width: 90%;
            max-width: 800px;
            z-index: 2;
        }

        .slide-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .slide-content h3 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease 0.2s;
        }

        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease 0.4s;
        }

        .slide-content .btn {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease 0.6s;
        }

        .swiper-slide-active .slide-content h1,
        .swiper-slide-active .slide-content h3,
        .swiper-slide-active .slide-content p,
        .swiper-slide-active .slide-content .btn {
            opacity: 1;
            transform: translateY(0);
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: #fff;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 1.5rem;
        }

        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: #fff;
            opacity: 0.5;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
            background: #fff;
        }

        @media (max-width: 768px) {
            .slide-content h1 {
                font-size: 2.5rem;
            }
            .slide-content h3 {
                font-size: 1.5rem;
            }
            .slide-content p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <section class="hero-section">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($slides as $slide): ?>
                    <?php 
                    $imagem_path = $slide['imagem'];
                    $imagem_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/UNITEC/' . $imagem_path;
                    ?>
                    <div class="swiper-slide">
                        <img src="<?php echo htmlspecialchars($imagem_url); ?>" 
                             alt="<?php echo htmlspecialchars($slide['titulo']); ?>"
                             loading="lazy">
                        <div class="slide-content">
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
            
            <!-- Navegação -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            
            <!-- Paginação -->
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.hero-swiper', {
                // Configurações básicas
                loop: true,
                effect: 'fade',
                speed: 1000,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                
                // Navegação
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                
                // Paginação
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                
                // Efeitos
                fadeEffect: {
                    crossFade: true
                },
                
                // Responsividade
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    768: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    }
                }
            });
        });
    </script>
</body>
</html> 