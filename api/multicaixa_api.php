<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

// Configurações da API Multicaixa Express
class MulticaixaAPI {
    private $api_url = 'https://api.emis.ao/v1'; // URL da API EMIS (exemplo)
    private $merchant_id = 'UNITEC001'; // ID do comerciante
    private $api_key = 'your_multicaixa_api_key'; // Chave da API
    private $terminal_id = 'MCX001'; // ID do terminal
    
    public function __construct() {
        // Carregar configurações do arquivo de config
        $this->api_url = defined('MULTICAIXA_API_URL') ? MULTICAIXA_API_URL : $this->api_url;
        $this->merchant_id = defined('MULTICAIXA_MERCHANT_ID') ? MULTICAIXA_MERCHANT_ID : $this->merchant_id;
        $this->api_key = defined('MULTICAIXA_API_KEY') ? MULTICAIXA_API_KEY : $this->api_key;
        $this->terminal_id = defined('MULTICAIXA_TERMINAL_ID') ? MULTICAIXA_TERMINAL_ID : $this->terminal_id;
    }

    /**
     * Gerar referência de pagamento via API Multicaixa
     */
    public function gerarReferencia($valor, $pedido_id, $descricao = '') {
        $endpoint = '/payments/reference';
        
        $data = [
            'merchant_id' => $this->merchant_id,
            'terminal_id' => $this->terminal_id,
            'amount' => $valor * 100, // Valor em centavos
            'currency' => 'AOA',
            'order_id' => $pedido_id,
            'description' => $descricao ?: "Compra UNITEC Store - Pedido #{$pedido_id}",
            'expiry_hours' => 24,
            'callback_url' => 'https://unitec.ao/api/multicaixa_callback.php'
        ];

        return $this->makeRequest('POST', $endpoint, $data);
    }

    /**
     * Verificar status de uma referência
     */
    public function verificarStatus($referencia) {
        $endpoint = "/payments/status/{$referencia}";
        
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Consultar transação por ID
     */
    public function consultarTransacao($transaction_id) {
        $endpoint = "/transactions/{$transaction_id}";
        
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Cancelar referência
     */
    public function cancelarReferencia($referencia) {
        $endpoint = "/payments/cancel/{$referencia}";
        
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
     * Simular API para desenvolvimento
     */
    public function simularAPI($action, $data = []) {
        // Simular delay de rede
        usleep(rand(500000, 2000000)); // 0.5 a 2 segundos
        
        switch ($action) {
            case 'gerar_referencia':
                return $this->simularGerarReferencia($data);
            case 'verificar_status':
                return $this->simularVerificarStatus($data);
            case 'consultar_transacao':
                return $this->simularConsultarTransacao($data);
            default:
                return ['success' => false, 'error' => 'Ação não reconhecida'];
        }
    }

    private function simularGerarReferencia($data) {
        $valor = $data['amount'] ?? 0;
        $pedido_id = $data['order_id'] ?? '';
        
        // Gerar referência no formato real do Multicaixa
        $data_ref = date('Ymd');
        $hora_ref = date('His');
        $referencia = 'MCX' . $data_ref . $hora_ref . strtoupper(substr(md5(uniqid()), 0, 6));
        
        $transacao = [
            'reference' => $referencia,
            'amount' => $valor,
            'currency' => 'AOA',
            'order_id' => $pedido_id,
            'status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'created_at' => date('Y-m-d H:i:s'),
            'merchant_id' => $this->merchant_id,
            'terminal_id' => $this->terminal_id
        ];

        // Salvar na sessão para simulação
        session_start();
        $_SESSION['multicaixa_transacoes'][$referencia] = $transacao;

        return [
            'success' => true,
            'data' => $transacao,
            'message' => 'Referência gerada com sucesso'
        ];
    }

    private function simularVerificarStatus($data) {
        $referencia = $data['reference'] ?? '';
        
        session_start();
        $transacao = $_SESSION['multicaixa_transacoes'][$referencia] ?? null;
        
        if (!$transacao) {
            return ['success' => false, 'error' => 'Referência não encontrada'];
        }

        // Simular mudança de status
        if ($transacao['status'] === 'pending') {
            $chance = rand(1, 10);
            if ($chance <= 7) { // 70% de chance de confirmação
                $transacao['status'] = 'completed';
                $transacao['completed_at'] = date('Y-m-d H:i:s');
                $transacao['bank'] = ['BFA', 'BAI', 'BIC', 'Standard Bank'][rand(0, 3)];
            } elseif ($chance <= 9) {
                $transacao['status'] = 'processing';
            } else {
                $transacao['status'] = 'expired';
            }
            
            $_SESSION['multicaixa_transacoes'][$referencia] = $transacao;
        }

        return [
            'success' => true,
            'data' => $transacao,
            'message' => 'Status verificado'
        ];
    }

    private function simularConsultarTransacao($data) {
        $transaction_id = $data['transaction_id'] ?? '';
        
        // Simular dados da transação
        $transacao = [
            'transaction_id' => $transaction_id,
            'reference' => 'MCX' . date('YmdHis') . strtoupper(substr(md5($transaction_id), 0, 6)),
            'amount' => rand(1000, 50000),
            'status' => 'completed',
            'bank' => 'BFA',
            'completed_at' => date('Y-m-d H:i:s'),
            'merchant_id' => $this->merchant_id
        ];

        return [
            'success' => true,
            'data' => $transacao,
            'message' => 'Transação consultada'
        ];
    }
}

// Processar requisições
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $multicaixa = new MulticaixaAPI();
    
    switch ($action) {
        case 'gerar_referencia':
            $valor = $_POST['valor'] ?? 0;
            $pedido_id = $_POST['pedido_id'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            
            if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
                // Usar API real
                $resultado = $multicaixa->gerarReferencia($valor, $pedido_id, $descricao);
            } else {
                // Usar simulação
                $resultado = $multicaixa->simularAPI('gerar_referencia', [
                    'amount' => $valor,
                    'order_id' => $pedido_id
                ]);
            }
            
            echo json_encode($resultado);
            break;
            
        case 'verificar_status':
            $referencia = $_POST['referencia'] ?? '';
            
            if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
                $resultado = $multicaixa->verificarStatus($referencia);
            } else {
                $resultado = $multicaixa->simularAPI('verificar_status', [
                    'reference' => $referencia
                ]);
            }
            
            echo json_encode($resultado);
            break;
            
        case 'consultar_transacao':
            $transaction_id = $_POST['transaction_id'] ?? '';
            
            if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
                $resultado = $multicaixa->consultarTransacao($transaction_id);
            } else {
                $resultado = $multicaixa->simularAPI('consultar_transacao', [
                    'transaction_id' => $transaction_id
                ]);
            }
            
            echo json_encode($resultado);
            break;
            
        default:
            echo json_encode(['error' => 'Ação não reconhecida']);
    }
} else {
    echo json_encode(['error' => 'Método não permitido']);
}
?> 