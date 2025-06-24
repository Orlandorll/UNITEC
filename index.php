<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Buscar categorias ativas
$categorias = get_categorias_ativas();

// Buscar slides ativos
try {
    $sql = "SELECT * FROM hero_images WHERE status = 1 ORDER BY ordem ASC, data_criacao DESC";
    $stmt = $conn->query($sql);
    $slides = $stmt->fetchAll();
} catch (PDOException $e) {
    $slides = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unitec - Tecnologia e Inovação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hero.css">
    <style>
        /* Estilos específicos do carousel */
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
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
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
                            
                            <div class="btn-group">
                                <a href="produtos.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Ver Produtos
                                </a>
                                <a href="sobre.php" class="btn btn-outline-light">
                                    <i class="fas fa-info-circle me-2"></i>Sobre Nós
                                </a>
                                <a href="contato.php" class="btn btn-outline-light">
                                    <i class="fas fa-envelope me-2"></i>Fale Conosco
                                </a>
                            </div>
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

    <!-- Featured Products Section -->
    <section id="featured" class="featured-products py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Produtos Selecionados</span>
                <h2 class="section-title">Destaques da Semana</h2>
                <div class="section-divider">
                    <span></span>
                    <i class="fas fa-star"></i>
                    <span></span>
                </div>
            </div>
            
            <div class="featured-tabs mb-4">
                <ul class="nav nav-pills justify-content-center" id="featuredTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#novidades">Novidades</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#mais-vendidos">Mais Vendidos</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#ofertas">Ofertas</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="novidades">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                        <?php
                        // Buscar produtos mais recentes
                        $sql_novidades = "SELECT p.*, c.nome as categoria_nome,
                                        (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = p.id AND imagem_principal = 1 LIMIT 1) as imagem
                                        FROM produtos p 
                                        LEFT JOIN categorias c ON p.categoria_id = c.id 
                                        WHERE p.status = 1 
                                        ORDER BY p.data_criacao DESC 
                                        LIMIT 4";
                        $stmt_novidades = $conn->query($sql_novidades);
                        $produtos_novidades = $stmt_novidades->fetchAll();

                        foreach ($produtos_novidades as $produto): ?>
                            <div class="col">
                                <div class="product-card">
                                    <div class="product-badge">Novo</div>
                                    <div class="product-thumb">
                                        <a href="produto.php?id=<?php echo $produto['id']; ?>">
                                            <img src="<?php echo get_imagem_produto_segura($produto['imagem']); ?>" 
                                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                        </a>
                                        <div class="product-actions">
                                            <button class="btn-action" data-bs-toggle="tooltip" title="Adicionar aos Favoritos">
                                                <i class="far fa-heart"></i>
                                            </button>
                                            <button class="btn-action" data-bs-toggle="tooltip" title="Adicionar ao Carrinho">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <button class="btn-action" data-bs-toggle="tooltip" title="Visualização Rápida">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category">
                                            <?php echo htmlspecialchars($produto['categoria_nome']); ?>
                                        </div>
                                        <h3 class="product-title">
                                            <a href="produto.php?id=<?php echo $produto['id']; ?>">
                                                <?php echo htmlspecialchars($produto['nome']); ?>
                                            </a>
                                        </h3>
                                        <div class="product-price">
                                            <?php if ($produto['preco_promocional']): ?>
                                                <span class="price-old">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                                <span class="price-new">Kz <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="price-new">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ofertas Especiais -->
    <section class="special-offers py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Promoções</span>
                <h2 class="section-title">Ofertas Especiais</h2>
                <p class="section-description">Aproveite nossas melhores ofertas do momento</p>
            </div>

            <div class="row g-4">
                <?php
                // Buscar produtos em promoção
                $sql_ofertas = "SELECT p.*, c.nome as categoria_nome,
                              (SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = p.id AND imagem_principal = 1 LIMIT 1) as imagem
                              FROM produtos p 
                              LEFT JOIN categorias c ON p.categoria_id = c.id 
                              WHERE p.status = 1 AND p.preco_promocional > 0 
                              ORDER BY (p.preco - p.preco_promocional) / p.preco DESC 
                              LIMIT 3";
                $stmt_ofertas = $conn->query($sql_ofertas);
                $produtos_ofertas = $stmt_ofertas->fetchAll();

                foreach ($produtos_ofertas as $produto): ?>
                    <div class="col-md-4">
                        <div class="offer-card">
                            <div class="offer-badge">
                                <?php 
                                $desconto = round((($produto['preco'] - $produto['preco_promocional']) / $produto['preco']) * 100);
                                echo "-{$desconto}%";
                                ?>
                            </div>
                            <div class="offer-thumb">
                                <a href="produto.php?id=<?php echo $produto['id']; ?>">
                                    <img src="<?php echo get_imagem_produto_segura($produto['imagem']); ?>" 
                                         alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                </a>
                            </div>
                            <div class="offer-info">
                                <div class="offer-category">
                                    <?php echo htmlspecialchars($produto['categoria_nome']); ?>
                                </div>
                                <h3 class="offer-title">
                                    <a href="produto.php?id=<?php echo $produto['id']; ?>">
                                        <?php echo htmlspecialchars($produto['nome']); ?>
                                    </a>
                                </h3>
                                <div class="offer-price">
                                    <span class="price-old">Kz <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                    <span class="price-new">Kz <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                </div>
                                <a href="produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-primary btn-block mt-3">
                                    Comprar Agora
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Por que escolher a UNITEC -->
    <section class="why-choose py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Nossos Diferenciais</span>
                <h2 class="section-title">Por que escolher a UNITEC?</h2>
                <p class="section-description">Descubra o que nos torna a melhor escolha para suas compras</p>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Entrega Rápida</h3>
                        <p>Entrega em todo o país com rastreamento em tempo real</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Garantia Estendida</h3>
                        <p>Produtos com garantia estendida e suporte técnico especializado</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Suporte 24/7</h3>
                        <p>Atendimento ao cliente disponível 24 horas por dia</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h3>Devolução Fácil</h3>
                        <p>Política de devolução simplificada em até 30 dias</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

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