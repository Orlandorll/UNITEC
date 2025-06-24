# Documentação da API Multicaixa Express

## Visão Geral

Este documento explica como configurar e usar a API real do Multicaixa Express no sistema UNITEC Store.

## O que é o Multicaixa Express?

O Multicaixa Express é o sistema de pagamentos móveis de Angola, operado pela EMIS (Empresa Interbancária de Serviços). Permite pagamentos instantâneos via aplicativo móvel.

## Configuração da API

### 1. Obter Credenciais

Para usar a API real, você precisa:

1. **Conta Comercial**: Abrir conta em um banco participante (BFA, BAI, BIC, Standard Bank)
2. **Registro EMIS**: Solicitar registro como comerciante na EMIS
3. **Credenciais API**: Obter Merchant ID, API Key e Terminal ID

### 2. Configurar o Sistema

Edite o arquivo `config/sms_config.php`:

```php
// Habilitar API real (true = API real, false = simulação)
define('MULTICAIXA_API_ENABLED', true);

// URL da API EMIS
define('MULTICAIXA_API_URL', 'https://api.emis.ao/v1');

// Suas credenciais (obter junto ao banco)
define('MULTICAIXA_MERCHANT_ID', 'SEU_MERCHANT_ID');
define('MULTICAIXA_API_KEY', 'SUA_API_KEY');
define('MULTICAIXA_TERMINAL_ID', 'SEU_TERMINAL_ID');

// URL de callback (deve ser pública)
define('MULTICAIXA_CALLBACK_URL', 'https://seudominio.ao/api/multicaixa_callback.php');
```

### 3. Configurar Callback

O callback deve ser acessível publicamente para receber notificações de pagamento.

## Funcionalidades da API

### 1. Gerar Referência de Pagamento

```php
// Exemplo de uso
$multicaixa = new MulticaixaAPI();
$resultado = $multicaixa->gerarReferencia(5000, 'PED123', 'Compra UNITEC Store');

if ($resultado['success']) {
    $referencia = $resultado['data']['reference'];
    echo "Referência gerada: $referencia";
}
```

**Parâmetros:**
- `valor`: Valor em Kwanzas (ex: 5000 = Kz 50,00)
- `pedido_id`: ID único do pedido
- `descricao`: Descrição do pagamento

**Resposta:**
```json
{
    "success": true,
    "data": {
        "reference": "MCX20241201123456ABC123",
        "amount": 5000,
        "currency": "AOA",
        "order_id": "PED123",
        "status": "pending",
        "expires_at": "2024-12-02 12:34:56",
        "merchant_id": "UNITEC001",
        "terminal_id": "MCX001"
    }
}
```

### 2. Verificar Status

```php
$resultado = $multicaixa->verificarStatus('MCX20241201123456ABC123');
```

**Status possíveis:**
- `pending`: Aguardando pagamento
- `processing`: Processando
- `completed`: Pago
- `failed`: Falhou
- `expired`: Expirado

### 3. Consultar Transação

```php
$resultado = $multicaixa->consultarTransacao('TXN123456');
```

### 4. Cancelar Referência

```php
$resultado = $multicaixa->cancelarReferencia('MCX20241201123456ABC123');
```

## Callback de Pagamento

O sistema recebe notificações automáticas quando o status do pagamento muda.

### Estrutura do Callback

```json
{
    "reference": "MCX20241201123456ABC123",
    "status": "completed",
    "amount": 5000,
    "order_id": "PED123",
    "transaction_id": "TXN123456",
    "bank": "BFA",
    "completed_at": "2024-12-01 12:34:56"
}
```

### Segurança

O callback verifica a assinatura da requisição para garantir autenticidade:

```php
$signature = $_SERVER['HTTP_X_MULTICAIXA_SIGNATURE'];
$expected_signature = hash_hmac('sha256', $payload, MULTICAIXA_API_KEY);

if (!hash_equals($signature, $expected_signature)) {
    // Assinatura inválida
    http_response_code(401);
    exit;
}
```

## Integração no Checkout

