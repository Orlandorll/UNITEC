<?php
// Configurações de SMS para UNITEC Store

// ========================================
// CONFIGURAÇÕES DE SMS
// ========================================

// Provedor de SMS (escolha um)
define('SMS_PROVIDER', 'local'); // 'twilio', 'africas_talking', 'local'

// ========================================
// TWILIO (Recomendado para produção)
// ========================================
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
define('TWILIO_FROM_NUMBER', '+244123456789'); // Número da UNITEC

// ========================================
// AFRICA'S TALKING (Alternativa para África)
// ========================================
define('AFRICASTALKING_API_KEY', 'your_api_key_here');
define('AFRICASTALKING_USERNAME', 'your_username_here');
define('AFRICASTALKING_FROM', 'UNITEC');

// ========================================
// CONFIGURAÇÕES GERAIS
// ========================================
define('SMS_ENABLED', true); // true/false para habilitar/desabilitar SMS
define('SMS_LOG_ENABLED', true); // Log de SMS para desenvolvimento
define('SMS_LOG_FILE', 'logs/sms_log.txt');

// ========================================
// MENSAGENS PERSONALIZADAS
// ========================================
define('SMS_MENSAGEM_PAGAMENTO', "🏪 UNITEC Store\n💰 Valor: Kz {valor}\n📋 Ref: {referencia}\n🛒 Pedido: #{pedido}\n✅ Confirme o pagamento no Multicaixa Express\n📞 Suporte: (+244) 937 960 963");

define('SMS_MENSAGEM_CONFIRMACAO', "✅ UNITEC Store\n💰 Pagamento Confirmado!\n📋 Ref: {referencia}\n🛒 Pedido: #{pedido}\n📦 Seu pedido será processado em breve\n📞 Suporte: (+244) 937 960 963");

// ========================================
// FUNÇÕES AUXILIARES
// ========================================

function formatarMensagem($template, $dados) {
    $mensagem = $template;
    foreach ($dados as $chave => $valor) {
        $mensagem = str_replace('{' . $chave . '}', $valor, $mensagem);
    }
    return $mensagem;
}

function logSMS($numero, $mensagem, $status) {
    if (!SMS_LOG_ENABLED) return;
    
    $log_dir = dirname(SMS_LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log = date('Y-m-d H:i:s') . " | +244{$numero} | {$status} | {$mensagem}\n";
    file_put_contents(SMS_LOG_FILE, $log, FILE_APPEND);
}

function validarNumeroAngolano($numero) {
    // Remove caracteres não numéricos
    $numero = preg_replace('/[^0-9]/', '', $numero);
    
    // Verifica se tem 9 dígitos e começa com 9
    return strlen($numero) === 9 && substr($numero, 0, 1) === '9';
}

function formatarNumeroAngolano($numero) {
    $numero = preg_replace('/[^0-9]/', '', $numero);
    return '+244' . $numero;
}

// ========================================
// CONFIGURAÇÕES DE DESENVOLVIMENTO
// ========================================
// Nota: As configurações acima já estão definidas para desenvolvimento
// Se precisar alterar para produção, modifique os valores acima

// ========================================
// CONFIGURAÇÕES MULTICAIXA EXPRESS API
// ========================================

// Habilitar API real do Multicaixa Express (false = usar simulação)
define('MULTICAIXA_API_ENABLED', false);

// URL da API EMIS (Empresa Interbancária de Serviços)
define('MULTICAIXA_API_URL', 'https://api.emis.ao/v1');

// Credenciais do comerciante (obter junto ao banco)
define('MULTICAIXA_MERCHANT_ID', 'UNITEC001');
define('MULTICAIXA_API_KEY', 'your_multicaixa_api_key_here');
define('MULTICAIXA_TERMINAL_ID', 'MCX001');

// Configurações de callback
define('MULTICAIXA_CALLBACK_URL', 'https://unitec.ao/api/multicaixa_callback.php');
?> 