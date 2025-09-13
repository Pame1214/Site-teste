-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql208.byethost22.com
-- Tempo de geração: 08/09/2025 às 13:29
-- Versão do servidor: 11.4.7-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `b22_39633188_dws2_pame`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `midias`
--

CREATE TABLE `midias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(250) NOT NULL,
  `ano` int(11) NOT NULL,
  `genero` varchar(150) NOT NULL,
  `poster` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `midias`
--

INSERT INTO `midias` (`id`, `titulo`, `ano`, `genero`, `poster`) VALUES
(1, 'Ainda Estou Aqui', 2025, 'Drama', 'https://omundodiplomatico.com.br/wp-content/uploads/2025/01/01.jpg'),
(3, 'Anora', 2025, 'Drama', 'https://www.termometrooscar.com/uploads/1/4/8/8/1488234/gt06isuw8aahiwg_orig.jpg'),
(4, 'The Last of Us', 2025, 'Drama / Pós-apocalíptico', 'https://example.com/poster/the_last_of_us.jpg'),
(6, 'Facere ipsum praese', 1965, 'Suspense', 'https://www.hyqiwilajipuwo.me.uk'),
(7, 'Libero voluptas et s', 1947, 'Aventura', 'https://www.hysufuxesaka.cc');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `midias`
--
ALTER TABLE `midias`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `midias`
--
ALTER TABLE `midias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
