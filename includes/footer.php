<!-- Footer -->
<footer class="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h4 class="footer-title">Sobre a Unitec</h4>
                        <p class="footer-text">
                            A Unitec é sua loja de tecnologia de confiança, oferecendo os melhores produtos
                            com preços competitivos e excelente atendimento ao cliente.
                        </p>
                        <div class="footer-social">
                            <?php
                            // Buscar configurações da loja
                            $sql = "SELECT facebook, twitter, instagram FROM configuracoes WHERE id = 1";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $config = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($config['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($config['facebook']); ?>" class="social-link" target="_blank">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($config['twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($config['twitter']); ?>" class="social-link" target="_blank">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($config['instagram'])): ?>
                                <a href="<?php echo htmlspecialchars($config['instagram']); ?>" class="social-link" target="_blank">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h4 class="footer-title">Links Rápidos</h4>
                        <ul class="footer-links">
                            <li><a href="sobre.php">Sobre Nós</a></li>
                            <li><a href="contato.php">Contato</a></li>
                            <li><a href="termos.php">Termos e Condições</a></li>
                            <li><a href="privacidade.php">Política de Privacidade</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h4 class="footer-title">Categorias</h4>
                        <ul class="footer-links">
                            <?php
                            try {
                                // Buscar categorias principais
                                $sql = "SELECT id, nome FROM categorias WHERE status = 1 ORDER BY nome LIMIT 5";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($categorias) > 0) {
                                    foreach ($categorias as $cat):
                                    ?>
                                        <li>
                                            <a href="produtos.php?categoria=<?php echo htmlspecialchars($cat['id']); ?>">
                                                <?php echo htmlspecialchars($cat['nome']); ?>
                                            </a>
                                        </li>
                                    <?php 
                                    endforeach;
                                } else {
                                    // Se não houver categorias, mostrar categorias padrão
                                    $categorias_padrao = [
                                        ['id' => 1, 'nome' => 'Smartphones'],
                                        ['id' => 2, 'nome' => 'Computadores'],
                                        ['id' => 3, 'nome' => 'Tablets'],
                                        ['id' => 4, 'nome' => 'Acessórios'],
                                        ['id' => 5, 'nome' => 'Gaming']
                                    ];
                                    
                                    foreach ($categorias_padrao as $cat):
                                    ?>
                                        <li>
                                            <a href="produtos.php?categoria=<?php echo htmlspecialchars($cat['id']); ?>">
                                                <?php echo htmlspecialchars($cat['nome']); ?>
                                            </a>
                                        </li>
                                    <?php 
                                    endforeach;
                                }
                            } catch (PDOException $e) {
                                // Em caso de erro, mostrar categorias padrão
                                $categorias_padrao = [
                                    ['id' => 1, 'nome' => 'Smartphones'],
                                    ['id' => 2, 'nome' => 'Computadores'],
                                    ['id' => 3, 'nome' => 'Tablets'],
                                    ['id' => 4, 'nome' => 'Acessórios'],
                                    ['id' => 5, 'nome' => 'Gaming']
                                ];
                                
                                foreach ($categorias_padrao as $cat):
                                ?>
                                    <li>
                                        <a href="produtos.php?categoria=<?php echo htmlspecialchars($cat['id']); ?>">
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </a>
                                    </li>
                                <?php 
                                endforeach;
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h4 class="footer-title">Contato</h4>
                        <ul class="footer-contact">
                            <?php
                            // Buscar informações de contato
                            $sql = "SELECT endereco, telefone, email_contato FROM configuracoes WHERE id = 1";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $contato = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($contato['endereco'] ?? 'Luanda, Angola'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt"></i>
                                <span><?php echo htmlspecialchars($contato['telefone'] ?? '(+244) 937 9609 636'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($contato['email_contato'] ?? 'unitec01@gmail.com'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> Unitec. Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <i class="fab fa-cc-apple-pay"></i>
                        <i class="fab fa-cc-amex"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer> 