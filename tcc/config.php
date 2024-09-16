<?php

    $dbHost = 'localhost';
    $dbUsername = 'root';
    $dbPassword = '';
    $dbName = 'victor';
    
    
    $conexao = new mysqli($dbHost,$dbUsername,$dbPassword,$dbName);
    

    // if($conexao->connect_errno)
     //{
         //echo "Erro";
    // }
    // else
    // {
         //echo "Conexão efetuada com sucesso";
    // }
// Arquivo: config.php

try {
    // Defina as variáveis com as informações do seu banco de dados
    $host = 'localhost'; // ou o endereço do seu servidor de banco de dados
    $dbname = 'victor'; // substitua pelo nome do seu banco de dados
    $username = 'root'; // seu nome de usuário do banco de dados
    $password = ''; // sua senha do banco de dados

    // Cria uma nova conexão com o banco de dados usando PDO
    $config = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Defina o modo de erro do PDO para exceções
    $config->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Caso ocorra um erro na conexão, exibe a mensagem de erro
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>