### 1. Página de Checkout

O checkout já está configurado para usar a API quando habilitada:

```javascript
// Gerar referência via API
async function gerarReferenciaAPI() {
    const formData = new FormData();
    formData.append('action', 'gerar_referencia');
    formData.append('valor', valor);
    formData.append('pedido_id', pedidoId);
    
    const response = await fetch('api/multicaixa_api.php', {
        method: 'POST',
        body: formData
    });
    
    const resultado = await response.json();
    // Processar resultado...
}
```

### 2. Verificação Automática

O sistema verifica automaticamente o status a cada 10 segundos:

```javascript
function iniciarVerificacaoAutomatica(referencia) {
    const interval = setInterval(async () => {
        const resultado = await verificarStatusAPI(referencia);
        if (resultado.status === 'completed') {
            clearInterval(interval);
            // Redirecionar para confirmação
        }
    }, 10000);
}
```

## Simulação vs API Real

### Modo Simulação (Desenvolvimento)

```php
define('MULTICAIXA_API_ENABLED', false);
```

- Usado para desenvolvimento e testes
- Gera referências fictícias
- Simula mudanças de status
- Não requer credenciais reais

### Modo API Real (Produção)

```php
define('MULTICAIXA_API_ENABLED', true);
```

- Conecta com a API real da EMIS
- Gera referências válidas
- Recebe notificações reais
- Requer credenciais válidas

## Logs e Monitoramento

### Logs de Callback

Os callbacks são registrados em `api/logs/multicaixa_callback.log`:

```
[2024-12-01 12:34:56] Callback recebido: {"reference":"MCX20241201123456ABC123","status":"completed"}
[2024-12-01 12:34:57] Pedido atualizado com sucesso: ID=PED123, Status=pago
```

### Logs de API

As requisições para a API são registradas automaticamente.

## Tratamento de Erros

### Erros Comuns

1. **Credenciais inválidas**
   ```json
   {"success": false, "error": "Unauthorized", "code": 401}
   ```

2. **Referência não encontrada**
   ```json
   {"success": false, "error": "Reference not found", "code": 404}
   ```

3. **Erro de conexão**
   ```json
   {"success": false, "error": "Connection timeout", "code": "CONNECTION_ERROR"}
   ```

### Fallback para Simulação

Se a API real falhar, o sistema automaticamente usa a simulação:

```php
if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED) {
    $resultado = $multicaixa->gerarReferencia($valor, $pedido_id);
} else {
    $resultado = $multicaixa->simularAPI('gerar_referencia', $data);
}
```

## Testes

### Teste Local

1. Configure `MULTICAIXA_API_ENABLED = false`
2. Acesse `multicaixa_simulator.php`
3. Teste todas as funcionalidades

### Teste com API Real

1. Configure credenciais válidas
2. Configure `MULTICAIXA_API_ENABLED = true`
3. Teste com valores pequenos
4. Verifique logs de callback

## Segurança

### Boas Práticas

1. **Nunca exponha credenciais** em código público
2. **Use HTTPS** para todas as comunicações
3. **Valide assinaturas** de callback
4. **Monitore logs** regularmente
5. **Teste em ambiente isolado** primeiro

### Configuração de Segurança

```php
// Validar IPs permitidos (opcional)
$allowed_ips = ['192.168.1.1', '10.0.0.1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    http_response_code(403);
    exit;
}
```

## Suporte

### Contatos Úteis

- **EMIS**: https://www.emis.ao
- **BFA**: https://www.bfa.ao
- **BAI**: https://www.bancobai.ao
- **BIC**: https://www.bic.ao

### Documentação Oficial

Consulte a documentação oficial da EMIS para detalhes técnicos específicos da API.

## Conclusão

A integração com a API real do Multicaixa Express permite processar pagamentos reais em Angola. O sistema está preparado para funcionar tanto em modo simulação (desenvolvimento) quanto com a API real (produção).

Para ativar a API real, configure as credenciais e defina `MULTICAIXA_API_ENABLED = true`. 