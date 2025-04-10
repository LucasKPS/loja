<?php
session_start();

// Verificar se o ID do produto foi passado na URL
if (isset($_GET['id'])) {
    $produto_id = $_GET['id'];

    // Verificar se o produto existe no carrinho
    if (isset($_SESSION['cart'][$produto_id])) {
        // Remover o produto do carrinho
        unset($_SESSION['cart'][$produto_id]);
    }
}

// Redirecionar de volta para o carrinho
header("Location: carrinho.php");
exit();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remover do Carrinho</title>
    <link rel="icon" href="imagens/ha.png" type="image/png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Conteúdo da página de remoção do carrinho -->
</body>
</html>