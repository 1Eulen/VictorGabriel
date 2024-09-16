
CREATE DATABASE victor;
USE victor;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(255) NOT NULL,
    descricao TEXT,
    quantidade INT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    fotoproduto VARCHAR(255),
    foto VARCHAR(255)
);
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    sexo ENUM('Masculino', 'Feminino', 'Outro'),
    data_nasc DATE,
    cidade VARCHAR(100),
    estado VARCHAR(100),
    endereco VARCHAR(255)
);
