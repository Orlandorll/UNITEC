<?php
// Verificar se já foi enviado output
if (!headers_sent()) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Simular delay de rede
usleep(rand(500000, 1500000)); // 0.5 a 1.5 segundos

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'gerar_referencia':
        gerarReferencia();
        break;
    case 'validar_pagamento':
        validarPagamento();
        break;
    case 'confirmar_pagamento':
        confirmarPagamento();
        break;
    case 'status_pagamento':
        statusPagamento();
        break;
    default:
        echo json_encode(['error' => 'Ação não reconhecida']);
}

function gerarReferencia() {
    $valor = $_POST['valor'] ?? 0;
    $pedido_id = $_POST['pedido_id'] ?? '';
    
    if (!$valor || !$pedido_id) {
        echo json_encode(['error' => 'Valor e ID do pedido são obrigatórios']);
        return;
    }
    
    // Gerar referência no formato real do Multicaixa
    $data = date('Ymd');
    $hora = date('His');
    $referencia = 'MCX' . $data . $hora . strtoupper(substr(md5(uniqid()), 0, 6));
    
    // Simular dados da transação
    $transacao = [
        'referencia' => $referencia,
        'valor' => number_format($valor, 2, '.', ''),
        'pedido_id' => $pedido_id,
        'data_geracao' => date('Y-m-d H:i:s'),
        'validade' => date('Y-m-d H:i:s', strtotime('+24 hours')),
        'status' => 'pendente',
        'banco_emissor' => 'EMIS',
        'terminal_id' => 'MCX' . rand(1000, 9999),
        'codigo_verificacao' => strtoupper(substr(md5($referencia), 0, 4))
    ];
    
    // Simular armazenamento (em produção seria no banco de dados)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['multicaixa_transacoes'][$referencia] = $transacao;
    
    echo json_encode([
        'success' => true,
        'data' => $transacao,
        'mensagem' => 'Referência Multicaixa Express gerada com sucesso'
    ]);
}

function validarPagamento() {
    $referencia = $_POST['referencia'] ?? '';
    
    if (!$referencia) {
        echo json_encode(['error' => 'Referência é obrigatória']);
        return;
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $transacao = $_SESSION['multicaixa_transacoes'][$referencia] ?? null;
    
    if (!$transacao) {
        echo json_encode(['error' => 'Referência não encontrada']);
        return;
    }
    
    // Simular verificação no sistema EMIS
    $status_possiveis = ['pendente', 'processando', 'confirmado', 'expirado'];
    $status_atual = $transacao['status'];
    
    // Simular mudança de status (em produção seria verificação real)
    if ($status_atual === 'pendente') {
        $chance_confirmacao = rand(1, 10);
        if ($chance_confirmacao <= 7) { // 70% de chance de confirmação
            $transacao['status'] = 'confirmado';
            $transacao['data_confirmacao'] = date('Y-m-d H:i:s');
            $transacao['banco_pagamento'] = ['BFA', 'BAI', 'BIC', 'Standard Bank', 'Millennium'][rand(0, 4)];
            $transacao['terminal_pagamento'] = 'MCX' . rand(1000, 9999);
        } else {
            $transacao['status'] = 'processando';
        }
    }
    
    $_SESSION['multicaixa_transacoes'][$referencia] = $transacao;
    
    echo json_encode([
        'success' => true,
        'data' => $transacao,
        'mensagem' => 'Status da transação verificado'
    ]);
}

function confirmarPagamento() {
    $referencia = $_POST['referencia'] ?? '';
    $codigo_verificacao = $_POST['codigo_verificacao'] ?? '';
    
    if (!$referencia || !$codigo_verificacao) {
        echo json_encode(['error' => 'Referência e código de verificação são obrigatórios']);
        return;
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $transacao = $_SESSION['multicaixa_transacoes'][$referencia] ?? null;
    
    if (!$transacao) {
        echo json_encode(['error' => 'Referência não encontrada']);
        return;
    }
    
    if ($transacao['codigo_verificacao'] !== strtoupper($codigo_verificacao)) {
        echo json_encode(['error' => 'Código de verificação inválido']);
        return;
    }
    
    // Simular confirmação manual
    $transacao['status'] = 'confirmado';
    $transacao['data_confirmacao'] = date('Y-m-d H:i:s');
    $transacao['confirmado_por'] = 'cliente';
    
    $_SESSION['multicaixa_transacoes'][$referencia] = $transacao;
    
    echo json_encode([
        'success' => true,
        'data' => $transacao,
        'mensagem' => 'Pagamento confirmado com sucesso'
    ]);
}

function statusPagamento() {
    $referencia = $_POST['referencia'] ?? $_GET['referencia'] ?? '';
    
    if (!$referencia) {
        echo json_encode(['error' => 'Referência é obrigatória']);
        return;
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $transacao = $_SESSION['multicaixa_transacoes'][$referencia] ?? null;
    
    if (!$transacao) {
        echo json_encode(['error' => 'Referência não encontrada']);
        return;
    }
    
    // Verificar se expirou
    if (strtotime($transacao['validade']) < time() && $transacao['status'] === 'pendente') {
        $transacao['status'] = 'expirado';
        $_SESSION['multicaixa_transacoes'][$referencia] = $transacao;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $transacao,
        'mensagem' => 'Status da transação consultado'
    ]);
}
?> 