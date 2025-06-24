<?php
// Teste simples do simulador
echo "<h1>Teste do Simulador Multicaixa</h1>\n";

// Simular POST request
$_POST['action'] = 'gerar_referencia';
$_POST['valor'] = 5000;
$_POST['pedido_id'] = 'PED' . time();

echo "<h2>Testando geração de referência...</h2>\n";
echo "<p><strong>Valor:</strong> Kz " . number_format($_POST['valor'], 2, ',', '.') . "</p>\n";
echo "<p><strong>Pedido:</strong> " . $_POST['pedido_id'] . "</p>\n";

// Incluir o simulador
ob_start();
include 'api/multicaixa_simulator.php';
$output = ob_get_clean();

echo "<h3>Resposta do Simulador:</h3>\n";
echo "<pre>" . htmlspecialchars($output) . "</pre>\n";

// Decodificar JSON para verificar
$response = json_decode($output, true);
if ($response) {
    if ($response['success']) {
        echo "<p style='color: green;'><strong>✅ Sucesso!</strong> Referência gerada: " . $response['data']['referencia'] . "</p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ Erro:</strong> " . $response['error'] . "</p>\n";
    }
} else {
    echo "<p style='color: red;'><strong>❌ Erro:</strong> Resposta inválida</p>\n";
}
?> 