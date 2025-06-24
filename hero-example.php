<?php
// Ativar exibição de erros para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o arquivo está sendo carregado
echo "<!-- Arquivo hero-example.php carregado -->";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Section - Bootstrap Carousel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos básicos para teste */
        .carousel-item {
            height: 400px;
        }
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Teste do Carousel</h1>
        
        <!-- Teste de imagem direta -->
        <div class="mb-5">
            <h2>Teste de Imagem Direta:</h2>
            <img src="uploads/hero/683b8767d5386.webp" style="max-width: 300px;" alt="Teste">
        </div>

        <!-- Carousel Simplificado -->
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="uploads/hero/683b8767d5386.webp" class="d-block w-100" alt="Slide 1">
                    <div class="carousel-caption">
                        <h5>Teste 1</h5>
                    </div>
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Verificar se o Bootstrap foi carregado
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado');
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap carregado');
                var myCarousel = new bootstrap.Carousel(document.getElementById('heroCarousel'));
            } else {
                console.log('Bootstrap não carregado');
            }
        });
    </script>
</body>
</html> 