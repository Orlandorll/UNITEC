<?php
// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Painel Administrativo</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Início
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="produtos.php">
                        <i class="fas fa-box"></i> Produtos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pedidos.php">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categorias.php">
                        <i class="fas fa-tags"></i> Categorias
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['nome']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="perfil.php">
                                <i class="fas fa-user"></i> Meu Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav> 