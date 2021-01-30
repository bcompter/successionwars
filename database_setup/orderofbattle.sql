-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 30, 2021 at 09:49 AM
-- Server version: 5.7.23-23
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `scrapyar_successionwars`
--

-- --------------------------------------------------------


--
-- Dumping data for table `orderofbattle`
--

INSERT INTO `orderofbattle` (`orderofbattle_id`, `name`, `description`, `user_id`, `draft`, `use_merc_phase`, `auto_factory_dmg_mod`, `destroy_jumpships`, `year`, `capitals_to_win`, `use_comstar`, `use_terra_interdict`, `use_terra_loot`, `world_id`) VALUES
(1, 'Fourth Succession War', '', 0, 0, 0, 0, 0, 3025, 4, 1, 1, 1, 1),
(2, 'Clan Invasion 3052', 'The Clans are sweeping through the Inner Sphere!', 46, 0, 0, 0, 0, 3052, 4, 1, 1, 1, 1),
(3, 'First Succession War', 'The first succession war provides a more level playing field for all players and a different cast of leaders and mercenaries to combat with.', 46, 0, 1, 1, 1, 2786, 4, 1, 1, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orderofbattle`
--
ALTER TABLE `orderofbattle`
  ADD PRIMARY KEY (`orderofbattle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orderofbattle`
--
ALTER TABLE `orderofbattle`
  MODIFY `orderofbattle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
