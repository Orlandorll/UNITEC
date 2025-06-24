<?php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Informações do PHP
echo "<h1>Teste de PHP</h1>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<p>Diretório atual: " . __DIR__ . "</p>";

// Teste de conexão com o servidor
echo "<h2>Teste de Conexão</h2>";
echo "<p>Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Teste de permissões
echo "<h2>Teste de Permissões</h2>";
$test_dirs = [
    'uploads',
    'uploads/hero',
    'assets',
    'assets/css'
];

foreach ($test_dirs as $dir) {
    echo "<p>Diretório $dir: ";
    if (is_dir($dir)) {
        echo "✅ Existe";
        echo " (Permissões: " . substr(sprintf('%o', fileperms($dir)), -4) . ")";
    } else {
        echo "❌ Não existe";
    }
    echo "</p>";
}

// Teste de imagens
echo "<h2>Teste de Imagens</h2>";
$images = [
    'uploads/hero/683b8767d5386.webp',
    'uploads/hero/683b88ad0bb09.webp',
    'uploads/hero/683b92960b280.jpg'
];

foreach ($images as $image) {
    echo "<p>Imagem $image: ";
    if (file_exists($image)) {
        echo "✅ Existe";
        echo " (Tamanho: " . filesize($image) . " bytes)";
        echo " (Permissões: " . substr(sprintf('%o', fileperms($image)), -4) . ")";
    } else {
        echo "❌ Não existe";
    }
    echo "</p>";
}
?> 