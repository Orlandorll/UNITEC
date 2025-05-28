# Unitec E-commerce

Este é um sistema de e-commerce desenvolvido para a Unitec, uma loja de produtos eletrônicos.

## Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- XAMPP (recomendado para desenvolvimento local)

## Instalação

1. Clone este repositório para sua pasta htdocs do XAMPP:
   ```
   C:\xampp\htdocs\UNITEC
   ```

2. Inicie o XAMPP e ative os serviços Apache e MySQL

3. Acesse o phpMyAdmin (http://localhost/phpmyadmin) e crie um novo banco de dados chamado `unitec_ecommerce`

4. O sistema criará automaticamente as tabelas necessárias na primeira execução

5. Acesse o site através do navegador:
   ```
   http://localhost/UNITEC
   ```

## Estrutura do Projeto

```
UNITEC/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   └── database.php
├── includes/
├── admin/
└── index.php
```

## Funcionalidades

- Sistema de autenticação de usuários
- Catálogo de produtos
- Carrinho de compras
- Sistema de pagamento
- Painel administrativo
- Gerenciamento de produtos
- Gerenciamento de pedidos

## Suporte

Para suporte, entre em contato através do email: contato@unitec.com 