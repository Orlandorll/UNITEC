<?php
require_once "config/database.php";

// Array com as imagens e informações do hero
$hero_images = [
    [
        'titulo' => 'Tecnologia e Inovação',
        'subtitulo' => 'Bem-vindo à UNITEC',
        'descricao' => 'Descubra as melhores soluções tecnológicas para seu negócio',
        'imagem' => 'uploads/hero/683b8767d5386.webp',
        'link_botao' => '#',
        'texto_botao' => 'Saiba Mais',
        'ordem' => 1,
        'status' => 1
    ],
    [
        'titulo' => 'Transformação Digital',
        'subtitulo' => 'Soluções Digitais',
        'descricao' => 'Impulsione seu negócio com nossas soluções inovadoras',
        'imagem' => 'uploads/hero/683b88ad0bb09.webp',
        'link_botao' => '#',
        'texto_botao' => 'Conheça Nossos Serviços',
        'ordem' => 2,
        'status' => 1
    ],
    [
        'titulo' => 'Atendimento 24/7',
        'subtitulo' => 'Suporte Técnico',
        'descricao' => 'Nossa equipe está sempre pronta para ajudar você',
        'imagem' => 'uploads/hero/683b92960b280.jpg',
        'link_botao' => '#',
        'texto_botao' => 'Fale Conosco',
        'ordem' => 3,
        'status' => 1
    ]
];

try {
    // Limpar a tabela existente
    $conn->exec("TRUNCATE TABLE hero_images");
    
    // Preparar a query de inserção
    $sql = "INSERT INTO hero_images (titulo, subtitulo, descricao, imagem, link_botao, texto_botao, ordem, status) 
            VALUES (:titulo, :subtitulo, :descricao, :imagem, :link_botao, :texto_botao, :ordem, :status)";
    $stmt = $conn->prepare($sql);
    
    // Inserir cada imagem
    foreach ($hero_images as $image) {
        $stmt->execute($image);
    }
    
    echo "Imagens do hero inseridas com sucesso!";
    
} catch (PDOException $e) {
    echo "Erro ao inserir imagens: " . $e->getMessage();
}
?> 