<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Buscar conteúdo da página sobre
try {
    $sql = "SELECT * FROM sobre LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sobre = $stmt->fetch();
} catch (PDOException $e) {
    $sobre = null;
}

// Debug para verificar os caminhos das imagens
$debug_imagens = false; // Mude para true para ver informações de debug
if ($debug_imagens && $sobre) {
    echo "<!-- Debug Info:\n";
    echo "CEO1 Imagem: " . ($sobre['ceo1_imagem'] ?? 'não definida') . "\n";
    echo "CEO2 Imagem: " . ($sobre['ceo2_imagem'] ?? 'não definida') . "\n";
    echo "CEO1 Imagem Existe: " . (file_exists($sobre['ceo1_imagem'] ?? '') ? 'sim' : 'não') . "\n";
    echo "CEO2 Imagem Existe: " . (file_exists($sobre['ceo2_imagem'] ?? '') ? 'sim' : 'não') . "\n";
    echo "-->";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .sobre-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        .sobre-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .sobre-title {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }
        .sobre-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 40px;
        }
        .sobre-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4a4a4a;
        }
        .sobre-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 30px;
            height: 100%;
            transition: transform 0.3s ease;
        }
        .sobre-card:hover {
            transform: translateY(-5px);
        }
        .sobre-card-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }
        .sobre-card-content {
            color: #4a4a4a;
            line-height: 1.6;
        }
        .icon-box {
            width: 60px;
            height: 60px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .icon-box i {
            font-size: 24px;
            color: white;
        }
        .team-member {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .team-member img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .team-member:hover img {
            transform: scale(1.05);
        }
        .team-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .team-member:hover .team-info {
            transform: translateY(0);
        }
        .media-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .media-card img, .media-card video {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .media-card:hover .media-overlay {
            opacity: 1;
        }
        .play-button {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .play-button i {
            font-size: 24px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="sobre-section">
        <div class="sobre-container">
            <?php if ($sobre): ?>
                <h1 class="sobre-title"><?php echo htmlspecialchars($sobre['titulo']); ?></h1>

                <div class="sobre-content">
                    <div class="sobre-text">
                        <?php echo nl2br(htmlspecialchars($sobre['conteudo'])); ?>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="sobre-card">
                            <div class="icon-box">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h3 class="sobre-card-title">Nossa Missão</h3>
                            <div class="sobre-card-content">
                                <?php echo nl2br(htmlspecialchars($sobre['missao'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sobre-card">
                            <div class="icon-box">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3 class="sobre-card-title">Nossa Visão</h3>
                            <div class="sobre-card-content">
                                <?php echo nl2br(htmlspecialchars($sobre['visao'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sobre-card">
                            <div class="icon-box">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3 class="sobre-card-title">Nossos Valores</h3>
                            <div class="sobre-card-content">
                                <?php echo nl2br(htmlspecialchars($sobre['valores'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção CEOs -->
                <div class="row mt-5">
                    <div class="col-12 mb-5">
                        <h2 class="text-center mb-4">Nossa Liderança</h2>
                        <div class="row">
                            <!-- CEO 1 -->
                            <div class="col-md-6">
                                <div class="team-member">
                                    <?php if (!empty($sobre['ceo1_imagem'])): ?>
                                        <img src="<?php echo htmlspecialchars($sobre['ceo1_imagem']); ?>" 
                                             alt="<?php echo htmlspecialchars($sobre['ceo1_nome']); ?>">
                                    <?php else: ?>
                                        <img src="assets/images/default-profile.jpg" alt="Imagem padrão">
                                    <?php endif; ?>
                                    <div class="team-info">
                                        <h3><?php echo htmlspecialchars($sobre['ceo1_nome']); ?></h3>
                                        <p class="text-primary mb-2"><?php echo htmlspecialchars($sobre['ceo1_cargo']); ?></p>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($sobre['ceo1_descricao'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <!-- CEO 2 -->
                            <div class="col-md-6">
                                <div class="team-member">
                                    <?php if (!empty($sobre['ceo2_imagem'])): ?>
                                        <img src="<?php echo htmlspecialchars($sobre['ceo2_imagem']); ?>" 
                                             alt="<?php echo htmlspecialchars($sobre['ceo2_nome']); ?>">
                                    <?php else: ?>
                                        <img src="assets/images/default-profile.jpg" alt="Imagem padrão">
                                    <?php endif; ?>
                                    <div class="team-info">
                                        <h3><?php echo htmlspecialchars($sobre['ceo2_nome']); ?></h3>
                                        <p class="text-primary mb-2"><?php echo htmlspecialchars($sobre['ceo2_cargo']); ?></p>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($sobre['ceo2_descricao'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mídia da Empresa -->
                    <div class="col-12">
                        <h2 class="text-center mb-4">Mídia da Empresa</h2>
                        <div class="row">
                            <!-- Vídeo -->
                            <?php if (!empty($sobre['video_empresa'])): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="media-card">
                                    <video id="companyVideo" poster="<?php echo !empty($sobre['imagem_empresa']) ? htmlspecialchars($sobre['imagem_empresa']) : 'assets/images/video-thumbnail.jpg'; ?>">
                                        <source src="<?php echo htmlspecialchars($sobre['video_empresa']); ?>" type="video/mp4">
                                        Seu navegador não suporta vídeos HTML5.
                                    </video>
                                    <div class="media-overlay">
                                        <div class="play-button" onclick="playVideo()">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <!-- Imagem -->
                            <?php if (!empty($sobre['imagem_empresa'])): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="media-card">
                                    <img src="<?php echo htmlspecialchars($sobre['imagem_empresa']); ?>" 
                                         alt="Imagem da empresa" 
                                         class="img-fluid">
                                    <div class="media-overlay">
                                        <div class="play-button">
                                            <i class="fas fa-expand"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <h4>Conteúdo em Construção</h4>
                    <p>Esta página está sendo preparada. Em breve teremos conteúdo disponível.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function playVideo() {
            const video = document.getElementById('companyVideo');
            video.play();
            video.controls = true;
        }
    </script>
</body>
</html> 