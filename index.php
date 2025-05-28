<?php
session_start();
require_once "config/database.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unitec - Tecnologia e Inovação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="top-bar-info">
                        <span><i class="fas fa-phone-alt me-2"></i>(+244) 937 9609 636</span>
                        <span><i class="fas fa-envelope me-2"></i>unitec01@gmail.com</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="top-bar-links text-end">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <span class="me-3">
                                <i class="fas fa-user me-1"></i>
                                Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                            </span>
                            <a href="perfil.php"><i class="fas fa-user-cog me-1"></i>Meu Perfil</a>
                            <span class="mx-2">|</span>
                            <a href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Sair</a>
                        <?php else: ?>
                            <a href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Entrar</a>
                            <span class="mx-2">|</span>
                            <a href="cadastro.php"><i class="fas fa-user-plus me-1"></i>Cadastrar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="index.php" class="logo">
                        <span class="logo-text">UNI<span class="text-primary">TEC</span></span>
                    </a>
                </div>
                <div class="col-md-6">
                    <form class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="O que você está procurando?">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <div class="header-actions text-end">
                        <a href="carrinho.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                        <a href="favoritos.php" class="btn btn-outline-primary">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Categories Navigation -->
    <nav class="categories-nav">
        <div class="container">
            <ul class="categories-menu">
                <li><a href="categoria.php?id=1"><i class="fas fa-mobile-alt"></i> Smartphones</a></li>
                <li><a href="categoria.php?id=2"><i class="fas fa-laptop"></i> Notebooks</a></li>
                <li><a href="categoria.php?id=3"><i class="fas fa-tablet-alt"></i> Tablets</a></li>
                <li><a href="categoria.php?id=4"><i class="fas fa-headphones"></i> Acessórios</a></li>
                <li><a href="categoria.php?id=5"><i class="fas fa-tv"></i> TVs</a></li>
                <li><a href="categoria.php?id=6"><i class="fas fa-camera"></i> Câmeras</a></li>
                <li><a href="categoria.php?id=7"><i class="fas fa-gamepad"></i> Games</a></li>
                <li><a href="categoria.php?id=8"><i class="fas fa-home"></i> Casa</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="hero-content">
                        <span class="hero-subtitle">Bem-vindo à Unitec</span>
                        <h1 class="hero-title">Tecnologia e Inovação ao seu alcance</h1>
                        <p class="hero-text">Descubra os melhores produtos tecnológicos com os melhores preços do mercado.</p>
                        <div class="hero-buttons">
                            <a href="produtos.php" class="btn btn-primary btn-lg">Ver Produtos</a>
                            <a href="sobre.php" class="btn btn-outline-primary btn-lg">Sobre Nós</a>
                        </div>
                        <div class="hero-features">
                            <div class="feature">
                                <i class="fas fa-truck"></i>
                                <span>Entrega Grátis</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-shield-alt"></i>
                                <span>Garantia</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-headset"></i>
                                <span>Suporte 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hero-image">
                        <img src="assets/images/hero-image.png" alt="Tecnologia Unitec" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Produtos em Destaque</h2>
                <p class="section-subtitle">Confira nossos produtos mais populares</p>
            </div>
            <div class="row">
                <!-- Product Card 1 -->
                <div class="col-md-3">
                    <div class="product-card">
                        <div class="product-badge">Novo</div>
                        <div class="product-thumb">
                            <img src="assets/images/products/smartphone.jpg" alt="Smartphone">
                            <div class="product-overlay">
                                <a href="#" class="btn-quick-view"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn-add-cart"><i class="fas fa-shopping-cart"></i></a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>(4.5)</span>
                            </div>
                            <h3 class="product-title">Smartphone XYZ</h3>
                            <div class="product-price">
                                <span class="price">R$ 1.999,00</span>
                                <span class="price-old">R$ 2.499,00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Card 2 -->
                <div class="col-md-3">
                    <div class="product-card">
                        <div class="product-badge sale">Oferta</div>
                        <div class="product-thumb">
                            <img src="assets/images/products/laptop.jpg" alt="Laptop">
                            <div class="product-overlay">
                                <a href="#" class="btn-quick-view"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn-add-cart"><i class="fas fa-shopping-cart"></i></a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>(5.0)</span>
                            </div>
                            <h3 class="product-title">Laptop Pro</h3>
                            <div class="product-price">
                                <span class="price">R$ 4.999,00</span>
                                <span class="price-old">R$ 5.999,00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Card 3 -->
                <div class="col-md-3">
                    <div class="product-card">
                        <div class="product-thumb">
                            <img src="assets/images/products/tablet.jpg" alt="Tablet">
                            <div class="product-overlay">
                                <a href="#" class="btn-quick-view"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn-add-cart"><i class="fas fa-shopping-cart"></i></a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(4.0)</span>
                            </div>
                            <h3 class="product-title">Tablet Ultra</h3>
                            <div class="product-price">
                                <span class="price">R$ 2.499,00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Card 4 -->
                <div class="col-md-3">
                    <div class="product-card">
                        <div class="product-badge">Mais Vendido</div>
                        <div class="product-thumb">
                            <img src="assets/images/products/headphones.jpg" alt="Headphones">
                            <div class="product-overlay">
                                <a href="#" class="btn-quick-view"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn-add-cart"><i class="fas fa-shopping-cart"></i></a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>(4.7)</span>
                            </div>
                            <h3 class="product-title">Headphones Pro</h3>
                            <div class="product-price">
                                <span class="price">R$ 799,00</span>
                                <span class="price-old">R$ 999,00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="footer-info">
                        <h3>Unitec</h3>
                        <p>Sua loja de tecnologia e inovação.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="footer-links">
                        <h4>Links Úteis</h4>
                        <ul>
                            <li><a href="sobre.php">Sobre Nós</a></li>
                            <li><a href="contato.php">Contato</a></li>
                            <li><a href="blog.php">Blog</a></li>
                            <li><a href="faq.php">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="footer-links">
                        <h4>Categorias</h4>
                        <ul>
                            <li><a href="categoria.php?id=1">Smartphones</a></li>
                            <li><a href="categoria.php?id=2">Notebooks</a></li>
                            <li><a href="categoria.php?id=3">Tablets</a></li>
                            <li><a href="categoria.php?id=4">Acessórios</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="footer-newsletter">
                        <h4>Newsletter</h4>
                        <p>Receba nossas novidades e promoções</p>
                        <form action="" method="post">
                            <input type="email" name="email" placeholder="Seu email">
                            <button type="submit">Inscrever</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p>&copy; 2024 Unitec. Todos os direitos reservados.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="payment-methods">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-amex"></i>
                            <i class="fab fa-pix"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 