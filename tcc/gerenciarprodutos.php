<?php
// Arquivo: gerenciarprodutos.php
// Página para listar, editar e deletar produtos

include "config.php";

// Verifica se um produto deve ser excluído
if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    // Recupera o nome do produto e a foto para deletar as imagens associadas
    $sql = "SELECT nome_produto, foto FROM produtos WHERE id = :id";
    $stmt = $config->prepare($sql);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        // Deleta as fotos do produto
        $fotos = explode(',', $produto["foto"]);
        $pastaProduto = "imagens/" . $produto["nome_produto"];
        foreach ($fotos as $foto) {
            $caminhoArquivo = $pastaProduto . "/" . $foto;
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
        }
        // Remove a pasta do produto se estiver vazia
        if (is_dir($pastaProduto) && count(scandir($pastaProduto)) == 2) { // '.' e '..'
            rmdir($pastaProduto);
        }

        // Remove o produto do banco de dados
        $sql = "DELETE FROM produtos WHERE id = :id";
        $stmt = $config->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $sucesso = "Produto deletado com sucesso.";
    } else {
        $erros[] = "Produto não encontrado.";
    }
}

// Verifica se um produto deve ser atualizado
if (isset($_POST["atualizar"])) {
    $id = intval($_POST["id"]);
    $nome_produto = htmlspecialchars($_POST["nome_produto"]);
    $descricao = htmlspecialchars($_POST["descricao"]);
    $quantidade = filter_var($_POST["quantidade"], FILTER_VALIDATE_INT);
    $preco = filter_var($_POST["preco"], FILTER_VALIDATE_FLOAT);

    // Valida os dados do formulário
    $erros = array();
    if (empty($nome_produto)) {
        $erros[] = "O nome do produto é obrigatório.";
    }
    if (empty($descricao)) {
        $erros[] = "A descrição do produto é obrigatória.";
    }
    if ($quantidade === false || $quantidade <= 0) {
        $erros[] = "A quantidade de produtos deve ser um número positivo.";
    }
    if ($preco === false || $preco <= 0) {
        $erros[] = "O preço deve ser um valor numérico positivo.";
    }

    // Se não houver erros, atualiza os dados no banco de dados
    if (empty($erros)) {
        $sql = "UPDATE produtos SET nome_produto = :nome_produto, descricao = :descricao, quantidade = :quantidade, preco = :preco WHERE id = :id";
        $stmt = $config->prepare($sql);
        $stmt->bindParam(":nome_produto", $nome_produto);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":preco", $preco);
        $stmt->bindParam(":id", $id);

        try {
            $stmt->execute();
            $sucesso = "Produto atualizado com sucesso.";
        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar o Produto: " . $e->getMessage();
        }
    }
}

// Prepara a consulta SQL para selecionar todos os produtos
$sql = "SELECT * FROM produtos";
$stmt = $config->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
<head>
    <style>
        body {
            background-color: #f5f5f5;
        }
        .product-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
            padding: 15px;
            text-align: center;
            position: relative;
        }
        .product-card img {
            max-width: 100%;
            height: auto;
        }
        .price {
            font-size: 1.25em;
            color: #ff5722;
            margin: 10px 0;
        }
        .carousel {
            position: relative;
            overflow: hidden;
        }
        .carousel-images {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        .carousel-image {
            min-width: 100%;
            box-sizing: border-box;
        }
        .carousel-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        .carousel-button.prev {
            left: 10px;
        }
        .carousel-button.next {
            right: 10px;
        }
        .delete-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            text-align: center;
            line-height: 30px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: darkred;
        }
    </style>
    <title>Gerenciar Produtos - Loja de Roupas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
