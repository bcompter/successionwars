-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 30, 2021 at 09:40 AM
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

--
-- Dumping data for table `card_types`
--

INSERT INTO `card_types` (`type_id`, `text`, `title`, `phase`) VALUES
(1, 'Advance Technology by 3.', 'Technology +3', 'Any'),
(2, 'Advance Technology by 5.', 'Technology +5', 'Any'),
(3, 'Roll one 7 strength attack against an enemy combat unit.', 'Death Commando', 'Any'),
(4, 'Look at another player\'s hand.', 'Spy', 'Any'),
(5, 'Build a Jump Ship 3 at no cost.  May be played only during the Production phase.', 'Jump Ship', 'Production'),
(6, 'Build a new unit at no cost.  Must be played during the production phase.', 'Star League Cache', 'Production'),
(7, 'Rebuild a destroyed unit to prewar strength at normal cost.  May be played only during production. ', 'Star League Construction', 'Production'),
(8, 'Regional Combat Bonus of +2 for the current turn. May be played anytime.', 'Star League Combat Bonus', 'Any'),
(9, '-2 on combat rolls in affected region for the length of a round. May be played anytime.', 'Comstar Regional Interdict', 'Any'),
(10, 'Create a new mercenary unit in a friendly territory. Playable at any time.', 'Mercenary', 'Any'),
(11, 'Look at another player\'s hand and steal a card.', 'Spy, Steal', 'Any'),
(12, 'Bribe an opposing leader for free.', 'Bribe', 'Any'),
(13, '+2 to any bribe attempt.', 'Blackmail', 'Any'),
(14, 'End a Mercenary contract.  Services immediately go to bid.', 'Contract Ends', 'Any'),
(15, '-2 to all combat rolls by the affected House for two rounds. May be played at any time.', 'Comstar House Interdict', 'Any'),
(16, 'Lift a Comstar House Interdict on any player. May be played at any time. Does not remove a Comstar Terra Interdict.', 'Lift Comstar House Interdict', 'Any'),
(17, 'Target player loses 3 technology.', 'Sabotage', 'Any'),
(18, 'Target player loses 3 technology.  You gain 3 technology.', 'Espionage', 'Any'),
(20, '+10 CBills', 'Economic Boom', 'Any'),
(21, 'Roll a strength 6 attack against one target in a contested border region. May be played only during the Combat phase.', 'Bombardment', 'Combat'),
(22, 'Roll a strength 4 attack against one target in an enemy controlled and contested border region. May be played only during the Combat phase.', 'Air Raid', 'Combat'),
(23, 'All players suffer House Interdict for two rounds.', 'HPG Blackout', 'Any'),
(24, '+7 Technology, remove from the game after use.', 'Star League Memory Core', 'Any'),
(25, 'All players -3 Technology.', 'Holy Shroud', 'Any'),
(26, '+25 CBills.  Remove from the game after use.', 'Germanium Supply', 'Any'),
(27, 'Target player -3 CBills.', 'Economic Sabotage', 'Any'),
(28, 'Target player -3 CBills.  You gain 3 CBills.', 'Embezzlement', 'Any'),
(29, 'Free Technology roll.', 'Research', 'Any'),
(30, 'Target player -5 Technology.  -3 if they are at or below 0 Technology.', 'Holy Shroud', 'Any'),
(31, 'Target player -3 Technology.  -1 if they are at or below 0 Technology.', 'Holy Shroud', 'Any'),
(32, '-2 to all combat rolls by the affected House for one round. May be played at any time.', 'Comstar House Interdict', 'Any'),
(33, 'All other players give you 1 CBill if able.', 'Economic Fraud', 'Any'),
(34, 'Pay 10 CBills. Target a contested region in which you have combat units. Roll a strength 7 attack on all enemy combat units present. Automatically damage any factory. Territory resource -1 permanently. May be played only during the Combat phase.', 'Nuclear Strike', 'Combat'),
(35, 'Pay 5 CBills. Upgrade target combat unit to strength 4. May be played only during the Production phase.', 'Reinforcements', 'Production'),
(36, 'Pay 5 CBills. Increase the resources of a target Region by two if it is below four resources. May be played only during the Production phase.', 'Regional Improvements', 'Production'),
(37, 'Target Jumpship +1 Movement. May be played only during the Movement phase.', 'Fast Recharge', 'Movement');

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'members', 'General User'),
(3, 'Super User', 'Super User');
COMMIT;


INSERT INTO `admin` (`admin_id`, `maintenance_mode`, `allow_register`, `dashboard_message`) VALUES ('1', '0', '1', 'No Message');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
