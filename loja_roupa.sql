-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08-Dez-2024 às 23:12
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `loja_roupa`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `imagens`
--

CREATE TABLE `imagens` (
  `id_imagem` int(11) NOT NULL,
  `imagens` varchar(255) NOT NULL,
  `id_produtos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL COMMENT 'tabela marcas',
  `nome_marca` varchar(100) NOT NULL COMMENT 'nome da marca do produto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nome_marca`) VALUES
(1, 'Nike'),
(2, 'Supreme'),
(3, 'Corteiz'),
(4, 'CDG'),
(5, 'Skepta'),
(6, 'Burberry'),
(7, 'Stüssy'),
(8, 'Evisu'),
(9, 'Moncler'),
(10, 'CP Company'),
(11, 'Bape');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id_produtos` int(11) NOT NULL COMMENT 'tabela produtos',
  `nome_produto` varchar(100) NOT NULL COMMENT 'nome do produto',
  `categoria` varchar(50) NOT NULL COMMENT 'categoria do produto',
  `tamanho` varchar(55) NOT NULL COMMENT 'tamanho do produto',
  `cor` varchar(50) NOT NULL COMMENT 'cor do produto',
  `preco` decimal(10,2) NOT NULL COMMENT 'preco do produto',
  `caminho_imagem` varchar(255) NOT NULL,
  `caminho_imagem_hover` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id_produtos`, `nome_produto`, `categoria`, `tamanho`, `cor`, `preco`, `caminho_imagem`, `caminho_imagem_hover`) VALUES
(1, 'Nike Supreme Tn (41)', 'Shoes', '41.0', 'Red', 266.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/Nike Tn Supreme.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/Nike Tn 2.webp'),
(2, 'Nike Corteiz 95s (46)', 'Shoes', '46.0', 'Black', 560.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/Nike 95s Corteiz .webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/Nike 95s Corteiz 2 .webp'),
(3, 'Nike CDG Shox (36.5)', 'Shoes', '36.5', 'White', 323.50, 'img/IMAGENS DE PRODUTOS PARA A PAP/Nike Shox CDG.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/Nike Shox CDG 2.webp'),
(4, 'Nike Skepta Tailwinds (43)', 'Shoes', '43.0', 'Red', 380.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/nike skepta vermelho.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/nike skepta vermelho 2.webp'),
(5, 'Burberry Scarf', 'Accesories', 'One size', 'Beige', 78.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/cascol burberry.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/cascol burberry 2.webp'),
(6, 'Burberry Scarf Blue', 'Accesories', 'One size', 'Blue', 78.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/cascol burberry azul.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/cascol burberry azul 2.webp'),
(7, 'Stüssy Nike Joggers (S)', 'Bottoms', 'S', 'Grey', 130.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/nike stussy joggers cinzento.jpg', 'img/IMAGENS DE PRODUTOS PARA A PAP/nike stussy joggers cinzento 2.jpg'),
(8, 'Stüssy Nike Joggers (XL)', 'Bottoms', 'XL', 'Blue', 140.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/joggers nike stussy azul.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/joggers nike stussy azul 2.webp'),
(9, 'Evisu Daicock Jeans (34)', 'Bottoms', '34', 'Black and Yellow', 250.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/calcas evisu M.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/calcas evisu M 2.webp'),
(10, 'Evisu Multipocket Jeans (32)', 'Bottoms', '32', 'Blue', 395.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/evisu multipocket.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/evisu multipocket 2.webp'),
(11, 'Nike Shox Trackies (XL)', 'Bottoms', 'XL', 'Black', 100.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/calcas nike shox.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/calcas nike shox 2.webp'),
(13, 'Bape T-shirt (M)', 'Tees', 'M', 'Black', 65.00, 'img/IMAGENS DE PRODUTOS PARA A PAP/Bape T-shirt.webp', 'img/IMAGENS DE PRODUTOS PARA A PAP/Bape T-shirt 2.webp');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos_marcas`
--

CREATE TABLE `produtos_marcas` (
  `id_produtos_marcas` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `id_marcas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos_marcas`
--

INSERT INTO `produtos_marcas` (`id_produtos_marcas`, `id_produto`, `id_marcas`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(4, 2, 3),
(5, 3, 1),
(6, 3, 4),
(7, 4, 1),
(8, 4, 5),
(9, 5, 6),
(10, 6, 6),
(11, 7, 1),
(12, 7, 7),
(13, 8, 1),
(14, 8, 7),
(15, 9, 8),
(16, 10, 8),
(17, 11, 1),
(18, 13, 11);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `imagens`
--
ALTER TABLE `imagens`
  ADD PRIMARY KEY (`id_imagem`),
  ADD KEY `fk_id_produtos` (`id_produtos`);

--
-- Índices para tabela `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marca`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id_produtos`);

--
-- Índices para tabela `produtos_marcas`
--
ALTER TABLE `produtos_marcas`
  ADD PRIMARY KEY (`id_produtos_marcas`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_marcas` (`id_marcas`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `imagens`
--
ALTER TABLE `imagens`
  MODIFY `id_imagem` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tabela marcas', AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id_produtos` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tabela produtos', AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `produtos_marcas`
--
ALTER TABLE `produtos_marcas`
  MODIFY `id_produtos_marcas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `imagens`
--
ALTER TABLE `imagens`
  ADD CONSTRAINT `fk_id_produtos` FOREIGN KEY (`id_produtos`) REFERENCES `produtos` (`id_produtos`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `imagens_ibfk_1` FOREIGN KEY (`id_imagem`) REFERENCES `produtos` (`id_produtos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `produtos_marcas`
--
ALTER TABLE `produtos_marcas`
  ADD CONSTRAINT `produtos_marcas_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produtos`) ON DELETE CASCADE,
  ADD CONSTRAINT `produtos_marcas_ibfk_2` FOREIGN KEY (`id_marcas`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
