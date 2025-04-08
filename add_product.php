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

    // Lógica para adicionar um produto
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $categoria_id = $_POST['categoria']; // Aqui é o campo categoria_id
        $preco = $_POST['preco'];
        $imagem = $_FILES['imagem']['name'];
        
        // Verifica se o arquivo foi enviado
        if (!empty($imagem)) {
            $target_dir = "imagens/"; // Pasta onde as imagens serão armazenadas
            $target_file = $target_dir . basename($imagem);
            move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file);
        }

        // Inserir no banco de dados
        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, categoria_id, preco, imagem) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $categoria_id, $preco, $imagem]);

        // Redirecionar de volta para a página principal após o sucesso
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
    <title>Adicionar Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Adicionar Novo Produto</h1>
    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" required><br>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" required></textarea><br>

        <label for="categoria">Categoria:</label>
        <select name="categoria" required>
            <?php
            // Consulta para carregar as categorias do banco
            $stmt = $conn->prepare("SELECT * FROM categorias"); // Alterado para tabela de categorias
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categorias as $categoria) {
                echo "<option value='{$categoria['id']}'>{$categoria['nome']}</option>";
            }
            ?>
        </select><br>

        <label for="preco">Preço:</label>
        <input type="number" step="0.01" name="preco" required><br>

        <label for="imagem">Imagem do Produto:</label>
        <input type="file" name="imagem" accept="image/*" required><br>

        <button type="submit">Adicionar Produto</button>
    </form>
</body>
</html>
