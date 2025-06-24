<?php
require_once 'config/sms_config.php';
require_once 'api/sms_service.php';

// Função para testar SMS
function testarSMS($numero_teste = '937960963') {
    echo "<h2>Teste do Serviço de SMS</h2>\n";
    
    // Verificar configurações
    echo "<h3>1. Verificando Configurações</h3>\n";
    echo "<p><strong>SMS Habilitado:</strong> " . (SMS_ENABLED ? 'Sim' : 'Não') . "</p>\n";
    echo "<p><strong>Provedor:</strong> " . SMS_PROVIDER . "</p>\n";
    echo "<p><strong>Log Habilitado:</strong> " . (SMS_LOG_ENABLED ? 'Sim' : 'Não') . "</p>\n";
    echo "<p><strong>Arquivo de Log:</strong> " . SMS_LOG_FILE . "</p>\n";
    
    // Testar validação de número
    echo "<h3>2. Teste de Validação de Número</h3>\n";
    echo "<p><strong>Número de teste:</strong> $numero_teste</p>\n";
    echo "<p><strong>Número válido:</strong> " . (validarNumeroAngolano($numero_teste) ? 'Sim' : 'Não') . "</p>\n";
    echo "<p><strong>Número formatado:</strong> " . formatarNumeroAngolano($numero_teste) . "</p>\n";
    
    // Testar envio de SMS
    echo "<h3>3. Teste de Envio de SMS</h3>\n";
    $sms = new SMSService();
    
    $mensagem_teste = "🧪 Teste UNITEC Store\n💰 Valor: Kz 50,00\n📋 Ref: TEST123\n🛒 Pedido: #TEST001\n✅ Confirme o pagamento no Multicaixa Express\n📞 Suporte: (+244) 937 960 963";
    
    echo "<p><strong>Mensagem de teste:</strong></p>\n";
    echo "<pre>" . htmlspecialchars($mensagem_teste) . "</pre>\n";
    
    $resultado = $sms->enviarSMS($numero_teste, $mensagem_teste);
    
    if ($resultado['success']) {
        echo "<p style='color: green;'><strong>✓ Sucesso!</strong> SMS enviado</p>\n";
        echo "<p><strong>Mensagem:</strong> " . $resultado['message'] . "</p>\n";
        if (isset($resultado['numero'])) {
            echo "<p><strong>Número:</strong> " . $resultado['numero'] . "</p>\n";
        }
        if (isset($resultado['timestamp'])) {
            echo "<p><strong>Timestamp:</strong> " . $resultado['timestamp'] . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'><strong>✗ Erro:</strong> " . $resultado['message'] . "</p>\n";
    }
    
    // Verificar logs
    echo "<h3>4. Verificando Logs</h3>\n";
    if (file_exists(SMS_LOG_FILE)) {
        $logs = file_get_contents(SMS_LOG_FILE);
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
$mensagem_teste = '';
$numero_teste = '937960963';
$resultado_teste = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_teste = $_POST['numero'] ?? '937960963';
    $mensagem_teste = $_POST['mensagem'] ?? '';
    
    if ($mensagem_teste) {
        $sms = new SMSService();
        $resultado_teste = $sms->enviarSMS($numero_teste, $mensagem_teste);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste SMS - UNITEC</title>
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
            <i class="fas fa-sms"></i> Teste do Serviço de SMS
        </h1>
        
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Informações do Teste</h5>
            <p class="mb-0">
                Este teste verifica se o serviço de SMS está configurado e funcionando corretamente.
                Em modo local, o SMS é simulado para desenvolvimento.
            </p>
        </div>
        
        <div class="test-section">
            <?php testarSMS($numero_teste); ?>
        </div>
        
        <!-- Formulário para teste manual -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-paper-plane"></i> Teste Manual de SMS</h5>
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
                        <div class="col-md-8">
                            <label class="form-label">Mensagem</label>
                            <textarea class="form-control" name="mensagem" rows="3" 
                                      placeholder="Digite sua mensagem de teste..."><?php echo htmlspecialchars($mensagem_teste); ?></textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar SMS de Teste
                        </button>
                    </div>
                </form>
                
                <?php if ($resultado_teste): ?>
                    <div class="mt-3">
                        <?php if ($resultado_teste['success']): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <strong>Sucesso!</strong> <?php echo $resultado_teste['message']; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <strong>Erro!</strong> <?php echo $resultado_teste['message']; ?>
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
            <a href="checkout.php" class="btn btn-outline-primary">
                <i class="fas fa-shopping-cart"></i> Ir para Checkout
            </a>
        </div>
        
        <div class="mt-4">
            <h5><i class="fas fa-cog"></i> Configurações Atuais</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6>Para Desenvolvimento:</h6>
                    <pre><code>SMS_PROVIDER = 'local'
SMS_ENABLED = true
SMS_LOG_ENABLED = true</code></pre>
                </div>
                <div class="col-md-6">
                    <h6>Para Produção:</h6>
                    <pre><code>SMS_PROVIDER = 'twilio'
SMS_ENABLED = true
TWILIO_ACCOUNT_SID = 'seu_sid'
TWILIO_AUTH_TOKEN = 'seu_token'
TWILIO_FROM_NUMBER = '+244123456789'</code></pre>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 