# 📋 Documentação - Simulação Multicaixa Express

## 🏦 O que é o Multicaixa Express?

O **Multicaixa Express** é o sistema de pagamento eletrônico mais usado em Angola, operado pela **EMIS** (Empresa Interbancária de Serviços). Permite pagamentos em terminais POS, internet banking e aplicações móveis.

## 🔄 Processo Completo da Simulação

### **1. Início no Checkout**
```
✅ Usuário seleciona "Multicaixa Express" no checkout
✅ Clica em "Finalizar Pedido"
✅ É redirecionado para a simulação
```

### **2. Geração da Referência**
```
✅ Sistema gera referência única automaticamente
✅ Formato: MCX20241201143022F8E9A1
✅ QR Code é gerado para pagamento móvel
✅ Instruções detalhadas são exibidas
```

### **3. Pagamento Real**
```
✅ Cliente vai ao banco/terminal/aplicativo
✅ Informa a referência gerada
✅ Confirma o valor
✅ Faz o pagamento real
```

### **4. Verificação e Confirmação**
```
✅ Sistema verifica automaticamente a cada 10 segundos
✅ Ou cliente confirma manualmente com código
✅ Status muda para "Confirmado"
✅ Redireciona para página de sucesso
```

## 📍 Onde Usar a Referência

### **🏦 Bancos Físicos**
- **BFA** - Banco de Fomento Angola
- **BAI** - Banco Angolano de Investimentos  
- **BIC** - Banco BIC
- **Standard Bank**
- **Millennium**

**Como pagar no banco:**
1. Vá ao balcão de atendimento
2. Diga: "Quero pagar uma referência Multicaixa"
3. Informe a referência: `MCX20241201143022F8E9A1`
4. Confirme o valor: `Kz 25.000,00`
5. Pague em dinheiro ou com cartão

### **🏧 Terminais Multicaixa**
- Shoppings e Supermercados
- Postos de Combustível
- Farmácias
- Lojas de Conveniência

**Como pagar no terminal:**
1. Selecione "Pagamentos"
2. Escolha "Referência"
3. Digite a referência: `MCX20241201143022F8E9A1`
4. Confirme o valor
5. Complete o pagamento

### **📱 Internet Banking**
1. Acesse sua conta bancária online
2. Procure por "Pagamentos" ou "Multicaixa"
3. Selecione "Pagamento por Referência"
4. Digite a referência e valor
5. Confirme a transação

### **📱 App do Banco**
1. Abra o app do seu banco
2. Procure por "Pagamentos" ou "Multicaixa"
3. Selecione "Referência"
4. Digite a referência e valor
5. Confirme o pagamento

## 🔢 Formato da Referência

### **Estrutura:**
```
MCX + DATA + HORA + CÓDIGO
```

### **Exemplo:**
```
MCX20241201143022F8E9A1
│   │         │      │
│   │         │      └── Código único (6 caracteres)
│   │         └──────── Hora (HHMMSS)
│   └────────────────── Data (YYYYMMDD)
└────────────────────── Prefixo Multicaixa
```

### **Significado:**
- **MCX**: Prefixo do Multicaixa Express
- **20241201**: Data (1 de Dezembro de 2024)
- **143022**: Hora (14:30:22)
- **F8E9A1**: Código único gerado

## ⏰ Validade e Prazos

### **Validade da Referência:**
- **24 horas** a partir da geração
- Após expirar, precisa gerar nova referência

### **Verificação Automática:**
- Sistema verifica a cada **10 segundos**
- Pode confirmar manualmente com código

### **Status Possíveis:**
- **Pendente**: Aguardando pagamento
- **Processando**: Pagamento em análise
- **Confirmado**: Pagamento aprovado
- **Expirado**: Referência vencida

## 🎯 Exemplo Prático

### **Cenário:**
Cliente quer comprar um smartphone por Kz 150.000,00

### **Passo 1: Checkout**
```
✅ Seleciona "Multicaixa Express"
✅ Clica "Finalizar Pedido"
✅ Redirecionado para simulação
```

### **Passo 2: Geração**
```
✅ Referência gerada: MCX20241201143022F8E9A1
✅ QR Code criado
✅ Instruções exibidas
```

### **Passo 3: Pagamento**
```
✅ Cliente vai ao BFA
✅ Informa referência: MCX20241201143022F8E9A1
✅ Confirma valor: Kz 150.000,00
✅ Paga em dinheiro
```

### **Passo 4: Confirmação**
```
✅ Sistema detecta pagamento
✅ Status muda para "Confirmado"
✅ Redireciona para sucesso
```

## 🔧 Funcionalidades Técnicas

### **API Simulada:**
- **Endpoint**: `api/multicaixa_simulator.php`
- **Ações**: gerar_referencia, validar_pagamento, confirmar_pagamento
- **Delay Realista**: 0.5 a 1.5 segundos

### **Interface:**
- **Design Responsivo**: Mobile e desktop
- **Animações**: Efeitos visuais suaves
- **Feedback**: Status em tempo real
- **QR Code**: Geração automática

### **Segurança:**
- **Código de Verificação**: 4 dígitos
- **Validação**: Verificação de dados
- **Expiração**: Controle de tempo
- **Sessão**: Armazenamento seguro

## 🚀 Como Testar

### **Teste Completo:**
1. Acesse o checkout
2. Selecione "Multicaixa Express"
3. Clique "Finalizar Pedido"
4. Aguarde geração da referência
5. Clique "Confirmar Manualmente"
6. Digite qualquer código de 4 dígitos
7. Confirme o pagamento

### **Teste de Verificação:**
1. Gere uma referência
2. Clique "Verificar Status"
3. Observe mudanças de status
4. Teste verificação automática

## 📞 Suporte

Para dúvidas sobre a simulação:
- **Email**: unitec01@gmail.com
- **Telefone**: (+244) 937 9609 636
- **Horário**: Segunda a Sexta, 8h às 18h

---

**Nota**: Esta é uma simulação educacional. Em produção, seria integrada com a API real do Multicaixa Express. 