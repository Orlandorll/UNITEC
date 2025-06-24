<?php
// Corrigir caminhos dos arquivos
$config_path = 'config/sms_config.php';
$api_path = 'api/multicaixa_notification.php';

if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die('Erro: Arquivo de configuração não encontrado');
}

if (file_exists($api_path)) {
    require_once $api_path;
} else {
    die('Erro: Arquivo da API não encontrado');
}

// Função para testar notificações
function testarNotificacao($numero_teste = '937960963') {
    echo "<h2>Teste de Notificação Multicaixa Express</h2>\n";
    
    // Verificar configurações
    echo "<h3>1. Verificando Configurações</h3>\n";
    echo "<p><strong>API Habilitada:</strong> " . (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED ? 'Sim' : 'Não') . "</p>\n";
    echo "<p><strong>API URL:</strong> " . (defined('MULTICAIXA_API_URL') ? MULTICAIXA_API_URL : 'Não configurada') . "</p>\n";
    echo "<p><strong>Merchant ID:</strong> " . (defined('MULTICAIXA_MERCHANT_ID') ? MULTICAIXA_MERCHANT_ID : 'Não configurado') . "</p>\n";
    echo "<p><strong>Terminal ID:</strong> " . (defined('MULTICAIXA_TERMINAL_ID') ? MULTICAIXA_TERMINAL_ID : 'Não configurado') . "</p>\n";
    
    // Testar serviço de notificação
    echo "<h3>2. Teste de Envio de Notificação</h3>\n";
    $multicaixa = new MulticaixaNotificationService();
    
    $valor_teste = 5000; // Kz 5.000,00
    $referencia_teste = 'TEST' . date('YmdHis');
    $pedido_teste = 'PED' . time();
    
    echo "<p><strong>Dados de teste:</strong></p>\n";
    echo "<ul>\n";
    echo "<li><strong>Número:</strong> +244$numero_teste</li>\n";
    echo "<li><strong>Valor:</strong> Kz " . number_format($valor_teste, 2, ',', '.') . "</li>\n";
    echo "<li><strong>Referência:</strong> $referencia_teste</li>\n";
    echo "<li><strong>Pedido:</strong> $pedido_teste</li>\n";
    echo "</ul>\n";
    
    if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
        echo "<p>Testando API real...</p>\n";
        $resultado = $multicaixa->enviarNotificacao($numero_teste, $valor_teste, $referencia_teste, $pedido_teste);
    } else {
        echo "<p>Testando simulação...</p>\n";
        $resultado = $multicaixa->simularNotificacao($numero_teste, $valor_teste, $referencia_teste, $pedido_teste);
    }
    
    if ($resultado['success']) {
        echo "<p style='color: green;'><strong>✓ Sucesso!</strong> Notificação enviada</p>\n";
        echo "<p><strong>ID da Notificação:</strong> " . $resultado['data']['notification_id'] . "</p>\n";
        echo "<p><strong>Status:</strong> " . $resultado['data']['status'] . "</p>\n";
        echo "<p><strong>Enviado em:</strong> " . $resultado['data']['sent_at'] . "</p>\n";
        echo "<p><strong>Expira em:</strong> " . $resultado['data']['expires_at'] . "</p>\n";
        
        $notification_id = $resultado['data']['notification_id'];
    } else {
        echo "<p style='color: red;'><strong>✗ Erro:</strong> " . $resultado['error'] . "</p>\n";
        return false;
    }
    
    // Testar verificação de status
    echo "<h3>3. Teste de Verificação de Status</h3>\n";
    if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
        $resultado_status = $multicaixa->verificarStatusNotificacao($notification_id);
    } else {
        $resultado_status = $multicaixa->simularVerificarStatus($notification_id);
    }
    
    if ($resultado_status['success']) {
        echo "<p style='color: green;'><strong>✓ Sucesso!</strong> Status verificado</p>\n";
        echo "<p><strong>Status atual:</strong> " . $resultado_status['data']['status'] . "</p>\n";
        if (isset($resultado_status['data']['bank'])) {
            echo "<p><strong>Banco:</strong> " . $resultado_status['data']['bank'] . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'><strong>✗ Erro:</strong> " . $resultado_status['error'] . "</p>\n";
    }
    
    // Verificar logs
    echo "<h3>4. Verificando Logs</h3>\n";
    $log_file = 'api/logs/multicaixa_notifications.log';
    if (file_exists($log_file)) {
        $logs = file_get_contents($log_file);
        $linhas = explode("\n", $logs);
        $ultimas_linhas = array_slice($linhas, -5); // Últimas 5 linhas
        
        echo "<p><strong>Últimas entradas no log:</strong></p>\n";
        echo "<pre>";
        foreach ($ultimas_linhas as $linha) {
            if (trim($linha)) {
                echo htmlspecialchars($linha) . "\n";
            }
        }
        echo "</pre>\n";
    } else {
        echo "<p style='color: orange;'><strong>⚠ Aviso:</strong> Arquivo de log não encontrado</p>\n";
    }
    
    return $resultado['success'];
}

