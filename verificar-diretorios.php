<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Verificação de Diretórios e Arquivos</h1>";

// Verificar diretório raiz
$root_dir = __DIR__;
echo "<h2>Diretório Raiz: {$root_dir}</h2>";
echo "<pre>";
print_r(scandir($root_dir));
echo "</pre>";

// Verificar diretório uploads
$uploads_dir = __DIR__ . '/uploads';
echo "<h2>Diretório Uploads: {$uploads_dir}</h2>";
if (is_dir($uploads_dir)) {
    echo "<pre>";
    print_r(scandir($uploads_dir));
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Diretório uploads não existe!</p>";
}

// Verificar diretório hero
$hero_dir = __DIR__ . '/uploads/hero';
echo "<h2>Diretório Hero: {$hero_dir}</h2>";
if (is_dir($hero_dir)) {
    echo "<pre>";
    print_r(scandir($hero_dir));
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Diretório hero não existe!</p>";
}

// Verificar arquivos específicos
$arquivos = [
    'uploads/hero/683b8767d5386.webp',
    'uploads/hero/683b88ad0bb09.webp',
    'uploads/hero/683b92960b280.jpg'
];

echo "<h2>Verificação de Arquivos Específicos</h2>";
foreach ($arquivos as $arquivo) {
    $caminho_completo = __DIR__ . '/' . $arquivo;
    echo "<p><strong>Arquivo:</strong> {$arquivo}</p>";
    echo "<ul>";
    echo "<li>Existe: " . (file_exists($caminho_completo) ? 'Sim' : 'Não') . "</li>";
    if (file_exists($caminho_completo)) {
        echo "<li>Tamanho: " . filesize($caminho_completo) . " bytes</li>";
        echo "<li>Permissões: " . substr(sprintf('%o', fileperms($caminho_completo)), -4) . "</li>";
        echo "<li>Caminho Absoluto: {$caminho_completo}</li>";
    }
    echo "</ul>";
}

// Verificar permissões do diretório
echo "<h2>Permissões dos Diretórios</h2>";
$diretorios = [
    'uploads' => $uploads_dir,
    'uploads/hero' => $hero_dir
];

foreach ($diretorios as $nome => $dir) {
    if (is_dir($dir)) {
        echo "<p><strong>{$nome}:</strong> " . substr(sprintf('%o', fileperms($dir)), -4) . "</p>";
    }
}

// Verificar se o Apache pode acessar os arquivos
echo "<h2>Teste de Acesso do Apache</h2>";
$test_file = $hero_dir . '/683b92960b280.jpg';
if (file_exists($test_file)) {
    echo "<p>Tentando ler o arquivo como o Apache...</p>";
    $content = @file_get_contents($test_file);
    if ($content !== false) {
        echo "<p style='color: green;'>Sucesso! O Apache pode ler o arquivo.</p>";
    } else {
        echo "<p style='color: red;'>Erro! O Apache não pode ler o arquivo.</p>";
    }
}
?> 