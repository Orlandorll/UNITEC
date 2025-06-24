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
    <title>Política de Privacidade - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .privacy-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .privacy-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 40px;
        }
        .privacy-title {
            color: #1d1d1f;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .privacy-content {
            color: #666;
            line-height: 1.8;
        }
        .privacy-content h2 {
            color: #1d1d1f;
            font-size: 1.8rem;
            margin: 40px 0 20px;
            font-weight: 600;
        }
        .privacy-content h3 {
            color: #1d1d1f;
            font-size: 1.4rem;
            margin: 30px 0 15px;
            font-weight: 600;
        }
        .privacy-content p {
            margin-bottom: 20px;
        }
        .privacy-content ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .privacy-content li {
            margin-bottom: 10px;
        }
        .last-updated {
            color: #888;
            font-style: italic;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="privacy-section">
        <div class="container">
            <div class="privacy-container">
                <h1 class="privacy-title">Política de Privacidade</h1>
                
                <div class="privacy-content">
                    <p>Bem-vindo à política de privacidade da UNITEC. Esta política descreve como coletamos, usamos e protegemos suas informações pessoais quando você utiliza nosso site e serviços.</p>

                    <h2>1. Informações que Coletamos</h2>
                    <p>Coletamos informações que você nos fornece diretamente, incluindo:</p>
                    <ul>
                        <li>Nome completo</li>
                        <li>Endereço de e-mail</li>
                        <li>Número de telefone</li>
                        <li>Endereço de entrega</li>
                        <li>Informações de pagamento</li>
                    </ul>

                    <h2>2. Como Usamos Suas Informações</h2>
                    <p>Utilizamos suas informações para:</p>
                    <ul>
                        <li>Processar seus pedidos e entregas</li>
                        <li>Comunicar sobre seus pedidos e nossa loja</li>
                        <li>Enviar atualizações e ofertas promocionais (com seu consentimento)</li>
                        <li>Melhorar nossos produtos e serviços</li>
                        <li>Prevenir fraudes e aumentar a segurança</li>
                    </ul>

                    <h2>3. Proteção de Dados</h2>
                    <p>Implementamos medidas de segurança técnicas e organizacionais para proteger suas informações pessoais contra acesso não autorizado, alteração, divulgação ou destruição.</p>

                    <h2>4. Compartilhamento de Informações</h2>
                    <p>Não vendemos suas informações pessoais. Compartilhamos informações apenas com:</p>
                    <ul>
                        <li>Prestadores de serviços de entrega</li>
                        <li>Processadores de pagamento</li>
                        <li>Autoridades legais quando exigido por lei</li>
                    </ul>

                    <h2>5. Seus Direitos</h2>
                    <p>Você tem o direito de:</p>
                    <ul>
                        <li>Acessar suas informações pessoais</li>
                        <li>Corrigir informações imprecisas</li>
                        <li>Solicitar a exclusão de seus dados</li>
                        <li>Retirar seu consentimento para marketing</li>
                    </ul>

                    <h2>6. Cookies e Tecnologias Similares</h2>
                    <p>Utilizamos cookies e tecnologias similares para melhorar sua experiência de navegação, analisar o uso do site e personalizar conteúdo e anúncios.</p>

                    <h2>7. Alterações na Política</h2>
                    <p>Podemos atualizar esta política periodicamente. Recomendamos que você revise esta página regularmente para se manter informado sobre como protegemos suas informações.</p>

                    <h2>8. Contato</h2>
                    <p>Se você tiver dúvidas sobre esta política de privacidade, entre em contato conosco através de:</p>
                    <ul>
                        <li>E-mail: privacidade@unitec.com</li>
                        <li>Telefone: (+244) 937 9609 636</li>
                        <li>Endereço: Luanda, Angola</li>
                    </ul>

                    <p class="last-updated">Última atualização: <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 