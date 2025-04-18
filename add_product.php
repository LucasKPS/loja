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

    // Lógica para adicionar um produto
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $categoria_id = $_POST['categoria'];
        $preco = $_POST['preco'];
        $quantidade = $_POST['quantidade'];
        $tamanho = $_POST['tamanho'];
        $cor = $_POST['cor'];
        $imagem = $_FILES['imagem']['name'];
        
        // Verifica se o arquivo foi enviado
        if (!empty($imagem)) {
            $target_dir = "imagens/";
            $target_file = $target_dir . basename($imagem);
            move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file);
        }

        // Inserir no banco de dados
        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, categoria_id, preco, quantidade, tamanho, cor, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $categoria_id, $preco, $quantidade, $tamanho, $cor, $imagem]);

        header("Location: index.php?success=Produto adicionado com sucesso!");
        exit();
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
    <title>Adicionar Roupa</title>
    <link rel="icon" href="imagens/ha.png" type="image/png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Adicionar Nova Roupa</h1>
    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" required><br>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" required></textarea><br>

        <label for="categoria">Categoria:</label>
        <select name="categoria" required>
            <?php
            $stmt = $conn->prepare("SELECT * FROM categorias");
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categorias as $categoria) {
                echo "<option value='{$categoria['id']}'>{$categoria['nome']}</option>";
            }
            ?>
        </select><br>

        <label for="preco">Preço:</label>
        <input type="number" step="0.01" name="preco" required><br>

        <label for="quantidade">Quantidade em Estoque:</label>
        <input type="number" name="quantidade" min="0" required><br>

        <label for="tamanho">Tamanho:</label>
        <select name="tamanho" required>
            <option value="PP">PP</option>
            <option value="P">P</option>
            <option value="M" selected>M</option>
            <option value="G">G</option>
            <option value="GG">GG</option>
            <option value="XG">XG</option>
        </select><br>

        <label for="cor">Cor:</label>
        <input type="text" name="cor" required><br>

        <label for="imagem">Imagem do Produto:</label>
        <input type="file" name="imagem" accept="image/*" required><br>

        <button type="submit">Adicionar Roupa</button>
    </form>
</body>
</html>