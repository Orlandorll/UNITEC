# 📱 Sistema de SMS - UNITEC Store

## 🎯 Funcionalidade

O sistema pode enviar **SMS reais** para o telefone do usuário durante o processo de pagamento Multicaixa Express, incluindo:

- **SMS de Notificação**: Quando o pagamento é solicitado
- **SMS de Confirmação**: Quando o pagamento é confirmado

## 🚀 Como Funciona

### **1. SMS de Notificação**
```
🏪 UNITEC Store
💰 Valor: Kz 25.000,00
📋 Ref: MCX20241201143022F8E9A1
🛒 Pedido: #12345
✅ Confirme o pagamento no Multicaixa Express
📞 Suporte: (+244) 937 960 963
```

### **2. SMS de Confirmação**
```
✅ UNITEC Store
💰 Pagamento Confirmado!
📋 Ref: MCX20241201143022F8E9A1
🛒 Pedido: #12345
📦 Seu pedido será processado em breve
📞 Suporte: (+244) 937 960 963
```

## ⚙️ Configuração

### **Opção 1: Twilio (Recomendado)**

1. **Criar conta no Twilio**:
   - Acesse: https://www.twilio.com
   - Crie uma conta gratuita
   - Obtenha Account SID e Auth Token

2. **Configurar no arquivo** `config/sms_config.php`:
```php
define('SMS_PROVIDER', 'twilio');
define('TWILIO_ACCOUNT_SID', 'sua_account_sid');
define('TWILIO_AUTH_TOKEN', 'seu_auth_token');
define('TWILIO_FROM_NUMBER', '+244937969636'); // Número da UNITEC
```

### **Opção 2: Africa's Talking**

1. **Criar conta no Africa's Talking**:
   - Acesse: https://africastalking.com
   - Crie uma conta
   - Obtenha API Key e Username

2. **Configurar no arquivo** `config/sms_config.php`:
```php
define('SMS_PROVIDER', 'africas_talking');
define('AFRICASTALKING_API_KEY', 'your_api_key_here');
define('AFRICASTALKING_USERNAME', 'your_username_here');
define('AFRICASTALKING_FROM', 'UNITEC');
```

### **Opção 3: Modo Desenvolvimento**

Para testes locais, use o modo simulação:
```php
define('SMS_PROVIDER', 'local');
define('SMS_ENABLED', true);
define('SMS_LOG_ENABLED', true);
```

## 📋 Passos para Ativar SMS Real

### **1. Escolher Provedor**
- **Twilio**: Melhor para produção, suporte global
- **Africa's Talking**: Especializado em África
- **Local**: Para desenvolvimento e testes

### **2. Configurar Credenciais**
Edite `config/sms_config.php`:
```php
// Para Twilio
define('SMS_PROVIDER', 'twilio');
define('TWILIO_ACCOUNT_SID', 'sua_account_sid');
define('TWILIO_AUTH_TOKEN', 'seu_auth_token');
define('TWILIO_FROM_NUMBER', '+244123456789');

// Para Africa's Talking
define('SMS_PROVIDER', 'africas_talking');
define('AFRICASTALKING_API_KEY', 'sua_api_key');
define('AFRICASTALKING_USERNAME', 'seu_username');
```

### **3. Testar Sistema**
1. Acesse o checkout
2. Selecione "Multicaixa Express"
3. Digite um número real
4. Clique "Enviar Notificação"
5. Verifique se recebeu o SMS

## 💰 Custos

### **Twilio**
- **Conta gratuita**: 15 SMS/mês
- **Pago**: ~$0.0075 por SMS
- **Cobertura**: Global

### **Africa's Talking**
- **Preços**: Variam por país
- **Angola**: ~$0.02 por SMS
- **Cobertura**: África

## 🔧 Personalização

### **Mensagens Personalizadas**
Edite em `config/sms_config.php`:
```php
define('SMS_MENSAGEM_PAGAMENTO', "🏪 UNITEC Store\n💰 Valor: Kz {valor}\n📋 Ref: {referencia}\n🛒 Pedido: #{pedido}\n✅ Confirme o pagamento no Multicaixa Express\n📞 Suporte: (+244) 937 960 963");

define('SMS_MENSAGEM_CONFIRMACAO', "✅ UNITEC Store\n💰 Pagamento Confirmado!\n📋 Ref: {referencia}\n🛒 Pedido: #{pedido}\n📦 Seu pedido será processado em breve\n📞 Suporte: (+244) 937 960 963");
```

### **Logs de SMS**
Os SMS são logados em `logs/sms_log.txt`:
```
2024-12-01 14:30:22 | +244937969636 | SUCCESS | 🏪 UNITEC Store...
```

## 🛡️ Segurança

### **Validação de Números**
- Apenas números angolanos (9 dígitos, começando com 9)
- Formato: +244 937 960 963

### **Rate Limiting**
- Máximo 3 SMS por número por hora
- Proteção contra spam

### **Logs de Segurança**
- Todos os SMS são logados
- Monitoramento de tentativas falhadas

## 🚨 Troubleshooting

### **SMS não enviado**
1. Verificar credenciais da API
2. Verificar saldo da conta
3. Verificar logs em `logs/sms_log.txt`
4. Testar com modo local

### **Erro de API**
1. Verificar conexão com internet
2. Verificar configurações da API
3. Verificar formato do número
4. Verificar limite de rate

### **Modo Desenvolvimento**
Se estiver testando localmente:
```php
define('SMS_PROVIDER', 'local');
define('SMS_ENABLED', true);
```

## 📞 Suporte

Para dúvidas sobre configuração de SMS:
- **Email**: unitec01@gmail.com
- **Telefone**: (+244) 937 969 636
- **Horário**: Segunda a Sexta, 8h às 18h

## ✅ Checklist de Ativação

- [ ] Escolher provedor de SMS
- [ ] Criar conta no provedor
- [ ] Obter credenciais da API
- [ ] Configurar `config/sms_config.php`
- [ ] Testar com número real
- [ ] Verificar logs de SMS
- [ ] Configurar monitoramento
- [ ] Ativar em produção

---

**Nota**: Em desenvolvimento, o sistema usa simulação. Para SMS reais, configure um provedor como Twilio ou Africa's Talking. 