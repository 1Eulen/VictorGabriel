<?php
// Página de exibição dos produtos cadastrados para um e-commerce de roupas
include "config.php";

// Recebe os dados do filtro, se houver
if (isset($_GET["filtro"])) {
    $filtro = $_GET["filtro"];
    $valor = $_GET["valor"];
}

// Prepara a consulta SQL para selecionar os dados da tabela produtos
$sql = "SELECT * FROM produtos";
if (isset($filtro) && isset($valor)) {
    // Adiciona uma cláusula WHERE na consulta SQL, de acordo com o filtro escolhido
    switch ($filtro) {
        case "nome_produto":
            $sql .= " WHERE nome_produto LIKE :valor";
            break;
        case "descricao":
            $sql .= " WHERE descricao LIKE :valor";
            break;
        case "preco":
            $sql .= " WHERE preco <= :valor";
            break;
    }
}
$stmt = $config->prepare($sql);
if (isset($filtro) && isset($valor)) {
    // Vincula o valor do filtro ao parâmetro da consulta SQL
    if ($filtro == "preco") {
        $stmt->bindParam(":valor", $valor, PDO::PARAM_INT);
    } else {
        $valor = "%" . $valor . "%";
        $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
    }
}

// Executa a consulta SQL
try {
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao selecionar os produtos: " . $e->getMessage();
}
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
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            transition: opacity 0.3s ease-in-out;
        }
        .product-card img:hover {
            opacity: 0.8;
        }
        .price {
            font-size: 1.25em;
            color: #ff5722;
            margin: 10px 0;
        }
    </style>
    <title>Exibição de Produtos - Loja de Roupas</title>
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
                <a class="navbar-item" href="regproduto.php">Cadastro Produtos</a>
                <a class="navbar-item" href="produtos.php">Exibição produtos</a>
                <a class="navbar-item" href="gerenciarprodutos.php">Gerenciar Produtos</a>
            </div>
        </div>
    </nav>
    <section class="section">
        <div class="container">
            <h1 class="title">Nossos Produtos</h1>
            <p class="subtitle">Explore nossa seleção de roupas e encontre o que você procura.</p>
            <form action="produtos.php" method="get">
                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Filtrar por:</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                            <div class="control">
                                <div class="select">
                                    <select name="filtro">
                                        <option value="nome_produto" <?php echo isset($filtro) && $filtro == "nome_produto" ? "selected" : ""; ?>>Nome do Produto</option>
                                        <option value="descricao" <?php echo isset($filtro) && $filtro == "descricao" ? "selected" : ""; ?>>Descrição</option>
                                        <option value="preco" <?php echo isset($filtro) && $filtro == "preco" ? "selected" : ""; ?>>Preço Máximo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input" type="text" name="valor" value="<?php echo isset($valor) ? ($valor) : ""; ?>">
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="button is-black is-rounded is-outlined" type="submit" value="Filtrar">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="columns is-multiline">
                <?php
                // Verifica se há produtos cadastrados
                if (isset($produtos) && !empty($produtos)) {
                    // Exibe os produtos em cartões
                    foreach ($produtos as $produto) {
                        echo "<div class='column is-one-quarter'>";
                        echo "<div class='product-card'>";

                        // Supondo que as imagens sejam separadas por vírgula
                        $imagens = explode(',', $produto["foto"]);
                        $imagem_principal = isset($imagens[0]) ? $imagens[0] : 'default.jpg'; // Exibe uma imagem padrão caso não tenha imagem
                        $imagem_hover = isset($imagens[1]) ? $imagens[1] : $imagem_principal; // Usa a mesma imagem se não houver segunda

                        // Imagem do Produto com efeito hover
                        echo "<img class='produto-imagem' src='imagens/" . ($produto["nome_produto"]) . "/" . ($imagem_principal) . "' 
                            data-hover='imagens/" . ($produto["nome_produto"]) . "/" . ($imagem_hover) . "' 
                            alt='Imagem do produto'>";

                        echo "<h2 class='title is-4'>" . ($produto["nome_produto"]) . "</h2>";
                        echo "<p class='subtitle is-6'>" . ($produto["descricao"]) . "</p>";
                        echo "<p class='price'>R$ " . number_format($produto["preco"], 2, ',', '.') . "</p>";
                        echo "<a class='button is-primary is-rounded' href='comprar.php?id=" . ($produto["id"]) . "'>Comprar</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    // Exibe uma mensagem de que não há produtos cadastrados
                    echo "<div class='notification is-warning'>";
                    echo "Não há produtos cadastrados no sistema.";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </section>
    <script>
        document.querySelectorAll('.produto-imagem').forEach(img => {
            const originalSrc = img.src;
            const hoverSrc = img.getAttribute('data-hover');

            img.addEventListener('mouseenter', () => {
                img.src = hoverSrc;
            });

            img.addEventListener('mouseleave', () => {
                img.src = originalSrc;
            });
        });
    </script>
</body>
</html>
