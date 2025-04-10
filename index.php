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

    // Busca todas as categorias para o menu lateral
    $stmt_categorias = $conn->prepare("SELECT * FROM categorias ORDER BY nome");
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se há filtro por categoria
    $categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    
    // Monta a consulta SQL base
    $sql = "SELECT p.*, c.nome AS categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.quantidade > 0";
    
    // Adiciona filtro por categoria se existir
    if ($categoria_filtro && is_numeric($categoria_filtro)) {
        $sql .= " AND p.categoria_id = :categoria_id";
    }
    
    // Prepara e executa a consulta
    $stmt = $conn->prepare($sql);
    
    if ($categoria_filtro && is_numeric($categoria_filtro)) {
        $stmt->bindParam(':categoria_id', $categoria_filtro, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lógica para adicionar produto ao carrinho (mantida igual)
    if (isset($_GET['add_to_cart'])) {
        $produto_id = $_GET['add_to_cart'];

        $stmt_estoque = $conn->prepare("SELECT quantidade FROM produtos WHERE id = ?");
        $stmt_estoque->execute([$produto_id]);
        $estoque = $stmt_estoque->fetchColumn();

        if ($estoque > 0) {
            if (!isset($_SESSION['cart'][$produto_id])) {
                $_SESSION['cart'][$produto_id] = [
                    'quantity' => 1
                ];
            } else {
                if ($_SESSION['cart'][$produto_id]['quantity'] < $estoque) {
                    $_SESSION['cart'][$produto_id]['quantity']++;
                } else {
                    $_SESSION['error'] = "Quantidade indisponível em estoque!";
                }
            }
        } else {
            $_SESSION['error'] = "Produto esgotado!";
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
    <title>Moda Fashion - Loja de Roupas</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos adicionais para o layout com sidebar */
        .main-content {
            display: flex;
            gap: 2rem;
        }
        
        .sidebar {
            width: 250px;
            flex-shrink: 0;
        }
        
        .products-container {
            flex-grow: 1;
        }
        
        .category-list {
            list-style: none;
            padding: 0;
        }
        
        .category-item {
            margin-bottom: 0.5rem;
        }
        
        .category-link {
            display: block;
            padding: 0.5rem 1rem;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .category-link:hover, .category-link.active {
            background-color: #f0f0f0;
            color: #007bff;
        }
        
        .category-link.active {
            font-weight: bold;
        }
        
        .clear-filter {
            display: block;
            margin-top: 1rem;
            color: #666;
            text-decoration: none;
        }
        
        .clear-filter:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="brand">Moda Fashion</a>
            <div class="nav-actions">
                <a href="carrinho.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span>(<?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?>)</span>
                    <?php endif; ?>
                </a>
                <a href="add_product.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Adicionar Roupa
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
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <h1 style="margin: 2rem 0 1rem;">Nossa Coleção</h1>
        
        <div class="main-content">
            <!-- Sidebar com filtro de categorias -->
            <div class="sidebar">
                <h3>Categorias</h3>
                <ul class="category-list">
                    <li class="category-item">
                        <a href="index.php" class="category-link <?= !isset($_GET['categoria']) ? 'active' : '' ?>">
                            Todas as Categorias
                        </a>
                    </li>
                    <?php foreach ($categorias as $categoria): ?>
                    <li class="category-item">
                        <a href="index.php?categoria=<?= $categoria['id'] ?>" 
                           class="category-link <?= isset($_GET['categoria']) && $_GET['categoria'] == $categoria['id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($categoria['nome']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if (isset($_GET['categoria'])): ?>
                    <a href="index.php" class="clear-filter">
                        <i class="fas fa-times"></i> Limpar filtro
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Lista de produtos -->
            <div class="products-container">
                <?php if (empty($produtos)): ?>
                    <div class="alert alert-info">
                        Nenhum produto encontrado nesta categoria.
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($produtos as $produto): ?>
                        <div class="product-card">
                            <img src="imagens/<?= htmlspecialchars($produto['imagem']) ?>" 
                                 class="product-image" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>">
                            <div class="product-body">
                                <span class="product-category">
                                    <?= htmlspecialchars($produto['categoria_nome']) ?>
                                </span>
                                <h3 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h3>
                                <div class="product-details">
                                    <span class="product-size">Tamanho: <?= htmlspecialchars($produto['tamanho']) ?></span>
                                    <span class="product-color">Cor: <?= htmlspecialchars($produto['cor']) ?></span>
                                </div>
                                <p><?= htmlspecialchars($produto['descricao']) ?></p>
                                <div class="product-info">
                                    <span class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                                    <span class="product-stock">Disponível: <?= htmlspecialchars($produto['quantidade']) ?></span>
                                </div>
                                <div class="product-actions">
                                    <a href="index.php?add_to_cart=<?= $produto['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-cart-plus"></i> Adicionar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $conn = null; ?>
</body>
</html>