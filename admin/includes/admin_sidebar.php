<?php
// Obter a página atual para destacar o item do menu
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual == 'index.php' ? 'active' : ''; ?>" 
                   href="index.php">
                    <i class="fas fa-home me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual == 'hero.php' ? 'active' : ''; ?>" 
                   href="hero.php">
                    <i class="fas fa-images me-2"></i>
                    Hero Section
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual == 'sobre.php' ? 'active' : ''; ?>" 
                   href="sobre.php">
                    <i class="fas fa-info-circle me-2"></i>
                    Sobre Nós
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual == 'mensagens.php' ? 'active' : ''; ?>" 
                   href="mensagens.php">
                    <i class="fas fa-envelope me-2"></i>
                    Mensagens
                    <?php
                    // Contar mensagens não lidas
                    $sql = "SELECT COUNT(*) as total FROM mensagens_contato WHERE status = 'não lida'";
                    $stmt = $conn->query($sql);
                    $nao_lidas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    if ($nao_lidas > 0):
                    ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $nao_lidas; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual == 'usuarios.php' ? 'active' : ''; ?>" 
                   href="usuarios.php">
                    <i class="fas fa-users me-2"></i>
                    Usuários
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual == 'configuracoes.php' ? 'active' : ''; ?>" 
                   href="configuracoes.php">
                    <i class="fas fa-cog me-2"></i>
                    Configurações
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Área do Usuário</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="../perfil.php">
                    <i class="fas fa-user me-2"></i>
                    Meu Perfil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Sair
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
    padding: 0.5rem 1rem;
    margin: 0.2rem 0;
    border-radius: 0.25rem;
}

.sidebar .nav-link:hover {
    color: #007bff;
    background-color: rgba(0, 123, 255, 0.1);
}

.sidebar .nav-link.active {
    color: #007bff;
    background-color: rgba(0, 123, 255, 0.1);
}

.sidebar .nav-link i {
    width: 20px;
    text-align: center;
}

.sidebar-heading {
    font-size: .75rem;
    text-transform: uppercase;
}

@media (max-width: 767.98px) {
    .sidebar {
        position: static;
        height: auto;
        padding-top: 0;
    }
}
</style> 