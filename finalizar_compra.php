<?php
session_start();

// Verifica se o carrinho não está vazio
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Aqui você pode processar o pedido, como salvar no banco de dados, etc.
    
    // Limpar o carrinho após a compra
    unset($_SESSION['cart']);
    
    // Mensagem de sucesso
    $_SESSION['success'] = "Compra efetuada com sucesso!";
} else {
    // Caso o carrinho esteja vazio
    $_SESSION['error'] = "Seu carrinho está vazio!";
}

// Redireciona de volta para a página de carrinho ou para a página inicial
header("Location: carrinho.php");
exit();
?>
