<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Criar tabela de configurações se não existir
$sql = "CREATE TABLE IF NOT EXISTS configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_loja VARCHAR(100) NOT NULL,
    email_contato VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(50),
    provincia VARCHAR(50),
    nif VARCHAR(20),
    descricao_loja TEXT,
    meta_keywords TEXT,
    meta_description TEXT,
    whatsapp VARCHAR(20),
    facebook VARCHAR(100),
    instagram VARCHAR(100),
    twitter VARCHAR(100),
    horario_funcionamento TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->exec($sql);

// Inserir configurações padrão se não existir
$sql = "INSERT INTO configuracoes (nome_loja, email_contato) 
        SELECT 'UNITEC', 'contato@unitec.com'
        WHERE NOT EXISTS (SELECT 1 FROM configuracoes WHERE id = 1)";
$conn->exec($sql);

$mensagem = '';
$erro = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_loja = trim($_POST['nome_loja']);
    $email_contato = trim($_POST['email_contato']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $cidade = trim($_POST['cidade']);
    $provincia = trim($_POST['provincia']);
    $nif = trim($_POST['nif']);
    $descricao_loja = trim($_POST['descricao_loja']);
    $meta_keywords = trim($_POST['meta_keywords']);
    $meta_description = trim($_POST['meta_description']);
    $whatsapp = trim($_POST['whatsapp']);
    $facebook = trim($_POST['facebook']);
    $instagram = trim($_POST['instagram']);
    $twitter = trim($_POST['twitter']);
    $horario_funcionamento = trim($_POST['horario_funcionamento']);

    // Validar campos obrigatórios
    if (empty($nome_loja) || empty($email_contato)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Atualizar configurações
        $sql = "UPDATE configuracoes SET 
                nome_loja = :nome_loja,
                email_contato = :email_contato,
                telefone = :telefone,
                endereco = :endereco,
                cidade = :cidade,
                provincia = :provincia,
                nif = :nif,
                descricao_loja = :descricao_loja,
                meta_keywords = :meta_keywords,
                meta_description = :meta_description,
                whatsapp = :whatsapp,
                facebook = :facebook,
                instagram = :instagram,
                twitter = :twitter,
                horario_funcionamento = :horario_funcionamento,
                data_atualizacao = NOW()
                WHERE id = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nome_loja', $nome_loja);
        $stmt->bindParam(':email_contato', $email_contato);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':provincia', $provincia);
        $stmt->bindParam(':nif', $nif);
        $stmt->bindParam(':descricao_loja', $descricao_loja);
        $stmt->bindParam(':meta_keywords', $meta_keywords);
        $stmt->bindParam(':meta_description', $meta_description);
        $stmt->bindParam(':whatsapp', $whatsapp);
        $stmt->bindParam(':facebook', $facebook);
        $stmt->bindParam(':instagram', $instagram);
        $stmt->bindParam(':twitter', $twitter);
        $stmt->bindParam(':horario_funcionamento', $horario_funcionamento);

        if ($stmt->execute()) {
            $mensagem = "Configurações atualizadas com sucesso!";
        } else {
            $erro = "Erro ao atualizar configurações. Por favor, tente novamente.";
        }
    }
}

// Buscar configurações atuais
$sql = "SELECT * FROM configuracoes WHERE id = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Inicializar campos com valores vazios se não existirem
$default_config = [
    'nome_loja' => '',
    'email_contato' => '',
    'telefone' => '',
    'endereco' => '',
    'cidade' => '',
    'provincia' => '',
    'nif' => '',
    'descricao_loja' => '',
    'meta_keywords' => '',
    'meta_description' => '',
    'whatsapp' => '',
    'facebook' => '',
    'instagram' => '',
    'twitter' => '',
    'horario_funcionamento' => ''
];

$config = is_array($config) ? array_merge($default_config, $config) : $default_config;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações Gerais - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-section {
            padding: 40px 0;
            background: #f5f5f7;
        }
        .admin-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .admin-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #1d1d1f;
        }
        .form-label {
            font-weight: 500;
            color: #1d1d1f;
        }
        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-section">
        <div class="container">
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="admin-title">Configurações Gerais</h1>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nome_loja" class="form-label required-field">Nome da Loja</label>
                            <input type="text" class="form-control" id="nome_loja" name="nome_loja" 
                                   value="<?php echo htmlspecialchars($config['nome_loja']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email_contato" class="form-label required-field">Email de Contato</label>
                            <input type="email" class="form-control" id="email_contato" name="email_contato" 
                                   value="<?php echo htmlspecialchars($config['email_contato']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" 
                                   value="<?php echo htmlspecialchars($config['telefone']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="whatsapp" class="form-label">WhatsApp</label>
                            <input type="tel" class="form-control" id="whatsapp" name="whatsapp" 
                                   value="<?php echo htmlspecialchars($config['whatsapp']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <input type="text" class="form-control" id="endereco" name="endereco" 
                               value="<?php echo htmlspecialchars($config['endereco']); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" 
                                   value="<?php echo htmlspecialchars($config['cidade']); ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="provincia" class="form-label">Província</label>
                            <input type="text" class="form-control" id="provincia" name="provincia" 
                                   value="<?php echo htmlspecialchars($config['provincia']); ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="nif" class="form-label">NIF</label>
                            <input type="text" class="form-control" id="nif" name="nif" 
                                   value="<?php echo htmlspecialchars($config['nif']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="horario_funcionamento" class="form-label">Horário de Funcionamento</label>
                        <textarea class="form-control" id="horario_funcionamento" name="horario_funcionamento" rows="2"><?php echo htmlspecialchars($config['horario_funcionamento']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="descricao_loja" class="form-label">Descrição da Loja</label>
                        <textarea class="form-control" id="descricao_loja" name="descricao_loja" rows="3"><?php echo htmlspecialchars($config['descricao_loja']); ?></textarea>
                    </div>

                    <h5 class="mt-4 mb-3">Redes Sociais</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="facebook" class="form-label">Facebook</label>
                            <input type="url" class="form-control" id="facebook" name="facebook" 
                                   value="<?php echo htmlspecialchars($config['facebook']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="instagram" class="form-label">Instagram</label>
                            <input type="url" class="form-control" id="instagram" name="instagram" 
                                   value="<?php echo htmlspecialchars($config['instagram']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="twitter" class="form-label">Twitter</label>
                        <input type="url" class="form-control" id="twitter" name="twitter" 
                               value="<?php echo htmlspecialchars($config['twitter']); ?>">
                    </div>

                    <h5 class="mt-4 mb-3">SEO</h5>
                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                               value="<?php echo htmlspecialchars($config['meta_keywords']); ?>">
                        <small class="text-muted">Palavras-chave separadas por vírgula</small>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars($config['meta_description']); ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 