</head>
<body>
    <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="pagina.html">
                <img src="icon.png" alt="Logo da loja">
            </a>
        </div>
        <div class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="pagina.html">Início</a>
                <a class="navbar-item" href="regproduto.php">Cadastrar produtos</a>
                <a class="navbar-item" href="produtos.php">Exibir produtos</a>
                <a class="navbar-item" href="gerenciarprodutos.php">Gerenciar Produtos</a>
            </div>
        </div>
    </nav>
    <section class="section">
        <div class="container">
            <h1 class="title">Gerenciar Produtos</h1>
            <?php
            // Exibe as mensagens de erro ou sucesso, se houver
            if (isset($erros) && !empty($erros)) {
                echo "<div class='notification is-danger'>";
                echo "<ul>";
                foreach ($erros as $erro) {
                    echo "<li>$erro</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
            if (isset($sucesso) && !empty($sucesso)) {
                echo "<div class='notification is-success'>";
                echo $sucesso;
                echo "</div>";
            }
            ?>
            <div class="columns is-multiline">
                <?php
                // Verifica se há produtos cadastrados
                if (isset($produtos) && !empty($produtos)) {
                    // Exibe os produtos em cartões
                    foreach ($produtos as $produto) {
                        echo "<div class='column is-one-quarter'>";
                        echo "<div class='product-card'>";
                        
                        // Carrossel de Imagens
                        $imagens = explode(',', $produto["foto"]); // Supondo que as imagens sejam separadas por vírgula
                        echo "<div class='carousel'>";
                        echo "<div class='carousel-images'>";
                        foreach ($imagens as $imagem) {
                            echo "<img class='carousel-image' src='imagens/" . htmlspecialchars($produto["nome_produto"]) . "/" . htmlspecialchars($imagem) . "' alt='Imagem do produto'>";
                        }
                        echo "</div>";
                        echo "<button class='carousel-button prev' onclick='moveSlide(-1)'>❮</button>";
                        echo "<button class='carousel-button next' onclick='moveSlide(1)'>❯</button>";
                        echo "</div>";

                        // Formulário de edição
                        echo "<form action='gerenciarprodutos.php' method='post'>";
                        echo "<input type='hidden' name='id' value='" . htmlspecialchars($produto["id"]) . "'>";
                        echo "<div class='field'>";
                        echo "<label class='label'>Nome do Produto</label>";
                        echo "<div class='control'>";
                        echo "<input class='input' type='text' name='nome_produto' value='" . htmlspecialchars($produto["nome_produto"]) . "' required>";
                        echo "</div>";
                        echo "</div>";
                        
                        echo "<div class='field'>";
                        echo "<label class='label'>Descrição</label>";
                        echo "<div class='control'>";
                        echo "<textarea class='textarea' name='descricao' required>" . htmlspecialchars($produto["descricao"]) . "</textarea>";
                        echo "</div>";
                        echo "</div>";
                        
                        echo "<div class='field'>";
                        echo "<label class='label'>Quantidade</label>";
                        echo "<div class='control'>";
                        echo "<input class='input' type='number' name='quantidade' value='" . htmlspecialchars($produto["quantidade"]) . "' required>";
                        echo "</div>";
                        echo "</div>";
                        
                        echo "<div class='field'>";
                        echo "<label class='label'>Preço</label>";
                        echo "<div class='control'>";
                        echo "<input class='input' type='number' step='0.01' name='preco' value='" . htmlspecialchars($produto["preco"]) . "' required>";
                        echo "</div>";
                        echo "</div>";
                        
                        echo "<button class='button is-primary' type='submit' name='atualizar'>Atualizar</button>";
                        echo "</form>";
                        
                        // Botão de exclusão
                        echo "<a href='gerenciarprodutos.php?action=delete&id=" . htmlspecialchars($produto["id"]) . "' class='delete-button'>X</a>";
                        
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Nenhum produto encontrado.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <script>
        let slideIndex = 0;
        function showSlides(n) {
            let slides = document.querySelectorAll(".carousel-image");
            if (n >= slides.length) { slideIndex = 0; }
            if (n < 0) { slideIndex = slides.length - 1; }
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex].style.display = "block";
        }

        function moveSlide(n) {
            showSlides(slideIndex += n);
        }

        // Inicializa o carrossel
        showSlides(slideIndex);
    </script>
</body>
</html>
