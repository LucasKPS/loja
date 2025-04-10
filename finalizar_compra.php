<?php
session_start();

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_loja_roupas";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction();

    // Verifica se o carrinho não está vazio
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // 1. Criar o pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (total) VALUES (?)");
        $total = 0;
        
        // Calcular total
        $cart_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $stmt_produtos = $conn->prepare("SELECT id, preco FROM produtos WHERE id IN ($placeholders)");
        $stmt_produtos->execute($cart_ids);
        $produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($produtos as $produto) {
            $quantidade = $_SESSION['cart'][$produto['id']]['quantity'];
            $total += $produto['preco'] * $quantidade;
        }
        
        $stmt->execute([$total]);
        $pedido_id = $conn->lastInsertId();
        
        // 2. Adicionar itens do pedido e atualizar estoque
        foreach ($produtos as $produto) {
            $quantidade = $_SESSION['cart'][$produto['id']]['quantity'];
            
            // Adicionar item ao pedido
            $stmt_item = $conn->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmt_item->execute([$pedido_id, $produto['id'], $quantidade, $produto['preco']]);
            
            // Atualizar estoque
            $stmt_estoque = $conn->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
            $stmt_estoque->execute([$quantidade, $produto['id']]);
        }
        
        $conn->commit();
        
        // Limpar o carrinho após a compra
        unset($_SESSION['cart']);
        
        $_SESSION['success'] = "Compra efetuada com sucesso! Nº do pedido: $pedido_id";
    } else {
        $_SESSION['error'] = "Seu carrinho está vazio!";
    }
} catch(PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Erro ao processar pedido: " . $e->getMessage();
}

header("Location: carrinho.php");
exit();
?>