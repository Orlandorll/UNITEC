<?php
// Teste de acesso às imagens do hero
$imagens = [
    'uploads/hero/683b8767d5386.webp',
    'uploads/hero/683b88ad0bb09.webp',
    'uploads/hero/683b92960b280.jpg'
];

echo "<h1>Teste de Acesso às Imagens do Hero</h1>";

foreach ($imagens as $imagem) {
    echo "<h2>Testando: $imagem</h2>";
    
    if (file_exists($imagem)) {
        echo "<p style='color: green;'>✅ Arquivo existe</p>";
        echo "<p>Permissões: " . substr(sprintf('%o', fileperms($imagem)), -4) . "</p>";
        echo "<p>Tamanho: " . filesize($imagem) . " bytes</p>";
        echo "<img src='$imagem' style='max-width: 300px;'><br><br>";
    } else {
        echo "<p style='color: red;'>❌ Arquivo não encontrado</p>";
    }
}

// Teste do CSS
$css = 'assets/css/style.css';
echo "<h2>Testando CSS: $css</h2>";
if (file_exists($css)) {
    echo "<p style='color: green;'>✅ Arquivo CSS existe</p>";
    echo "<p>Permissões: " . substr(sprintf('%o', fileperms($css)), -4) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Arquivo CSS não encontrado</p>";
}
?> 