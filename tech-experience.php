<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Experience - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tech-experience-hero {
            background: linear-gradient(135deg, #0071e3 0%, #005bb5 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 60px;
        }
        .experience-section {
            padding: 80px 0;
        }
        .experience-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .experience-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .experience-icon {
            width: 100px;
            height: 100px;
            background: #0071e3;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
        }
        .experience-card h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #333;
        }
        .experience-card p {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .ar-preview {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .ar-preview img {
            max-width: 100%;
            border-radius: 10px;
        }
        .support-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .support-option {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .support-option i {
            font-size: 50px;
            color: #0071e3;
            margin-bottom: 20px;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .course-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .course-content {
            padding: 20px;
        }
        .course-content h4 {
            margin-bottom: 10px;
            color: #333;
        }
        .course-content p {
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="tech-experience-hero">
        <div class="container">
            <h1 class="display-4 mb-4">Tech Experience</h1>
            <p class="lead">Descubra uma nova forma de interagir com a tecnologia</p>
        </div>
    </section>

    <!-- Realidade Aumentada -->
    <section id="ar" class="experience-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="ar-preview">
                        <img src="assets/images/ar-preview.jpg" alt="AR Preview" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="mb-4">Realidade Aumentada</h2>
                    <p class="lead mb-4">Visualize os produtos em sua casa antes de comprar usando nossa tecnologia AR.</p>
                    <div class="ar-features">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-primary me-3"></i>
                            <span>Visualização em tempo real</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-primary me-3"></i>
                            <span>Medidas precisas</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-primary me-3"></i>
                            <span>Compatível com iOS e Android</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg mt-4" data-bs-toggle="modal" data-bs-target="#arModal">
                        Experimentar AR
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Support -->
    <section id="support" class="experience-section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-4">Tech Support 24/7</h2>
                <p class="lead">Suporte técnico especializado disponível 24 horas por dia, 7 dias por semana.</p>
            </div>
            <div class="support-options">
                <div class="support-option">
                    <i class="fas fa-video"></i>
                    <h4>Videochamada</h4>
                    <p>Converse com um especialista em tempo real</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal">
                        Iniciar Videochamada
                    </button>
                </div>
                <div class="support-option">
                    <i class="fas fa-comments"></i>
                    <h4>Chat ao Vivo</h4>
                    <p>Atendimento via chat com nossa equipe</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#chatModal">
                        Iniciar Chat
                    </button>
                </div>
                <div class="support-option">
                    <i class="fas fa-phone-alt"></i>
                    <h4>Telefone</h4>
                    <p>Atendimento telefônico personalizado</p>
                    <a href="tel:+2449379609636" class="btn btn-primary">
                        Ligar Agora
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Academy -->
    <section id="academy" class="experience-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-4">Tech Academy</h2>
                <p class="lead">Aprenda a usar seus dispositivos com nossos cursos gratuitos e workshops.</p>
            </div>
            <div class="courses-grid">
                <div class="course-card">
                    <img src="assets/images/course-smartphone.jpg" alt="Curso de Smartphone">
                    <div class="course-content">
                        <h4>Smartphone Masterclass</h4>
                        <p>Aprenda a tirar o máximo proveito do seu smartphone.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                            Inscrever-se
                        </button>
                    </div>
                </div>
                <div class="course-card">
                    <img src="assets/images/course-laptop.jpg" alt="Curso de Laptop">
                    <div class="course-content">
                        <h4>Laptop Essentials</h4>
                        <p>Domine as funcionalidades essenciais do seu laptop.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                            Inscrever-se
                        </button>
                    </div>
                </div>
                <div class="course-card">
                    <img src="assets/images/course-tablet.jpg" alt="Curso de Tablet">
                    <div class="course-content">
                        <h4>Tablet Workshop</h4>
                        <p>Descubra como seu tablet pode ser mais produtivo.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                            Inscrever-se
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modais -->
    <!-- Modal AR -->
    <div class="modal fade" id="arModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Realidade Aumentada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ar-container">
                        <div class="ar-preview">
                            <img src="assets/images/ar-preview.jpg" alt="AR Preview" class="img-fluid">
                        </div>
                        <div class="ar-instructions">
                            <h4>Como usar:</h4>
                            <ol>
                                <li>Escolha um produto</li>
                                <li>Clique em "Ver em AR"</li>
                                <li>Permita acesso à câmera</li>
                                <li>Visualize o produto em seu espaço</li>
                            </ol>
                            <button class="btn btn-primary">Iniciar Experiência AR</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Videochamada -->
    <div class="modal fade" id="videoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Iniciar Videochamada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Para iniciar uma videochamada, por favor:</p>
                    <ol>
                        <li>Permita o acesso à sua câmera</li>
                        <li>Verifique sua conexão com a internet</li>
                        <li>Use fones de ouvido para melhor qualidade</li>
                    </ol>
                    <button class="btn btn-primary">Iniciar Videochamada</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chat -->
    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chat ao Vivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-container">
                        <div class="chat-messages" style="height: 300px; overflow-y: auto; margin-bottom: 20px;">
                            <!-- Mensagens do chat serão inseridas aqui -->
                        </div>
                        <div class="chat-input">
                            <input type="text" class="form-control" placeholder="Digite sua mensagem...">
                            <button class="btn btn-primary mt-2">Enviar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Curso -->
    <div class="modal fade" id="courseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inscrever-se no Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar Inscrição</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 