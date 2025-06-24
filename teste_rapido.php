<?php
echo "<h1>Teste Rápido - Verificação de Caminhos</h1>";

// Teste 1: Verificar se o arquivo de configuração existe
echo "<h2>1. Verificando arquivo de configuração</h2>";
$config_path = 'config/sms_config.php';
if (file_exists($config_path)) {
    echo "✅ Arquivo de configuração encontrado: $config_path<br>";
    
    // Tentar carregar
    try {
        require_once $config_path;
        echo "✅ Arquivo de configuração carregado com sucesso<br>";
        
        // Verificar se as constantes estão definidas
        if (defined('SMS_PROVIDER')) {
            echo "✅ SMS_PROVIDER definido: " . SMS_PROVIDER . "<br>";
        }
        if (defined('MULTICAIXA_API_ENABLED')) {
            echo "✅ MULTICAIXA_API_ENABLED definido: " . (MULTICAIXA_API_ENABLED ? 'true' : 'false') . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao carregar configuração: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Arquivo de configuração não encontrado: $config_path<br>";
}

// Teste 2: Verificar se o arquivo da API existe
echo "<h2>2. Verificando arquivo da API</h2>";
$api_path = 'api/multicaixa_notification.php';
if (file_exists($api_path)) {
    echo "✅ Arquivo da API encontrado: $api_path<br>";
    
    // Tentar carregar
    try {
        require_once $api_path;
        echo "✅ Arquivo da API carregado com sucesso<br>";
        
        // Verificar se a classe existe
        if (class_exists('MulticaixaNotificationService')) {
            echo "✅ Classe MulticaixaNotificationService encontrada<br>";
            
            // Testar instanciação
            $multicaixa = new MulticaixaNotificationService();
            echo "✅ Instância criada com sucesso<br>";
        } else {
            echo "❌ Classe MulticaixaNotificationService não encontrada<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao carregar API: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Arquivo da API não encontrado: $api_path<br>";
}

// Teste 3: Verificar estrutura de pastas
echo "<h2>3. Verificando estrutura de pastas</h2>";
$pastas = ['config', 'api', 'logs'];
foreach ($pastas as $pasta) {
    if (is_dir($pasta)) {
        echo "✅ Pasta $pasta existe<br>";
    } else {
        echo "❌ Pasta $pasta não existe<br>";
    }
}

// Teste 4: Verificar arquivos importantes
echo "<h2>4. Verificando arquivos importantes</h2>";
$arquivos = [
    'config/sms_config.php',
    'config/database.php',
    'api/multicaixa_notification.php',
    'api/sms_service.php',
    'api/multicaixa_api.php',
    'teste_notificacao_multicaixa.php'
];

foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo existe<br>";
    } else {
        echo "❌ $arquivo não existe<br>";
    }
}

echo "<h2>5. Teste de Funcionamento</h2>";
echo "<p>Se todos os testes acima passaram, você pode acessar:</p>";
echo "<ul>";
echo "<li><a href='teste_notificacao_multicaixa.php'>teste_notificacao_multicaixa.php</a> - Teste completo de notificações</li>";
echo "<li><a href='multicaixa_simulator.php'>multicaixa_simulator.php</a> - Simulador do Multicaixa Express</li>";
echo "<li><a href='teste_sms.php'>teste_sms.php</a> - Teste do serviço de SMS</li>";
echo "</ul>";

echo "<p><strong>Status:</strong> ";
if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
    echo "🟢 Modo API Real habilitado";
} else {
    echo "🟡 Modo Simulação (desenvolvimento)";
}
echo "</p>";
?> 