<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";
require_once "config/sms_config.php"; // Carregar configurações da API

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// Verificar se há itens no carrinho
$sql = "SELECT COUNT(*) FROM carrinho WHERE usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$tem_itens = $stmt->fetchColumn() > 0;

if (!$tem_itens) {
    header("Location: produtos.php?erro=carrinho_vazio");
    exit;
}

// Buscar informações do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->fetch();

// Buscar itens do carrinho com informações dos produtos
$sql = "SELECT c.*, p.nome as produto_nome, p.preco, p.preco_promocional, p.estoque 
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id 
        WHERE c.usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$itens_carrinho = $stmt->fetchAll();

// Calcular totais
$subtotal = 0;
$total_itens = 0;

foreach ($itens_carrinho as $item) {
    $preco = $item['preco_promocional'] ? $item['preco_promocional'] : $item['preco'];
    $subtotal += $preco * $item['quantidade'];
    $total_itens += $item['quantidade'];
}

// Processar o pedido
$mensagem = '';
$mensagem_tipo = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    // Validar dados do formulário
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';

    // Validações
    $erros = [];
    if (empty($endereco)) $erros[] = "O endereço é obrigatório";
    if (empty($cidade)) $erros[] = "A cidade é obrigatória";
    if (empty($estado)) $erros[] = "O estado é obrigatório";
    if (empty($nif)) $erros[] = "O NIF é obrigatório";
    if (empty($forma_pagamento)) $erros[] = "A forma de pagamento é obrigatória";

    if (empty($erros)) {
        try {
            $conn->beginTransaction();

            // Criar pedido
            $sql = "INSERT INTO pedidos (
                usuario_id, 
                endereco_entrega, 
                cidade_entrega, 
                estado_entrega, 
                cep_entrega, 
                metodo_pagamento, 
                valor_total, 
                status, 
                status_pagamento,
                data_criacao
            ) VALUES (
                :usuario_id, 
                :endereco, 
                :cidade, 
                :estado, 
                :nif, 
                :forma_pagamento, 
                :total, 
                'pendente',
                'pendente',
                NOW()
            )";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':nif', $nif);
            $stmt->bindParam(':forma_pagamento', $forma_pagamento);
            $stmt->bindParam(':total', $subtotal);
            $stmt->execute();
            $pedido_id = $conn->lastInsertId();

            if ($pedido_id) {
                // Adicionar itens do pedido
                foreach ($itens_carrinho as $item) {
                    $preco = $item['preco_promocional'] ? $item['preco_promocional'] : $item['preco'];
                    
                    // Verificar estoque antes de adicionar
                    if ($item['quantidade'] > $item['estoque']) {
                        throw new Exception("Quantidade indisponível para o produto: " . $item['produto_nome']);
                    }

                    $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco) 
                            VALUES (:pedido_id, :produto_id, :quantidade, :preco)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':pedido_id', $pedido_id);
                    $stmt->bindParam(':produto_id', $item['produto_id']);
                    $stmt->bindParam(':quantidade', $item['quantidade']);
                    $stmt->bindParam(':preco', $preco);
                    $stmt->execute();

                    // Atualizar estoque
                    $sql = "UPDATE produtos SET estoque = estoque - :quantidade WHERE id = :produto_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':quantidade', $item['quantidade']);
                    $stmt->bindParam(':produto_id', $item['produto_id']);
                    $stmt->execute();
                }

                // Limpar carrinho
                $sql = "DELETE FROM carrinho WHERE usuario_id = :usuario_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
                $stmt->execute();

                $conn->commit();
                
                // Redirecionar para página de confirmação
                header("Location: pedido-confirmado.php?id=" . $pedido_id);
                exit;
            } else {
                throw new Exception("Erro ao criar o pedido");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $mensagem = "Erro ao processar o pedido: " . $e->getMessage();
            $mensagem_tipo = 'danger';
        }
    } else {
        $mensagem = "Por favor, corrija os seguintes erros:<br>" . implode("<br>", $erros);
        $mensagem_tipo = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Unitec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .checkout-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .checkout-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
        }
        .order-summary {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 20px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #d2d2d7;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #1d1d1f;
        }
        .order-item-price {
            font-size: 0.9rem;
            color: #6e6e73;
        }
        .order-summary-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #d2d2d7;
        }
        .payment-method {
            margin-bottom: 15px;
        }
        .payment-method label {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
            position: relative;
        }
        .payment-method label:hover {
            border-color: #0071e3;
            background: #f0f8ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.15);
        }
        .payment-method input[type="radio"] {
            margin-right: 15px;
            transform: scale(1.2);
        }
        .payment-method input[type="radio"]:checked + i + strong {
            color: #0071e3;
        }
        .payment-method-icon {
            margin-right: 15px;
            font-size: 1.5rem;
            width: 30px;
            text-align: center;
        }
        .payment-method strong {
            font-size: 1.1rem;
            margin-right: 10px;
        }
        .payment-method small {
            font-size: 0.85rem;
            margin-top: 2px;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-section">
        <div class="container">
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $mensagem_tipo; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($itens_carrinho)): ?>
                <div class="checkout-container">
                    <div class="text-center">
                        <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                        <h3>Seu carrinho está vazio</h3>
                        <p>Adicione produtos ao seu carrinho para continuar com a compra.</p>
                        <a href="produtos.php" class="btn btn-primary">Continuar Comprando</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-container">
                            <h2 class="checkout-title">Informações de Entrega</h2>
                            
                            <form method="POST" id="checkout-form">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="endereco" class="form-label">Endereço de Entrega</label>
                                    <input type="text" class="form-control" id="endereco" name="endereco" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cidade" class="form-label">Cidade</label>
                                        <input type="text" class="form-control" id="cidade" name="cidade" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <input type="text" class="form-control" id="estado" name="estado" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="nif" class="form-label">NIF</label>
                                        <input type="text" class="form-control" id="nif" name="nif" required>
                                    </div>
                                </div>

                                <h3 class="checkout-title mt-4">Forma de Pagamento</h3>
                                
                                <!-- Informações sobre métodos de pagamento -->
                                <div class="alert alert-light mb-4">
                                    <h6><i class="fas fa-info-circle me-2 text-primary"></i>Métodos de Pagamento Disponíveis</h6>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <strong>Multicaixa Express:</strong> Sistema EMIS - aceito em todos os bancos
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <strong>Referência:</strong> Pague em qualquer banco com uma referência única
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="multicaixa_express">
                                        <i class="fas fa-credit-card payment-method-icon" style="color: #1e40af;"></i>
                                        <strong>Multicaixa Express</strong>
                                        <small class="text-muted d-block">Sistema EMIS - Pagamento eletrônico</small>
                                    </label>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="pagamento_referencia">
                                        <i class="fas fa-barcode payment-method-icon" style="color: #7c3aed;"></i>
                                        <strong>Pagamento por Referência</strong>
                                        <small class="text-muted d-block">Referência única para pagar em qualquer banco</small>
                                    </label>
                                </div>

                                <!-- Campo para mostrar referência de pagamento -->
                                <div id="referencia-pagamento" class="mb-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Referência de Pagamento</h6>
                                        <p class="mb-2">Use esta referência para pagar em qualquer banco ou terminal Multicaixa:</p>
                                        <div class="d-flex align-items-center">
                                            <input type="text" class="form-control me-2" id="referencia-gerada" readonly 
                                                   value="<?php echo 'REF-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8)); ?>">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="copiarReferencia()">
                                                <i class="fas fa-copy"></i> Copiar
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-clock me-1"></i>Esta referência é válida por 24 horas
                                        </small>
                                    </div>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="transferencia">
                                        <i class="fas fa-exchange-alt payment-method-icon" style="color: #059669;"></i>
                                        <strong>Transferência Bancária</strong>
                                        <small class="text-muted d-block">Transferência direta</small>
                                    </label>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="forma_pagamento" value="dinheiro">
                                        <i class="fas fa-money-bill-wave payment-method-icon" style="color: #16a34a;"></i>
                                        <strong>Dinheiro Físico</strong>
                                        <small class="text-muted d-block">Pagamento na entrega</small>
                                    </label>
                                </div>

                                <button type="submit" name="finalizar_pedido" class="btn btn-info btn-lg w-100 mt-4 text-white">
                                    Finalizar Pedido
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h3 class="checkout-title">Resumo do Pedido</h3>
                            
                            <?php foreach ($itens_carrinho as $item): ?>
                                <div class="cart-item">
                                    <div class="item-info">
                                        <span class="item-name"><?php echo htmlspecialchars($item['produto_nome']); ?></span>
                                        <span class="item-quantity">x<?php echo $item['quantidade']; ?></span>
                                    </div>
                                    <div class="item-price">
                                        Kz <?php echo number_format(($item['preco_promocional'] ? $item['preco_promocional'] : $item['preco']) * $item['quantidade'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="order-summary-total">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal (<?php echo $total_itens; ?> itens)</span>
                                    <span>Kz <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Frete</span>
                                    <span>Grátis</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>Kz <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para NIF
        document.getElementById('nif').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6, 9);
            }
            e.target.value = value;
        });

        // Melhorar experiência das opções de pagamento
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="forma_pagamento"]');
            const paymentLabels = document.querySelectorAll('.payment-method label');
            const referenciaDiv = document.getElementById('referencia-pagamento');

            paymentMethods.forEach(function(radio, index) {
                radio.addEventListener('change', function() {
                    // Remover classe ativa de todos os labels
                    paymentLabels.forEach(label => {
                        label.style.borderColor = '#e5e7eb';
                        label.style.background = '#fafafa';
                        label.style.transform = 'translateY(0)';
                        label.style.boxShadow = 'none';
                    });

                    // Adicionar classe ativa ao label selecionado
                    if (this.checked) {
                        const selectedLabel = paymentLabels[index];
                        selectedLabel.style.borderColor = '#0071e3';
                        selectedLabel.style.background = '#f0f8ff';
                        selectedLabel.style.transform = 'translateY(-2px)';
                        selectedLabel.style.boxShadow = '0 4px 12px rgba(0, 113, 227, 0.15)';
                    }

                    // Mostrar/ocultar campo de referência
                    if (this.value === 'pagamento_referencia') {
                        referenciaDiv.style.display = 'block';
                        // Gerar nova referência
                        const novaReferencia = 'REF-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-' + Math.random().toString(36).substr(2, 8).toUpperCase();
                        document.getElementById('referencia-gerada').value = novaReferencia;
                    } else {
                        referenciaDiv.style.display = 'none';
                    }
                });
            });

            // Validação do formulário
            document.getElementById('checkout-form').addEventListener('submit', function(e) {
                const selectedPayment = document.querySelector('input[name="forma_pagamento"]:checked');
                
                if (!selectedPayment) {
                    e.preventDefault();
                    alert('Por favor, selecione uma forma de pagamento.');
                    return false;
                }

                // Redirecionar para simulação do Multicaixa Express
                if (selectedPayment.value === 'multicaixa_express') {
                    e.preventDefault();
                    const valor = <?php echo $subtotal; ?>;
                    const pedido_id = '<?php echo time(); ?>'; // ID temporário
                    
                    // Verificar se API real está habilitada
                    <?php if (defined('MULTICAIXA_API_ENABLED') && MULTICAIXA_API_ENABLED): ?>
                        // Usar API real
                        window.location.href = `multicaixa_simulator.php?pedido_id=${pedido_id}&valor=${valor}&api_real=true`;
                    <?php else: ?>
                        // Usar simulação
                        window.location.href = `multicaixa_simulator.php?pedido_id=${pedido_id}&valor=${valor}`;
                    <?php endif; ?>
                    return false;
                }
            });
        });

        // Função para copiar referência
        function copiarReferencia() {
            const referenciaInput = document.getElementById('referencia-gerada');
            referenciaInput.select();
            referenciaInput.setSelectionRange(0, 99999); // Para dispositivos móveis
            
            try {
                document.execCommand('copy');
                
                // Feedback visual
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
                
            } catch (err) {
                console.error('Erro ao copiar: ', err);
                alert('Erro ao copiar a referência. Tente copiar manualmente.');
            }
        }

        // Função para gerar referência via API (se disponível)
        async function gerarReferenciaAPI(valor, pedidoId) {
            try {
                const formData = new FormData();
                formData.append('action', 'gerar_referencia');
                formData.append('valor', valor);
                formData.append('pedido_id', pedidoId);
                formData.append('descricao', 'Compra UNITEC Store');
                
                const response = await fetch('api/multicaixa_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    return resultado.data;
                } else {
                    throw new Error(resultado.error);
                }
            } catch (error) {
                console.error('Erro ao gerar referência via API:', error);
                return null;
            }
        }
    </script>
</body>
</html> 