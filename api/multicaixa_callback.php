<?php
header('Content-Type: application/json');

// Log de callbacks
$log_file = 'logs/multicaixa_callback.log';
$log_dir = dirname($log_file);

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function logCallback($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Log da requisição recebida
logCallback("Callback recebido: " . json_encode($_POST));

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logCallback("Erro: Método não permitido - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Carregar configurações - corrigir caminho
$config_path = __DIR__ . '/../config/sms_config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    // Fallback para quando chamado de fora da pasta api
    $config_path = 'config/sms_config.php';
    if (file_exists($config_path)) {
        require_once $config_path;
    } else {
        die(json_encode(['error' => 'Arquivo de configuração não encontrado']));
    }
}

$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    require_once $database_path;
} else {
    // Fallback para quando chamado de fora da pasta api
    $database_path = 'config/database.php';
    if (file_exists($database_path)) {
        require_once $database_path;
    } else {
        die(json_encode(['error' => 'Arquivo de banco de dados não encontrado']));
    }
}

try {
    // Verificar assinatura da requisição (segurança)
    $signature = $_SERVER['HTTP_X_MULTICAIXA_SIGNATURE'] ?? '';
    $payload = file_get_contents('php://input');
    
    // Verificar se a assinatura é válida (implementar conforme documentação da API)
    if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
        $expected_signature = hash_hmac('sha256', $payload, MULTICAIXA_API_KEY);
        if (!hash_equals($signature, $expected_signature)) {
            logCallback("Erro: Assinatura inválida");
            http_response_code(401);
            echo json_encode(['error' => 'Assinatura inválida']);
            exit;
        }
    }
    
    // Processar dados do callback
    $reference = $_POST['reference'] ?? '';
    $status = $_POST['status'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $order_id = $_POST['order_id'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    $bank = $_POST['bank'] ?? '';
    $completed_at = $_POST['completed_at'] ?? '';
    
    logCallback("Processando: Reference=$reference, Status=$status, Order=$order_id");
    
    if (empty($reference) || empty($status)) {
        logCallback("Erro: Dados obrigatórios ausentes");
        http_response_code(400);
        echo json_encode(['error' => 'Dados obrigatórios ausentes']);
        exit;
    }
    
    // Atualizar status do pedido no banco de dados
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar pedido
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$order_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        logCallback("Erro: Pedido não encontrado - ID: $order_id");
        http_response_code(404);
        echo json_encode(['error' => 'Pedido não encontrado']);
        exit;
    }
    
    // Atualizar status do pedido
    $novo_status = '';
    switch ($status) {
        case 'completed':
            $novo_status = 'pago';
            break;
        case 'failed':
            $novo_status = 'cancelado';
            break;
        case 'expired':
            $novo_status = 'expirado';
            break;
        default:
            $novo_status = 'pendente';
    }
    
    // Atualizar pedido
    $stmt = $conn->prepare("
        UPDATE pedidos 
        SET status = ?, 
            metodo_pagamento = 'Multicaixa Express',
            referencia_pagamento = ?,
            data_pagamento = ?,
            observacoes = CONCAT(IFNULL(observacoes, ''), ' | Pagamento via Multicaixa Express - Ref: ', ?)
        WHERE id = ?
    ");
    
    $data_pagamento = $status === 'completed' ? date('Y-m-d H:i:s') : null;
    $observacoes = "Banco: $bank, Transaction ID: $transaction_id";
    
    $stmt->execute([
        $novo_status,
        $reference,
        $data_pagamento,
        $observacoes,
        $order_id
    ]);
    
    // Se pagamento confirmado, enviar SMS de confirmação
    if ($status === 'completed') {
        // Buscar dados do cliente
        $stmt = $conn->prepare("SELECT u.nome, u.telefone FROM usuarios u JOIN pedidos p ON u.id = p.usuario_id WHERE p.id = ?");
        $stmt->execute([$order_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cliente && $cliente['telefone']) {
            // Enviar SMS de confirmação
            require_once 'sms_service.php';
            $sms = new SMSService();
            
            $mensagem = "Olá {$cliente['nome']}! Seu pagamento de Kz " . number_format($amount/100, 2, ',', '.') . " foi confirmado. Pedido #$order_id. Obrigado por escolher UNITEC Store!";
            
            $resultado_sms = $sms->enviarSMS($cliente['telefone'], $mensagem);
            logCallback("SMS enviado: " . json_encode($resultado_sms));
        }
    }
    
    logCallback("Pedido atualizado com sucesso: ID=$order_id, Status=$novo_status");
    
    // Responder com sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Callback processado com sucesso',
        'order_id' => $order_id,
        'status' => $novo_status
    ]);
    
} catch (Exception $e) {
    logCallback("Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 