<?php
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

class MulticaixaNotificationService {
    private $api_url = 'https://api.emis.ao/v1';
    private $merchant_id = 'UNITEC001';
    private $api_key = 'your_multicaixa_api_key';
    private $terminal_id = 'MCX001';
    
    public function __construct() {
        // Carregar configurações do arquivo de config
        $this->api_url = defined('MULTICAIXA_API_URL') ? MULTICAIXA_API_URL : $this->api_url;
        $this->merchant_id = defined('MULTICAIXA_MERCHANT_ID') ? MULTICAIXA_MERCHANT_ID : $this->merchant_id;
        $this->api_key = defined('MULTICAIXA_API_KEY') ? MULTICAIXA_API_KEY : $this->api_key;
        $this->terminal_id = defined('MULTICAIXA_TERMINAL_ID') ? MULTICAIXA_TERMINAL_ID : $this->terminal_id;
    }

    /**
     * Enviar notificação real para Multicaixa Express
     */
    public function enviarNotificacao($numero, $valor, $referencia, $pedido_id) {
        $endpoint = '/notifications/send';
        
        $data = [
            'merchant_id' => $this->merchant_id,
            'terminal_id' => $this->terminal_id,
            'phone_number' => '+244' . $numero,
            'amount' => $valor * 100, // Valor em centavos
            'reference' => $referencia,
            'order_id' => $pedido_id,
            'description' => "Compra UNITEC Store - Pedido #{$pedido_id}",
            'expiry_minutes' => 30,
            'notification_type' => 'payment_request'
        ];

        return $this->makeRequest('POST', $endpoint, $data);
    }

    /**
     * Verificar status da notificação
     */
    public function verificarStatusNotificacao($notification_id) {
        $endpoint = "/notifications/status/{$notification_id}";
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Cancelar notificação
     */
    public function cancelarNotificacao($notification_id) {
        $endpoint = "/notifications/cancel/{$notification_id}";
        return $this->makeRequest('POST', $endpoint);
    }

    /**
     * Fazer requisição para a API
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->api_url . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: UNITEC-Store/1.0'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $error,
                'code' => 'CONNECTION_ERROR'
            ];
        }

        $response_data = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'data' => $response_data,
                'code' => $http_code
            ];
        } else {
            return [
                'success' => false,
                'error' => $response_data['message'] ?? 'Erro na API',
                'code' => $http_code,
                'response' => $response_data
            ];
        }
    }

    /**
     * Simular notificação para desenvolvimento
     */
    public function simularNotificacao($numero, $valor, $referencia, $pedido_id) {
        // Simular delay de rede
        usleep(rand(2000000, 4000000)); // 2-4 segundos
        
        // Gerar ID de notificação fictício
        $notification_id = 'NOT' . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
        
        $notificacao = [
            'notification_id' => $notification_id,
            'phone_number' => '+244' . $numero,
            'amount' => $valor,
            'reference' => $referencia,
            'order_id' => $pedido_id,
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            'merchant_id' => $this->merchant_id,
            'terminal_id' => $this->terminal_id
        ];

        // Salvar na sessão para simulação
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['multicaixa_notifications'][$notification_id] = $notificacao;

        // Log da simulação
        $log_message = date('Y-m-d H:i:s') . " | SIMULATED | +244{$numero} | Kz {$valor} | Ref: {$referencia} | Status: sent\n";
        file_put_contents(__DIR__ . '/logs/multicaixa_notifications.log', $log_message, FILE_APPEND | LOCK_EX);

        return [
            'success' => true,
            'data' => $notificacao,
            'message' => 'Notificação simulada enviada com sucesso'
        ];
    }

    /**
     * Verificar status simulado
     */
    public function simularVerificarStatus($notification_id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $notificacao = $_SESSION['multicaixa_notifications'][$notification_id] ?? null;
        
        if (!$notificacao) {
            return ['success' => false, 'error' => 'Notificação não encontrada'];
        }

        // Simular mudança de status
        if ($notificacao['status'] === 'sent') {
            $chance = rand(1, 10);
            if ($chance <= 6) { // 60% de chance de confirmação
                $notificacao['status'] = 'confirmed';
                $notificacao['confirmed_at'] = date('Y-m-d H:i:s');
                $notificacao['bank'] = ['BFA', 'BAI', 'BIC', 'Standard Bank'][rand(0, 3)];
            } elseif ($chance <= 8) {
                $notificacao['status'] = 'pending';
            } else {
                $notificacao['status'] = 'expired';
            }
            
            $_SESSION['multicaixa_notifications'][$notification_id] = $notificacao;
        }

        return [
            'success' => true,
            'data' => $notificacao,
            'message' => 'Status verificado'
        ];
    }
}

// Processar requisições apenas se for chamado diretamente
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $multicaixa = new MulticaixaNotificationService();
        
        switch ($action) {
            case 'enviar_notificacao':
                $numero = $_POST['numero'] ?? '';
                $valor = $_POST['valor'] ?? 0;
                $referencia = $_POST['referencia'] ?? '';
                $pedido_id = $_POST['pedido_id'] ?? '';
                
                if (!$numero || !$valor || !$referencia) {
                    echo json_encode(['error' => 'Dados obrigatórios ausentes']);
                    exit;
                }
                
                if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
                    // Usar API real
                    $resultado = $multicaixa->enviarNotificacao($numero, $valor, $referencia, $pedido_id);
                } else {
                    // Usar simulação
                    $resultado = $multicaixa->simularNotificacao($numero, $valor, $referencia, $pedido_id);
                }
                
                echo json_encode($resultado);
                break;
                
            case 'verificar_status':
                $notification_id = $_POST['notification_id'] ?? '';
                
                if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
                    $resultado = $multicaixa->verificarStatusNotificacao($notification_id);
                } else {
                    $resultado = $multicaixa->simularVerificarStatus($notification_id);
                }
                
                echo json_encode($resultado);
                break;
                
            default:
                echo json_encode(['error' => 'Ação não reconhecida']);
        }
    } else {
        echo json_encode(['error' => 'Método não permitido']);
    }
}
?> 