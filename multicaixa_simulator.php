<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?redirect=multicaixa_simulator.php");
    exit;
}

$pedido_id = $_GET['pedido_id'] ?? '';
$valor = $_GET['valor'] ?? 0;

if (!$pedido_id || !$valor) {
    header("Location: checkout.php?erro=dados_invalidos");
    exit;
}

// Verificar se deve usar API real
$usar_api_real = false;
if (isset($_GET['api_real']) && $_GET['api_real'] === 'true') {
    $usar_api_real = true;
} elseif (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
    $usar_api_real = true;
}

// Carregar configurações da API
require_once 'config/sms_config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multicaixa Express - Simulação - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .multicaixa-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .multicaixa-card {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(30, 64, 175, 0.3);
        }
        .multicaixa-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .multicaixa-logo i {
            font-size: 4rem;
            margin-bottom: 10px;
        }
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #1e40af;
        }
        .status-pendente { border-left-color: #f59e0b; }
        .status-processando { border-left-color: #3b82f6; }
        .status-confirmado { border-left-color: #10b981; }
        .status-expirado { border-left-color: #ef4444; }
        
        .referencia-display {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .referencia-code {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            color: #1e40af;
            letter-spacing: 2px;
        }
        .btn-multicaixa {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-multicaixa:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
            color: white;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .resultado-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="multicaixa-container">
        <!-- Header Multicaixa -->
        <div class="multicaixa-card">
            <div class="multicaixa-logo">
                <i class="fas fa-credit-card"></i>
                <h2>Multicaixa Express</h2>
                <p>Sistema EMIS - Pagamento Eletrônico</p>
            </div>
            
            <div class="row text-center">
                <div class="col-md-6">
                    <h5>Valor a Pagar</h5>
                    <h3 class="mb-0">Kz <?php echo number_format($valor, 2, ',', '.'); ?></h3>
                </div>
                <div class="col-md-6">
                    <h5>Pedido</h5>
                    <h3 class="mb-0">#<?php echo $pedido_id; ?></h3>
                </div>
            </div>
        </div>

        <!-- Painel de Simulação Simplificado -->
        <div class="status-card">
            <h4><i class="fas fa-cogs me-2"></i>Painel de Simulação</h4>
            <p>Use os botões abaixo para simular o processo de pagamento.</p>

            <div class="my-3">
                <button class="btn btn-multicaixa btn-lg me-2" onclick="gerarReferencia()">
                    <i class="fas fa-plus me-2"></i>Gerar Referência
                </button>
                <button class="btn btn-primary btn-lg" id="btn-verificar" onclick="verificarStatus()" disabled>
                    <i class="fas fa-search me-2"></i>Verificar Pagamento
                </button>
            </div>

            <div class="loading-spinner" id="loading-spinner">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2" id="loading-text">Processando...</p>
            </div>

            <div id="resultado-area" class="resultado-card" style="display: none;">
                <h5><i class="fas fa-info-circle me-2"></i>Resultado da Simulação</h5>
                <p class="mb-1"><strong>Referência:</strong> <span id="referencia-display" class="referencia-code"></span></p>
                <p class="mb-0"><strong>Status:</strong> <span id="status-display" class="fw-bold"></span></p>
            </div>

            <div id="confirmacao-area" class="mt-3" style="display: none;">
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Pagamento Confirmado!</h5>
                    <p>O pagamento foi processado com sucesso. Você será redirecionado em breve.</p>
                    <hr>
                    <a href="#" id="link-pedido" class="btn btn-success">Ver Detalhes do Pedido</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Variáveis globais
        let referenciaAtual = null;
        let checkInterval = null;
        
        // Elementos do DOM
        const btnVerificar = document.getElementById('btn-verificar');
        const loadingSpinner = document.getElementById('loading-spinner');
        const loadingText = document.getElementById('loading-text');
        const resultadoArea = document.getElementById('resultado-area');
        const referenciaDisplay = document.getElementById('referencia-display');
        const statusDisplay = document.getElementById('status-display');
        const confirmacaoArea = document.getElementById('confirmacao-area');
        const linkPedido = document.getElementById('link-pedido');

        function mostrarLoading(texto) {
            loadingText.innerText = texto;
            loadingSpinner.style.display = 'block';
            resultadoArea.style.display = 'none';
        }

        function esconderLoading() {
            loadingSpinner.style.display = 'none';
        }

        // Funções de simulação
        function gerarReferencia() {
            mostrarLoading('Gerando referência...');
            
            setTimeout(() => {
                referenciaAtual = 'MCX' + Date.now();
                referenciaDisplay.innerText = referenciaAtual;
                statusDisplay.innerText = 'Pendente';
                statusDisplay.className = 'fw-bold text-warning';
                
                esconderLoading();
                resultadoArea.style.display = 'block';
                btnVerificar.disabled = false;
                confirmacaoArea.style.display = 'none';

            }, 1500);
        }

        function verificarStatus() {
            if (!referenciaAtual) {
                alert('Gere uma referência primeiro.');
                return;
            }

            mostrarLoading('Verificando o estado do pagamento...');
            btnVerificar.disabled = true;

            setTimeout(() => {
                const status = ['confirmado', 'pendente', 'expirado'][Math.floor(Math.random() * 3)];
                
                statusDisplay.innerText = status.charAt(0).toUpperCase() + status.slice(1);

                if (status === 'confirmado') {
                    statusDisplay.className = 'fw-bold text-success';
                    confirmacaoArea.style.display = 'block';
                    linkPedido.href = `pedido-confirmado.php?pedido_id=<?php echo $pedido_id; ?>&ref=${referenciaAtual}`;
                    
                    // Opcional: redirecionar automaticamente
                    // setTimeout(() => {
                    //    window.location.href = linkPedido.href;
                    // }, 3000);

                } else if (status === 'expirado') {
                    statusDisplay.className = 'fw-bold text-danger';
                    btnVerificar.disabled = true; // Não pode verificar mais se expirou
                } else { // Pendente
                    statusDisplay.className = 'fw-bold text-warning';
                    btnVerificar.disabled = false; // Pode verificar novamente
                }
                
                esconderLoading();
                resultadoArea.style.display = 'block';
            }, 2000);
        }
    </script>
</body>
</html> 