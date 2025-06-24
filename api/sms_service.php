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

// Classe principal de SMS
class SMSService {
    
    public function enviarSMS($numero, $mensagem) {
        if (!SMS_ENABLED) {
            return ['success' => false, 'message' => 'SMS desabilitado'];
        }
        
        // Validar número
        if (!validarNumeroAngolano($numero)) {
            return ['success' => false, 'message' => 'Número inválido'];
        }
        
        switch (SMS_PROVIDER) {
            case 'twilio':
                return $this->enviarViaTwilio($numero, $mensagem);
            case 'africas_talking':
                return $this->enviarViaAfricasTalking($numero, $mensagem);
            case 'local':
            default:
                return $this->enviarViaAPILocal($numero, $mensagem);
        }
    }

    private function enviarViaTwilio($numero, $mensagem) {
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';
        
        $data = [
            'From' => TWILIO_FROM_NUMBER,
            'To' => formatarNumeroAngolano($numero),
            'Body' => $mensagem
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode(TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN),
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) {
            logSMS($numero, $mensagem, 'SUCCESS');
            return ['success' => true, 'message' => 'SMS enviado com sucesso via Twilio'];
        } else {
            logSMS($numero, $mensagem, 'ERROR: ' . $response);
            return ['success' => false, 'message' => 'Erro Twilio: ' . $response];
        }
    }

    private function enviarViaAfricasTalking($numero, $mensagem) {
        $url = 'https://api.africastalking.com/version1/messaging';
        
        $data = [
            'username' => AFRICASTALKING_USERNAME,
            'to' => formatarNumeroAngolano($numero),
            'message' => $mensagem,
            'from' => AFRICASTALKING_FROM
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apiKey: ' . AFRICASTALKING_API_KEY,
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) {
            logSMS($numero, $mensagem, 'SUCCESS');
            return ['success' => true, 'message' => 'SMS enviado com sucesso via Africa\'s Talking'];
        } else {
            logSMS($numero, $mensagem, 'ERROR: ' . $response);
            return ['success' => false, 'message' => 'Erro Africa\'s Talking: ' . $response];
        }
    }

    private function enviarViaAPILocal($numero, $mensagem) {
        // Simulação para desenvolvimento
        usleep(rand(1000000, 3000000)); // 1-3 segundos
        
        // Sempre simular sucesso em desenvolvimento
        $sucesso = true;
        
        if ($sucesso) {
            logSMS($numero, $mensagem, 'SUCCESS (SIMULATED)');
            return [
                'success' => true, 
                'message' => 'SMS simulado com sucesso (modo desenvolvimento)',
                'numero' => $numero,
                'mensagem' => $mensagem,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } else {
            logSMS($numero, $mensagem, 'ERROR (SIMULATED)');
            return ['success' => false, 'message' => 'Erro simulado no envio'];
        }
    }
}

// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'enviar_sms') {
        $numero = $_POST['numero'] ?? '';
        $valor = $_POST['valor'] ?? '';
        $referencia = $_POST['referencia'] ?? '';
        $pedido_id = $_POST['pedido_id'] ?? '';
        
        if (!$numero || !$valor) {
            echo json_encode(['error' => 'Número e valor são obrigatórios']);
            exit;
        }
        
        // Criar mensagem personalizada
        $dados = [
            'valor' => number_format($valor, 2, ',', '.'),
            'referencia' => $referencia,
            'pedido' => $pedido_id
        ];
        
        $mensagem = formatarMensagem(SMS_MENSAGEM_PAGAMENTO, $dados);
        
        $sms = new SMSService();
        $resultado = $sms->enviarSMS($numero, $mensagem);
        
        echo json_encode($resultado);
    } elseif ($action === 'confirmar_sms') {
        $numero = $_POST['numero'] ?? '';
        $referencia = $_POST['referencia'] ?? '';
        $pedido_id = $_POST['pedido_id'] ?? '';
        
        // Mensagem de confirmação
        $dados = [
            'referencia' => $referencia,
            'pedido' => $pedido_id
        ];
        
        $mensagem = formatarMensagem(SMS_MENSAGEM_CONFIRMACAO, $dados);
        
        $sms = new SMSService();
        $resultado = $sms->enviarSMS($numero, $mensagem);
        
        echo json_encode($resultado);
    } else {
        echo json_encode(['error' => 'Ação não reconhecida']);
    }
} else {
    echo json_encode(['error' => 'Método não permitido']);
}
?> 