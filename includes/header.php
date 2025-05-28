<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="top-bar-info">
                    <span><i class="fas fa-phone-alt"></i>(+244) 937 9609 636</span>
                    <span><i class="fas fa-envelope"></i>unitec01@gmail.com</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="top-bar-links">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <span>
                            <i class="fas fa-user"></i>
                            Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                        </span>
                        <a href="perfil.php">Meu Perfil</a>
                        <a href="logout.php">Sair</a>
                    <?php else: ?>
                        <a href="login.php">Entrar</a>
                        <a href="cadastro.php">Cadastrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <a href="index.php" class="logo">
                    <h1 class="logo-text">UNITEC</h1>
                </a>
            </div>
            <div class="col-md-6">
                <form class="search-form">
                    <input type="text" placeholder="Buscar produtos...">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-3">
                <div class="user-menu">
                    <a href="carrinho.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
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
            <li><a href="produtos.php">Todos os Produtos</a></li>
            <?php
            // Buscar categorias principais
            $sql = "SELECT * FROM categorias WHERE status = 1 ORDER BY nome LIMIT 6";
            $stmt = $conn->query($sql);
            while ($categoria = $stmt->fetch()) {
                echo '<li><a href="produtos.php?categoria=' . $categoria['id'] . '">' . 
                     htmlspecialchars($categoria['nome']) . '</a></li>';
            }
            ?>
        </ul>
    </div>
</nav>

<style>
/* Reset e Estilos Gerais */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

body {
    color: #1d1d1f;
    background: #fff;
    line-height: 1.5;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
}

.col-md-3, .col-md-6 {
    padding: 0 10px;
}

.col-md-3 {
    width: 25%;
}

.col-md-6 {
    width: 50%;
}

/* Top Bar */
.top-bar {
    background: #f5f5f7;
    padding: 8px 0;
    font-size: 12px;
    color: #6e6e73;
}

.top-bar-info span {
    margin-right: 20px;
}

.top-bar-info i {
    margin-right: 5px;
}

.top-bar-links {
    text-align: right;
}

.top-bar-links a {
    color: #6e6e73;
    text-decoration: none;
    margin-left: 20px;
    transition: color 0.3s ease;
}

.top-bar-links a:hover {
    color: #1d1d1f;
}

/* Main Header */
.main-header {
    padding: 20px 0;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: saturate(180%) blur(20px);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo-text {
    font-size: 24px;
    font-weight: 600;
    color: #1d1d1f;
    letter-spacing: -0.5px;
}

.search-form {
    display: flex;
    max-width: 500px;
    margin: 0 auto;
    background: #f5f5f7;
    border-radius: 8px;
    overflow: hidden;
}

.search-form input {
    flex: 1;
    padding: 12px 16px;
    border: none;
    background: transparent;
    font-size: 14px;
    color: #1d1d1f;
}

.search-form input::placeholder {
    color: #86868b;
}

.search-form button {
    padding: 0 16px;
    background: transparent;
    border: none;
    color: #86868b;
    cursor: pointer;
    transition: color 0.3s ease;
}

.search-form button:hover {
    color: #1d1d1f;
}

.cart-icon {
    color: #1d1d1f;
    text-decoration: none;
    font-size: 18px;
    position: relative;
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #0071e3;
    color: #fff;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 10px;
}

/* Categories Navigation */
.categories-nav {
    background: #fff;
    border-bottom: 1px solid #d2d2d7;
}

.categories-menu {
    list-style: none;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
    height: 44px;
}

.categories-menu li {
    position: relative;
}

.categories-menu a {
    display: block;
    padding: 0 20px;
    color: #1d1d1f;
    text-decoration: none;
    font-size: 14px;
    font-weight: 400;
    height: 44px;
    line-height: 44px;
    transition: color 0.3s ease;
}

.categories-menu a:hover {
    color: #0071e3;
}

.categories-menu li::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #0071e3;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.categories-menu li:hover::after {
    width: 100%;
}

/* Responsividade */
@media (max-width: 768px) {
    .col-md-3, .col-md-6 {
        width: 100%;
        margin-bottom: 15px;
    }
    
    .top-bar-info, .top-bar-links {
        text-align: center;
    }
    
    .top-bar-links a {
        margin: 0 10px;
    }
    
    .categories-menu {
        overflow-x: auto;
        justify-content: flex-start;
        -webkit-overflow-scrolling: touch;
    }
    
    .categories-menu::-webkit-scrollbar {
        display: none;
    }
    
    .categories-menu a {
        white-space: nowrap;
    }
}
</style> 