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
    <title>Termos e Condições - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .terms-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .terms-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 40px;
        }
        .terms-title {
            color: #1d1d1f;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .terms-content {
            color: #666;
            line-height: 1.8;
        }
        .terms-content h2 {
            color: #1d1d1f;
            font-size: 1.8rem;
            margin: 40px 0 20px;
            font-weight: 600;
        }
        .terms-content h3 {
            color: #1d1d1f;
            font-size: 1.4rem;
            margin: 30px 0 15px;
            font-weight: 600;
        }
        .terms-content p {
            margin-bottom: 20px;
        }
        .terms-content ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .terms-content li {
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

    <div class="terms-section">
        <div class="container">
            <div class="terms-container">
                <h1 class="terms-title">Termos e Condições</h1>
                
                <div class="terms-content">
                    <p>Bem-vindo aos Termos e Condições da UNITEC. Ao acessar e utilizar nosso site, você concorda em cumprir estes termos. Por favor, leia atentamente.</p>

                    <h2>1. Aceitação dos Termos</h2>
                    <p>Ao acessar e utilizar o site da UNITEC, você concorda em cumprir estes termos e condições. Se você não concordar com qualquer parte destes termos, não deverá utilizar nosso site.</p>

                    <h2>2. Conta de Usuário</h2>
                    <p>Ao criar uma conta em nosso site, você é responsável por:</p>
                    <ul>
                        <li>Manter a confidencialidade de sua senha</li>
                        <li>Fornecer informações precisas e atualizadas</li>
                        <li>Notificar-nos imediatamente sobre qualquer uso não autorizado</li>
                    </ul>

                    <h2>3. Produtos e Serviços</h2>
                    <p>Nossos produtos e serviços são oferecidos conforme disponibilidade. Reservamo-nos o direito de:</p>
                    <ul>
                        <li>Modificar ou descontinuar produtos a qualquer momento</li>
                        <li>Limitar a quantidade de produtos por pedido</li>
                        <li>Recusar pedidos que pareçam fraudulentos</li>
                    </ul>

                    <h2>4. Preços e Pagamento</h2>
                    <p>Todos os preços estão em Kwanza (Kz) e incluem impostos aplicáveis. Aceitamos os métodos de pagamento especificados durante o checkout.</p>

                    <h2>5. Entrega</h2>
                    <p>Nossas políticas de entrega incluem:</p>
                    <ul>
                        <li>Prazos de entrega estimados</li>
                        <li>Áreas de cobertura</li>
                        <li>Taxas de entrega (quando aplicável)</li>
                        <li>Políticas de rastreamento</li>
                    </ul>

                    <h2>6. Devoluções e Reembolsos</h2>
                    <p>Nossa política de devolução permite:</p>
                    <ul>
                        <li>Devolução em até 14 dias após a entrega</li>
                        <li>Produtos devem estar em condições originais</li>
                        <li>Reembolso processado em até 30 dias</li>
                    </ul>

                    <h2>7. Propriedade Intelectual</h2>
                    <p>Todo o conteúdo do site, incluindo textos, imagens, logos e software, é propriedade da UNITEC e está protegido por leis de propriedade intelectual.</p>

                    <h2>8. Limitação de Responsabilidade</h2>
                    <p>A UNITEC não será responsável por:</p>
                    <ul>
                        <li>Danos indiretos ou consequenciais</li>
                        <li>Perdas de dados ou interrupção de negócios</li>
                        <li>Problemas técnicos fora de nosso controle</li>
                    </ul>

                    <h2>9. Alterações nos Termos</h2>
                    <p>Reservamo-nos o direito de modificar estes termos a qualquer momento. As alterações entram em vigor imediatamente após sua publicação no site.</p>

                    <h2>10. Contato</h2>
                    <p>Para questões sobre estes termos, entre em contato conosco:</p>
                    <ul>
                        <li>E-mail: termos@unitec.com</li>
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