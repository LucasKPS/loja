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
