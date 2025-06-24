<?php
// Criar diretório se não existir
if (!file_exists('assets/img')) {
    mkdir('assets/img', 0777, true);
}

// Gerar no-image.jpg
function gerarNoImage() {
    // Criar uma imagem 400x400
    $imagem = imagecreatetruecolor(400, 400);
    
    // Definir cores
    $cinza = imagecolorallocate($imagem, 248, 249, 250); // #f8f9fa
    $cinza_escuro = imagecolorallocate($imagem, 108, 117, 125); // #6c757d
    
    // Preencher fundo
    imagefill($imagem, 0, 0, $cinza);
    
    // Desenhar ícone de imagem (simplificado)
    $tamanho_icone = 100;
    $x = (400 - $tamanho_icone) / 2;
    $y = (400 - $tamanho_icone) / 2;
    
    // Desenhar retângulo do ícone
    imagerectangle($imagem, $x, $y, $x + $tamanho_icone, $y + $tamanho_icone, $cinza_escuro);
    
    // Desenhar "montanha" do ícone
    $pontos = array(
        $x + 20, $y + $tamanho_icone - 20,  // Ponto base esquerdo
        $x + $tamanho_icone/2, $y + 20,     // Ponto do topo
        $x + $tamanho_icone - 20, $y + $tamanho_icone - 20  // Ponto base direito
    );
    imagepolygon($imagem, $pontos, 3, $cinza_escuro);
    
    // Desenhar sol
    imageellipse($imagem, $x + $tamanho_icone - 30, $y + 30, 20, 20, $cinza_escuro);
    
    // Salvar imagem
    imagejpeg($imagem, 'assets/img/no-image.jpg', 90);
    imagedestroy($imagem);
}

// Gerar payment-methods.png
function gerarPaymentMethods() {
    // Criar uma imagem 300x30 (altura fixa de 30px como definido no CSS)
    $imagem = imagecreatetruecolor(300, 30);
    
    // Definir cores
    $branco = imagecolorallocate($imagem, 255, 255, 255);
    $preto = imagecolorallocate($imagem, 0, 0, 0);
    
    // Preencher fundo branco
    imagefill($imagem, 0, 0, $branco);
    
    // Definir fonte
    $fonte = 5; // Fonte padrão do GD
    
    // Desenhar texto dos métodos de pagamento
    $texto = "Visa  Mastercard  PIX  Boleto";
    $x = 10;
    $y = 20;
    
    // Desenhar texto
    imagestring($imagem, $fonte, $x, $y, $texto, $preto);
    
    // Salvar imagem
    imagepng($imagem, 'assets/img/payment-methods.png');
    imagedestroy($imagem);
}

// Executar a geração das imagens
gerarNoImage();
gerarPaymentMethods();

echo "Imagens geradas com sucesso!\n";
echo "- assets/img/no-image.jpg\n";
echo "- assets/img/payment-methods.png\n";
?> 