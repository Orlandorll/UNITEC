<?php
// Teste básico
echo "Teste 1: PHP está funcionando<br>";

// Teste de diretório
echo "Teste 2: Verificando diretório atual<br>";
echo "Diretório: " . __DIR__ . "<br>";

// Teste de arquivo
echo "Teste 3: Verificando arquivo hero-example.php<br>";
if (file_exists('hero-example.php')) {
    echo "Arquivo hero-example.php existe<br>";
} else {
    echo "Arquivo hero-example.php NÃO existe<br>";
}

// Teste de imagem
echo "Teste 4: Verificando primeira imagem<br>";
$imagem = 'uploads/hero/683b8767d5386.webp';
if (file_exists($imagem)) {
    echo "Imagem existe<br>";
    echo "<img src='$imagem' style='max-width: 200px;'><br>";
} else {
    echo "Imagem NÃO existe<br>";
}

// Teste de HTML básico
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Simples</title>
</head>
<body>
    <h1>Teste de HTML</h1>
    <p>Se você está vendo esta mensagem, o HTML está funcionando.</p>
</body>
</html> 