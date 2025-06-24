<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/UNITEC/favicon.ico">
    <link rel="stylesheet" href="/UNITEC/assets/css/style.css">
</head>
<body>
<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="top-bar-info">
                    <span class="phone-icon">(+244) 937 9609 636</span>
                    <span class="email-icon">unitec01@gmail.com</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="top-bar-links">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <div class="user-dropdown">
                            <button class="user-menu-btn" id="userMenuBtn">
                                <span class="user-icon"></span>
                                <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                                <span class="arrow-down"></span>
                            </button>
                            <div class="dropdown-content" id="userDropdown">
                                <a href="minhas-mensagens.php" class="dropdown-item messages-icon">
                                    Minhas Mensagens
                                </a>
                                <a href="perfil.php" class="dropdown-item profile-icon">
                                    Meu Perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item logout-icon">
                                    Sair
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="login-link">Entrar</a>
                        <a href="cadastro.php" class="register-link">Cadastrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header">
    <div class="container">
        <!-- Logo and Cart Row -->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <a href="index.php" class="logo">
                    <h1 class="logo-text">UNITEC</h1>
                </a>
            </div>
            <div class="col-md-6 text-end">
                <div class="user-menu">
                    <a class="cart-link" href="carrinho.php">
                        <span class="cart-icon"></span>
                        <?php
                        if (isset($_SESSION['usuario_id'])) {
                            $sql = "SELECT SUM(quantidade) as total FROM carrinho WHERE usuario_id = :usuario_id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
                            $stmt->execute();
                            $total = $stmt->fetchColumn();
                            if ($total > 0) {
                                echo '<span class="cart-count">' . $total . '</span>';
                            }
                        }
                        ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Search Bar Row -->
        <div class="row">
            <div class="col-12">
                <div class="search-container">
                    <form action="produtos.php" method="GET" class="search-form">
                        <div class="search-group">
                            <input type="text" name="busca" class="search-input" placeholder="O que você está procurando?" value="<?php echo isset($busca) ? htmlspecialchars($busca) : ''; ?>">
                            <button class="search-button" type="submit">
                                <span class="search-icon"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="row mt-3">
            <div class="col-12">
                <nav class="main-nav">
                    <ul class="main-menu">
                        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <a href="index.php">
                                <span class="home-icon"></span>
                                Início
                            </a>
                        </li>
                        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'sobre.php' ? 'active' : ''; ?>">
                            <a href="sobre.php">
                                <span class="about-icon"></span>
                                Sobre Nós
                            </a>
                        </li>
                        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'contato.php' ? 'active' : ''; ?>">
                            <a href="contato.php">
                                <span class="contact-icon"></span>
                                Contacte-nos
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>

