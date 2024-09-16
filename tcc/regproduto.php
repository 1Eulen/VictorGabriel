<?php
// Arquivo: regproduto.php
// Página de cadastro
include "config.php";

// Verifica se o formulário foi enviado
if (isset($_POST["enviar"])) {
    // Recebe os dados do formulário e sanitiza
    $nome_produto = htmlspecialchars(trim($_POST["nome_produto"]));
    $descricao = htmlspecialchars(trim($_POST["descricao"]));
    $quantidade = filter_var(trim($_POST["quantidade"]), FILTER_VALIDATE_INT);
    $preco = filter_var(trim($_POST["preco"]), FILTER_VALIDATE_FLOAT);
    $fotoproduto = $_FILES["fotoproduto"]; // Recebe o arquivo da foto do produto

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
    if (empty($fotoproduto["name"][0])) {
        $erros[] = "Pelo menos uma foto do produto é obrigatória.";
    }


    if (empty($erros)) {
        // Cria uma pasta com o nome do produto se não existir
        $pasta_produto = "imagens/" . preg_replace('/[^a-zA-Z0-9]/', '_', $nome_produto);
        if (!file_exists($pasta_produto)) {
            mkdir($pasta_produto, 0777, true);
        }

        // Array para armazenar os nomes dos arquivos das fotos
        $nomes_arquivos = array();
        
        // Processa cada arquivo de foto
        foreach ($fotoproduto["name"] as $index => $nome_foto) {
            if ($fotoproduto["error"][$index] == 0) {
                if ($fotoproduto["size"][$index] <= 2 * 1024 * 1024 && in_array($fotoproduto["type"][$index], array("image/jpeg", "image/png"))) {
                    $extensao = pathinfo($nome_foto, PATHINFO_EXTENSION);
                    $nome_arquivo = uniqid() . "." . $extensao;
                    $caminho_arquivo = $pasta_produto . "/" . $nome_arquivo;
                    
                    if (move_uploaded_file($fotoproduto["tmp_name"][$index], $caminho_arquivo)) {
                        $nomes_arquivos[] = $nome_arquivo;
                    } else {
                        $erros[] = "Não foi possível salvar a foto " . $nome_foto;
                    }
                } else {
                    $erros[] = "A foto " . $nome_foto . " deve ter no máximo 2 MB e ser uma imagem JPG ou PNG.";
                }
            } else {
                $erros[] = "Ocorreu um erro ao enviar a foto " . $nome_foto;
            }
        }

    
        if (empty($erros)) {
            // Concatena os nomes dos arquivos separados por vírgula
            $fotos = implode(",", $nomes_arquivos);
            
            $sql = "INSERT INTO produtos (nome_produto, descricao, quantidade, preco, foto) VALUES (:nome_produto, :descricao, :quantidade, :preco, :foto)";
            $stmt = $config->prepare($sql);
            $stmt->bindParam(":nome_produto", $nome_produto);
            $stmt->bindParam(":descricao", $descricao);
            $stmt->bindParam(":quantidade", $quantidade);
            $stmt->bindParam(":preco", $preco);
            $stmt->bindParam(":foto", $fotos);

            // Executa a consulta SQL
            try {
                $stmt->execute();
                $sucesso = "Produto cadastrado com sucesso.";
            } catch (PDOException $e) {
                $erros[] = "Erro ao cadastrar o produto: " . $e->getMessage();
            }
        }
    }
}
?>
<html>
<head>
    <style>
        body {
            background-color: black;
        }
    </style>
    <title>Cadastro de Produtos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
</head>
<body>
    <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="pagina.html">
                <img src="icon.png" alt="Logo do site">
            </a>
        </div>
        <div class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="regproduto.php">Cadastro de produtos</a>
                <a class="navbar-item" href="produtos.php">Exibição Produtos</a>
                <a class="navbar-item" href="gerenciarprodutos.php">Gerenciar Produtos</a>
            </div>
        </div>
    </nav>
    <section class="section">
        <div class="container">
            <h1 class="title has-text-white">Cadastro de Produtos</h1>
            <p class="subtitle has-text-white">Preencha o formulário abaixo para cadastrar um produto.</p>
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
            <form action="regproduto.php" method="post" enctype="multipart/form-data">
                <div class="field">
                    <label class="label has-text-white">Nome do Produto</label>
                    <div class="control">
                        <input class="input" type="text" name="nome_produto" value="<?php echo isset($nome_produto) ? htmlspecialchars($nome_produto) : ''; ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label has-text-white">Descrição</label>
                    <div class="control">
                        <input class="input" type="text" name="descricao" value="<?php echo isset($descricao) ? htmlspecialchars($descricao) : ''; ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label has-text-white">Quantidade</label>
                    <div class="control">
                        <input class="input" type="number" name="quantidade" value="<?php echo isset($quantidade) ? htmlspecialchars($quantidade) : ''; ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label has-text-white">Preço</label>
                    <div class="control">
                        <input class="input" type="text" name="preco" value="<?php echo isset($preco) ? htmlspecialchars($preco) : ''; ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label has-text-white">Fotos do Produto</label>
                    <div class="control">
                        <input class="input" type="file" name="fotoproduto[]" accept="image/jpeg, image/png" multiple>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <input class="button is-white is-rounded is-outlined" type="submit" name="enviar" value="Enviar">
                    </div>
                </div>
            </form>
        </div>
    </section>
</body>
</html>