// Processar formulário de teste
$numero_teste = '937960963';
$valor_teste = 5000;
$resultado_teste = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_teste = $_POST['numero'] ?? '937960963';
    $valor_teste = $_POST['valor'] ?? 5000;
    
    $multicaixa = new MulticaixaNotificationService();
    $referencia_teste = 'TEST' . date('YmdHis');
    $pedido_teste = 'PED' . time();
    
    if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
        $resultado_teste = $multicaixa->enviarNotificacao($numero_teste, $valor_teste, $referencia_teste, $pedido_teste);
    } else {
        $resultado_teste = $multicaixa->simularNotificacao($numero_teste, $valor_teste, $referencia_teste, $pedido_teste);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Notificação Multicaixa Express - UNITEC</title>
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
            <i class="fas fa-mobile-alt"></i> Teste de Notificação Multicaixa Express
        </h1>
        
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Informações do Teste</h5>
            <p class="mb-0">
                Este teste verifica se as notificações do Multicaixa Express estão funcionando.
                <strong>Em modo real:</strong> Você receberá uma notificação real no app Multicaixa Express.
                <strong>Em modo simulação:</strong> Simula o comportamento para desenvolvimento.
            </p>
        </div>
        
        <div class="test-section">
            <?php testarNotificacao($numero_teste); ?>
        </div>
        
        <!-- Formulário para teste manual -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-paper-plane"></i> Teste Manual de Notificação</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Número de Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text">+244</span>
                                <input type="tel" class="form-control" name="numero" 
                                       value="<?php echo htmlspecialchars($numero_teste); ?>" 
                                       placeholder="937960963" maxlength="9" pattern="[0-9]{9}">
                            </div>
                            <small class="text-muted">Digite apenas os 9 dígitos</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor (Kz)</label>
                            <input type="number" class="form-control" name="valor" 
                                   value="<?php echo $valor_teste; ?>" min="100" step="100">
                            <small class="text-muted">Valor em Kwanzas</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane"></i> Enviar Notificação
                            </button>
                        </div>
                    </div>
                </form>
                
                <?php if ($resultado_teste): ?>
                    <div class="mt-3">
                        <?php if ($resultado_teste['success']): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <strong>Sucesso!</strong> 
                                <?php echo $resultado_teste['message']; ?>
                                <br><strong>ID:</strong> <?php echo $resultado_teste['data']['notification_id']; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <strong>Erro!</strong> 
                                <?php echo $resultado_teste['error']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="multicaixa_simulator.php" class="btn btn-primary me-2">
                <i class="fas fa-play"></i> Ir para Simulador
            </a>
            <a href="teste_multicaixa_api.php" class="btn btn-outline-primary">
                <i class="fas fa-plug"></i> Testar API
            </a>
        </div>
        
        <div class="mt-4">
            <h5><i class="fas fa-cog"></i> Configurações Atuais</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6>Para Desenvolvimento:</h6>
                    <pre><code>MULTICAIXA_API_ENABLED = false
# Simula notificações para testes</code></pre>
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
        
        <div class="alert alert-warning mt-4">
            <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
            <p class="mb-0">
                <strong>Para receber notificações reais:</strong> Você precisa ter credenciais válidas da API da EMIS 
                e configurar <code>MULTICAIXA_API_ENABLED = true</code>. Sem essas credenciais, o sistema funciona 
                apenas em modo simulação.
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 