<!-- Menu de Categorias -->
<nav class="categories-nav">
    <div class="container">
        <ul class="categories-menu">
            <li class="<?php echo !isset($categoria_id) || !$categoria_id ? 'active' : ''; ?>">
                <a href="produtos.php">Todos</a>
            </li>
            <?php 
            // Buscar categorias ativas se não estiverem definidas
            if (!isset($categorias)) {
                require_once "config/database.php";
                require_once "includes/functions.php";
                $categorias = get_categorias_ativas();
            }
            foreach ($categorias as $cat): ?>
                <li class="<?php echo isset($categoria_id) && $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                    <a href="produtos.php?categoria=<?php echo $cat['id']; ?>">
                        <?php
                        // Ícones padrão para cada categoria
                        $icone = 'fa-box'; // Ícone padrão
                        switch (strtolower($cat['nome'])) {
                            case 'smartphones':
                                $icone = 'fa-mobile-alt';
                                break;
                            case 'notebooks':
                                $icone = 'fa-laptop';
                                break;
                            case 'tablets':
                                $icone = 'fa-tablet-alt';
                                break;
                            case 'acessórios':
                                $icone = 'fa-headphones';
                                break;
                            case 'tvs':
                                $icone = 'fa-tv';
                                break;
                            case 'câmeras':
                                $icone = 'fa-camera';
                                break;
                            case 'games':
                                $icone = 'fa-gamepad';
                                break;
                            case 'casa':
                                $icone = 'fa-home';
                                break;
                        }
                        ?>
                        <i class="fas <?php echo $icone; ?>"></i> <?php echo htmlspecialchars($cat['nome']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
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

.col-md-3, .col-md-6, .col-12 {
    padding: 0 10px;
}

.col-md-3 {
    width: 25%;
}

.col-md-6 {
    width: 50%;
}

.col-12 {
    width: 100%;
}

/* Ícones */
.phone-icon::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 5px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M493.4 24.6l-104-24c-11.3-2.6-22.9 3.3-27.5 13.9l-48 112c-4.2 9.8-1.4 21.3 6.9 28l60.6 49.6c-36 76.7-98.9 140.5-177.2 177.2l-49.6-60.6c-6.8-8.3-18.2-11.1-28-6.9l-112 48C3.9 366.5-2 378.1.6 389.4l24 104C27.1 504.2 36.7 512 48 512c256.1 0 464-207.5 464-464 0-11.2-7.7-20.9-18.6-23.4z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.email-icon::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 5px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.user-icon {
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 5px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.arrow-down {
    display: inline-block;
    width: 10px;
    height: 10px;
    margin-left: 5px;
    border: solid #2c3e50;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    transition: transform 0.3s ease;
}

.messages-icon::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 10px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.profile-icon::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 10px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.logout-icon::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 10px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497 273L329 441c-15 15-41 4.5-41-17v-96H152c-13.3 0-24-10.7-24-24v-96c0-13.3 10.7-24 24-24h136V88c0-21.4 25.9-32 41-17l168 168c9.3 9.4 9.3 24.6 0 34zM192 436v-40c0-6.6-5.4-12-12-12H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h84c6.6 0 12-5.4 12-12V76c0-6.6-5.4-12-12-12H96c-53 0-96 43-96 96v192c0 53 43 96 96 96h84c6.6 0 12-5.4 12-12z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.cart-icon {
    display: inline-block;
    width: 24px;
    height: 24px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"/></svg>') no-repeat center;
    background-size: contain;
}

.search-icon {
    display: inline-block;
    width: 20px;
    height: 20px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="white" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/></svg>') no-repeat center;
    background-size: contain;
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

/* User Dropdown */
.user-dropdown {
    position: relative;
    display: inline-block;
}

.user-menu-btn {
    color: white;
    text-decoration: none;
    padding: 8px 20px;
    border-radius: 20px;
    border: 2px solid white;
    background: transparent;
    transition: all 0.3s ease;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.user-menu-btn:hover {
    background: #0071e3;
    color: white;
    border-color: #0071e3;
}

.user-menu-btn:hover .arrow-down {
    border-color: white;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 10px;
    background: #34495e;
    min-width: 220px;
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    z-index: 1000;
    padding: 8px;
}

.dropdown-content.show {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s ease;
    background: #2c3e50;
    margin: 5px;
    border-radius: 8px;
    font-weight: 500;
}

.dropdown-item:hover {
    background: #3498db;
    color: white;
    transform: translateX(5px);
}

.dropdown-divider {
    height: 2px;
    background: #4a6278;
    margin: 8px 5px;
}

/* Cart */
.cart-link {
    position: relative;
    text-decoration: none;
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #0071e3;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    min-width: 18px;
    text-align: center;
}

/* Search */
.search-group {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    flex: 1;
    height: 45px;
    padding: 0 20px;
    border: 2px solid #e0e0e0;
    border-radius: 25px 0 0 25px;
    font-size: 16px;
}

.search-input:focus {
    outline: none;
    border-color: #0071e3;
}

.search-button {
    width: 50px;
    background: #0071e3;
    border: none;
    border-radius: 0 25px 25px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-button:hover {
    background: #0077ed;
}

/* Responsividade */
@media (max-width: 768px) {
    .col-md-6 {
        width: 100%;
        text-align: center;
    }
    
    .top-bar-info, .top-bar-links {
        justify-content: center;
    }
    
    .search-group {
        margin-top: 15px;
    }
}

/* Main Navigation */
.main-nav {
    background: #2c3e50;
    padding: 10px 0;
    border-radius: 10px;
    margin: 10px 0;
}

.main-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    gap: 20px;
}

.main-menu li {
    position: relative;
}

.main-menu li a {
    color: #ecf0f1;
    text-decoration: none;
    padding: 12px 25px;
    display: flex;
    align-items: center;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.main-menu li a:hover {
    background: #3498db;
    transform: translateY(-2px);
}

.main-menu li.active a {
    background: #3498db;
    color: white;
}

.main-menu li a span {
    margin-right: 8px;
}

/* Ícones do Menu Principal */
.home-icon::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="white" d="M280.37 148.26L96 300.11V464a16 16 0 0 0 16 16l112.06-.29a16 16 0 0 0 15.92-16V368a16 16 0 0 1 16-16h64a16 16 0 0 1 16 16v95.64a16 16 0 0 0 16 16.05L464 480a16 16 0 0 0 16-16V300L295.67 148.26a12.19 12.19 0 0 0-15.3 0zM571.6 251.47L488 182.56V44.05a12 12 0 0 0-12-12h-56a12 12 0 0 0-12 12v72.61L318.47 43a48 48 0 0 0-61 0L4.34 251.47a12 12 0 0 0-1.6 16.9l25.5 31A12 12 0 0 0 45.15 301l235.22-193.74a12.19 12.19 0 0 1 15.3 0L530.9 301a12 12 0 0 0 16.9-1.6l25.5-31a12 12 0 0 0-1.7-16.93z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.about-icon::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="white" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

.contact-icon::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="white" d="M497.39 361.8l-112-48a24 24 0 0 0-28 6.9l-49.6 60.6A370.66 370.66 0 0 1 130.6 204.11l60.6-49.6a23.94 23.94 0 0 0 6.9-28l-48-112A24.16 24.16 0 0 0 122.6.61l-104 24A24 24 0 0 0 0 48c0 256.5 207.9 464 464 464a24 24 0 0 0 23.4-18.6l24-104a24.29 24.29 0 0 0-14.01-27.6z"/></svg>') no-repeat center;
    background-size: contain;
    vertical-align: middle;
}

/* Responsividade do Menu Principal */
@media (max-width: 768px) {
    .main-menu {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .main-menu li a {
        width: 100%;
        justify-content: center;
        padding: 15px 25px;
    }

    .main-nav {
        margin: 10px;
        padding: 15px 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            const arrow = userMenuBtn.querySelector('.arrow-down');
            if (arrow) {
                arrow.style.transform = userDropdown.classList.contains('show') ? 'rotate(-135deg)' : 'rotate(45deg)';
            }
        });

        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
                const arrow = userMenuBtn.querySelector('.arrow-down');
                if (arrow) {
                    arrow.style.transform = 'rotate(45deg)';
                }
            }
        });
    }
});
</script>
</body>
</html> 