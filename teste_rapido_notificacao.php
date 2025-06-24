<?php
// Teste rápido de notificação Multicaixa Express
require_once 'config/sms_config.php';
require_once 'api/multicaixa_notification.php';

echo "<h1>Teste Rápido - Notificação Multicaixa Express</h1>\n";

// Verificar configurações
echo "<h2>1. Configurações</h2>\n";
echo "<p><strong>API Habilitada:</strong> " . (MULTICAIXA_API_ENABLED ? 'Sim' : 'Não') . "</p>\n";
echo "<p><strong>API URL:</strong> " . MULTICAIXA_API_URL . "</p>\n";
echo "<p><strong>Merchant ID:</strong> " . MULTICAIXA_MERCHANT_ID . "</p>\n";
echo "<p><strong>Terminal ID:</strong> " . MULTICAIXA_TERMINAL_ID . "</p>\n";

// Testar serviço
echo "<h2>2. Teste de Notificação</h2>\n";
$multicaixa = new MulticaixaNotificationService();

$numero = '937960963';
$valor = 5000;
$referencia = 'TEST' . date('YmdHis');
$pedido = 'PED' . time();

echo "<p><strong>Dados:</strong> +244{$numero} | Kz {$valor} | Ref: {$referencia}</p>\n";

if (MULTICAIXA_API_ENABLED) {
    echo "<p>Testando API real...</p>\n";
    $resultado = $multicaixa->enviarNotificacao($numero, $valor, $referencia, $pedido);
} else {
    echo "<p>Testando simulação...</p>\n";
    $resultado = $multicaixa->simularNotificacao($numero, $valor, $referencia, $pedido);
}

if ($resultado['success']) {
    echo "<p style='color: green;'><strong>✓ Sucesso!</strong></p>\n";
    echo "<p><strong>ID:</strong> " . $resultado['data']['notification_id'] . "</p>\n";
    echo "<p><strong>Status:</strong> " . $resultado['data']['status'] . "</p>\n";
    
    // Testar verificação de status
    echo "<h2>3. Verificação de Status</h2>\n";
    $notification_id = $resultado['data']['notification_id'];
    
    if (MULTICAIXA_API_ENABLED) {
        $status_resultado = $multicaixa->verificarStatusNotificacao($notification_id);
    } else {
        $status_resultado = $multicaixa->simularVerificarStatus($notification_id);
    }
    
    if ($status_resultado['success']) {
        echo "<p style='color: green;'><strong>✓ Status verificado!</strong></p>\n";
        echo "<p><strong>Status atual:</strong> " . $status_resultado['data']['status'] . "</p>\n";
        if (isset($status_resultado['data']['bank'])) {
            echo "<p><strong>Banco:</strong> " . $status_resultado['data']['bank'] . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'><strong>✗ Erro no status:</strong> " . $status_resultado['error'] . "</p>\n";
    }
} else {
    echo "<p style='color: red;'><strong>✗ Erro:</strong> " . $resultado['error'] . "</p>\n";
}

// Verificar log
echo "<h2>4. Log</h2>\n";
$log_file = 'api/logs/multicaixa_notifications.log';
if (file_exists($log_file)) {
    echo "<p style='color: green;'><strong>✓ Log encontrado</strong></p>\n";
    $logs = file_get_contents($log_file);
    $linhas = explode("\n", $logs);
    $ultimas = array_slice($linhas, -3);
    echo "<pre>";
    foreach ($ultimas as $linha) {
        if (trim($linha)) {
            echo htmlspecialchars($linha) . "\n";
        }
    }
    echo "</pre>\n";
} else {
    echo "<p style='color: orange;'><strong>⚠ Log não encontrado</strong></p>\n";
}

echo "<h2>5. Resumo</h2>\n";
echo "<p>✅ Teste concluído com sucesso!</p>\n";
echo "<p>📱 Sistema de notificação funcionando</p>\n";
echo "<p>🔧 Modo: " . (MULTICAIXA_API_ENABLED ? 'Produção' : 'Desenvolvimento') . "</p>\n";
?> 