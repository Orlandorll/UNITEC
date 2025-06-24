<?php
require_once 'config/sms_config.php';
require_once 'api/multicaixa_api.php';

// Função para testar a API
function testarAPI() {
    echo "<h2>Teste da API Multicaixa Express</h2>\n";
    
    $multicaixa = new MulticaixaAPI();
    
    // Teste 1: Verificar configurações
    echo "<h3>1. Verificando Configurações</h3>\n";
    echo "<p><strong>API Habilitada:</strong> " . (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED ? 'Sim' : 'Não') . "</p>\n";
    echo "<p><strong>API URL:</strong> " . (defined('MULTICAIXA_API_URL') ? MULTICAIXA_API_URL : 'Não configurada') . "</p>\n";
    echo "<p><strong>Merchant ID:</strong> " . (defined('MULTICAIXA_MERCHANT_ID') ? MULTICAIXA_MERCHANT_ID : 'Não configurado') . "</p>\n";
    echo "<p><strong>Terminal ID:</strong> " . (defined('MULTICAIXA_TERMINAL_ID') ? MULTICAIXA_TERMINAL_ID : 'Não configurado') . "</p>\n";
    
    // Teste 2: Gerar referência
    echo "<h3>2. Teste de Geração de Referência</h3>\n";
    $valor_teste = 5000; // Kz 50,00
    $pedido_id = 'TEST' . time();
    
    if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
        echo "<p>Testando API real...</p>\n";
        $resultado = $multicaixa->gerarReferencia($valor_teste, $pedido_id, 'Teste UNITEC Store');
    } else {
        echo "<p>Testando simulação...</p>\n";
        $resultado = $multicaixa->simularAPI('gerar_referencia', [
            'amount' => $valor_teste,
            'order_id' => $pedido_id
        ]);
    }
    
    if ($resultado['success']) {
        echo "<p style='color: green;'><strong>✓ Sucesso!</strong> Referência gerada</p>\n";
        echo "<p><strong>Referência:</strong> " . $resultado['data']['reference'] . "</p>\n";
        echo "<p><strong>Status:</strong> " . $resultado['data']['status'] . "</p>\n";
        echo "<p><strong>Expira em:</strong> " . $resultado['data']['expires_at'] . "</p>\n";
        
        $referencia_teste = $resultado['data']['reference'];
    } else {
        echo "<p style='color: red;'><strong>✗ Erro:</strong> " . $resultado['error'] . "</p>\n";
        return false;
    }
    
    // Teste 3: Verificar status
    echo "<h3>3. Teste de Verificação de Status</h3>\n";
    if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
        $resultado_status = $multicaixa->verificarStatus($referencia_teste);
    } else {
        $resultado_status = $multicaixa->simularAPI('verificar_status', [
            'reference' => $referencia_teste
        ]);
    }
    
    if ($resultado_status['success']) {
        echo "<p style='color: green;'><strong>✓ Sucesso!</strong> Status verificado</p>\n";
        echo "<p><strong>Status atual:</strong> " . $resultado_status['data']['status'] . "</p>\n";
    } else {
        echo "<p style='color: red;'><strong>✗ Erro:</strong> " . $resultado_status['error'] . "</p>\n";
    }
    
    // Teste 4: Testar callback
    echo "<h3>4. Teste de Callback</h3>\n";
    $callback_url = defined('MULTICAIXA_CALLBACK_URL') ? MULTICAIXA_CALLBACK_URL : 'http://localhost/UNITEC/api/multicaixa_callback.php';
    echo "<p><strong>URL do Callback:</strong> $callback_url</p>\n";
    
    // Simular dados de callback
    $dados_callback = [
        'reference' => $referencia_teste,
        'status' => 'completed',
        'amount' => $valor_teste,
        'order_id' => $pedido_id,
        'transaction_id' => 'TXN' . time(),
        'bank' => 'BFA',
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    echo "<p><strong>Dados de teste:</strong></p>\n";
    echo "<pre>" . json_encode($dados_callback, JSON_PRETTY_PRINT) . "</pre>\n";
    
    // Teste 5: Verificar conectividade
    echo "<h3>5. Teste de Conectividade</h3>\n";
    if (defined('MULTICAIXA_API_URL')) {
        $url = MULTICAIXA_API_URL;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "<p style='color: orange;'><strong>⚠ Aviso:</strong> Erro de conexão: $error</p>\n";
        } else {
            echo "<p style='color: green;'><strong>✓ Conectividade:</strong> HTTP $http_code</p>\n";
        }
    }
    
    return true;
}

// Executar testes
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste API Multicaixa Express - UNITEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background: #f5f5f7; }
        .test-container { 
            background: white; 
            border-radius: 10px; 
            padding: 30px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .test-section { margin-bottom: 30px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .warning { color: #fd7e14; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="text-center mb-4">
            <i class="fas fa-plug"></i> Teste da API Multicaixa Express
        </h1>
        
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Informações do Teste</h5>
            <p class="mb-0">
                Este teste verifica se a API do Multicaixa Express está configurada e funcionando corretamente.
                Se estiver em modo simulação, os testes serão executados com dados fictícios.
            </p>
        </div>
        
        <div class="test-section">
            <?php testarAPI(); ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="multicaixa_simulator.php" class="btn btn-primary me-2">
                <i class="fas fa-play"></i> Ir para Simulador
            </a>
            <a href="checkout.php" class="btn btn-outline-primary">
                <i class="fas fa-shopping-cart"></i> Ir para Checkout
            </a>
        </div>
        
        <div class="mt-4">
            <h5><i class="fas fa-cog"></i> Configurações Recomendadas</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6>Para Desenvolvimento:</h6>
                    <pre><code>MULTICAIXA_API_ENABLED = false</code></pre>
                </div>
                <div class="col-md-6">
                    <h6>Para Produção:</h6>
                    <pre><code>MULTICAIXA_API_ENABLED = true
MULTICAIXA_API_URL = 'https://api.emis.ao/v1'
MULTICAIXA_MERCHANT_ID = 'SEU_MERCHANT_ID'
MULTICAIXA_API_KEY = 'SUA_API_KEY'
MULTICAIXA_TERMINAL_ID = 'SEU_TERMINAL_ID'</code></pre>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 