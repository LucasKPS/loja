<?php
session_start();

// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_loja_roupas";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se o ID do produto for passado para remoção
    if (isset($_GET['remover_id'])) {
        $produto_id = $_GET['remover_id'];

        // Verificar se o produto está no carrinho
        if (isset($_SESSION['cart'][$produto_id])) {
            // Remover o produto do carrinho
            unset($_SESSION['cart'][$produto_id]);
        }

        // Redirecionar de volta para o carrinho após remover o item
        header("Location: carrinho.php");
        exit();
    }

    // Verificar se o carrinho não está vazio
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // Obter os IDs dos produtos no carrinho
        $cart_ids = array_keys($_SESSION['cart']);

        // Consultar todos os produtos no carrinho com base nos IDs
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE id IN ($placeholders)");
        $stmt->execute($cart_ids);
        $produtos_no_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $produtos_no_carrinho = [];
    }

} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
    <link rel="icon" href="imagens/ha.png" type="image/png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="brand">Minha Loja</a>
            <div class="nav-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Início
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Carrinho de Compras</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($produtos_no_carrinho)): ?>
            <div class="cart-items">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Total</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        <?php foreach ($produtos_no_carrinho as $produto): ?>
                            <?php
                            $quantidade = $_SESSION['cart'][$produto['id']]['quantity'];
                            $total_item = $produto['preco'] * $quantidade;
                            $total += $total_item;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($produto['nome']) ?></td>
                                <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                <td><?= $quantidade ?></td>
                                <td>R$ <?= number_format($total_item, 2, ',', '.') ?></td>
                                <td>
                                    <a href="carrinho.php?remover_id=<?= $produto['id'] ?>" class="btn btn-danger">Remover</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <strong>Total: R$ <?= number_format($total, 2, ',', '.') ?></strong>
                </div>

                <div class="cart-actions">
                    <a href="index.php" class="btn btn-secondary">Voltar às Compras</a>
                    <a href="finalizar_compra.php" class="btn btn-success">Finalizar Compra</a>
                </div>
            </div>
        <?php else: ?>
            <p>Seu carrinho está vazio.</p>
            <a href="index.php" class="btn btn-primary">Voltar para a loja</a>
        <?php endif; ?>
    </div>
</body>
</html>