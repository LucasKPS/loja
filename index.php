<?php
session_start();

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_loja";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para buscar todos os produtos com suas categorias
    $stmt = $conn->prepare("SELECT p.*, c.nome AS categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lógica para adicionar produto ao carrinho
    if (isset($_GET['add_to_cart'])) {
        $produto_id = $_GET['add_to_cart'];

        // Verifica se o produto já está no carrinho
        if (!isset($_SESSION['cart'][$produto_id])) {
            $_SESSION['cart'][$produto_id] = [
                'quantity' => 1
            ];
        } else {
            // Se já existe, aumenta a quantidade
            $_SESSION['cart'][$produto_id]['quantity']++;
        }
    }

} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Loja - Produtos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="brand">Minha Loja</a>
            <div class="nav-actions">
                <a href="carrinho.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span>(<?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?>)</span>
                    <?php endif; ?>
                </a>
                <a href="add_product.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Adicionar Produto
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <h1 style="margin: 2rem 0 1rem;">Nossos Produtos</h1>
        
        <div class="products-grid">
            <?php foreach ($produtos as $produto): ?>
            <div class="product-card">
                <img src="imagens/<?= htmlspecialchars($produto['imagem']) ?>" 
                     class="product-image" 
                     alt="<?= htmlspecialchars($produto['nome']) ?>">
                <div class="product-body">
                    <!-- Usando categoria_nome agora -->
                    <span class="product-category">
                        <?= htmlspecialchars($produto['categoria_nome']) ?>
                    </span>
                    <h3 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h3>
                    <p><?= htmlspecialchars($produto['descricao']) ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                        <span class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                        <a href="index.php?add_to_cart=<?= $produto['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php $conn = null; ?>
</body>
</html>
