<?php
session_start();
require_once "config/database.php";
require_once "includes/functions.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id'])) {
    header("Location: meus-pedidos.php");
    exit;
}

$pedido_id = $_GET['id'];

// Buscar informações do pedido
$sql = "SELECT p.*, u.nome as nome_usuario, u.email,
        (SELECT SUM(ip.preco * ip.quantidade) 
         FROM itens_pedido ip 
         WHERE ip.pedido_id = p.id) as total_calculado
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = :pedido_id AND p.usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$pedido = $stmt->fetch();

if (!$pedido) {
    header("Location: meus-pedidos.php");
    exit;
}

// Buscar itens do pedido
$sql = "SELECT ip.*, p.nome as produto_nome,
        (ip.preco * ip.quantidade) as subtotal_item
        FROM itens_pedido ip 
        JOIN produtos p ON ip.produto_id = p.id 
        WHERE ip.pedido_id = :pedido_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->execute();
$itens = $stmt->fetchAll();

// Calcular subtotal
$subtotal = 0;
foreach ($itens as $item) {
    $subtotal += $item['subtotal_item'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function voltar() {
            window.location.href = 'meus-pedidos.php';
        }

        function baixarFatura() {
            const areaFatura = document.getElementById('fatura');
            const btnVoltar = document.querySelector('.back-button');
            const btnBaixar = document.querySelector('.download-button');

            // Esconder botões para não aparecerem na imagem
            btnVoltar.style.display = 'none';
            btnBaixar.style.display = 'none';

            html2canvas(areaFatura, {
                scale: 2, // Aumenta a resolução da imagem
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'fatura-unitec-<?php echo $pedido_id; ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                // Mostrar os botões novamente
                btnVoltar.style.display = 'flex';
                btnBaixar.style.display = 'flex';
            });
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .botoes-acao {
            position: absolute;
            left: 20px;
            top: 20px;
            display: flex;
            gap: 10px;
        }
        .back-button, .download-button {
            background: #0d6efd;
            border: none;
            font-size: 16px;
            color: white;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        .download-button {
            background: #198754; /* Cor verde */
        }
        .back-button:hover, .download-button:hover {
            opacity: 0.9;
            color: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            padding-top: 20px;
        }
        .order-number {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        .order-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .order-info {
            margin-bottom: 10px;
        }
        .order-info p {
            margin: 5px 0;
        }
        .order-items {
            margin: 20px 0;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .order-items th, .order-items td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .order-items th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .order-total {
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .order-total p {
            margin: 5px 0;
            font-size: 16px;
        }
        .order-total .grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
        }
        .order-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container" id="fatura">
        <div class="botoes-acao">
            <button onclick="voltar()" class="back-button">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button onclick="baixarFatura()" class="download-button">
                <i class="fas fa-download"></i> Baixar Fatura
            </button>
        </div>
        <div class="header">
            <h1 class="order-number">Detalhes do Pedido #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></h1>
        </div>

        <div class="order-container">
            <div class="order-header">
                <div class="order-info">
                    <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($pedido['status']); ?></p>
                    <p><strong>Forma de Pagamento:</strong> <?php echo ucfirst($pedido['metodo_pagamento']); ?></p>
                </div>
            </div>

            <div class="order-section">
                <h2>Endereço de Entrega</h2>
                <div class="order-info">
                    <p><strong>Endereço:</strong> <?php echo $pedido['endereco_entrega'] ?? 'Não informado'; ?></p>
                    <p><strong>Cidade:</strong> <?php echo $pedido['cidade_entrega'] ?? 'Não informado'; ?></p>
                    <p><strong>Estado:</strong> <?php echo $pedido['estado_entrega'] ?? 'Não informado'; ?></p>
                    <p><strong>CEP:</strong> <?php echo $pedido['cep_entrega'] ?? 'Não informado'; ?></p>
                </div>
            </div>

            <div class="order-items">
                <h2>Itens do Pedido</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td>Kz <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                            <td>Kz <?php echo number_format($item['subtotal_item'], 2, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="order-total">
                <p><strong>Subtotal:</strong> Kz <?php echo number_format($subtotal, 2, ',', '.'); ?></p>
                <p><strong>Frete:</strong> Grátis</p>
                <p class="grand-total">Total: Kz <?php echo number_format($subtotal, 2, ',', '.'); ?></p>
            </div>

            <div class="order-footer">
                <p>Obrigado por comprar conosco!</p>
                <p>UNITEC - Seu parceiro em tecnologia</p>
            </div>
        </div>
    </div>
</body>
</html> 