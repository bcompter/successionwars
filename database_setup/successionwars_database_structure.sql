-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 30, 2021 at 09:21 AM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL DEFAULT '1',
  `maintenance_mode` tinyint(4) NOT NULL DEFAULT '0',
  `allow_register` tinyint(4) NOT NULL DEFAULT '0',
  `dashboard_message` varchar(500) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arrows`
--

CREATE TABLE `arrows` (
  `world_id` smallint(6) NOT NULL,
  `divs` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `post_id` int(11) NOT NULL,
  `status` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT 'Draft',
  `author_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE latin1_general_ci NOT NULL DEFAULT 'Untitled',
  `text` longtext COLLATE latin1_general_ci NOT NULL,
  `last_edit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title_url` varchar(200) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bugs`
--

CREATE TABLE `bugs` (
  `bug_id` int(11) NOT NULL,
  `title` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `description` longtext COLLATE latin1_general_ci NOT NULL,
  `status` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `is_bug` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bug_comments`
--

CREATE TABLE `bug_comments` (
  `bug_comment_id` int(11) NOT NULL,
  `bug_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` longtext COLLATE latin1_general_ci NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bug_karma`
--

CREATE TABLE `bug_karma` (
  `bug_karma_id` int(11) NOT NULL,
  `bug_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `card_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `being_played` tinyint(4) NOT NULL DEFAULT '0',
  `target_id` int(11) DEFAULT NULL,
  `traded` tinyint(4) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_types`
--

CREATE TABLE `card_types` (
  `type_id` int(11) NOT NULL,
  `text` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `title` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `phase` varchar(20) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `chat_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `message` varchar(300) COLLATE latin1_general_ci NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `to_player_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `ip_address` varchar(16) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combatbonus`
--

CREATE TABLE `combatbonus` (
  `combatbonus_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `combatunit_id` int(11) DEFAULT NULL,
  `source_id` int(11) NOT NULL,
  `source_type` int(11) NOT NULL,
  `ttl` mediumint(9) NOT NULL,
  `value` mediumint(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combatlog`
--

CREATE TABLE `combatlog` (
  `combatlog_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `territory_id` int(11) NOT NULL,
  `casualties_owed` int(11) NOT NULL DEFAULT '0',
  `casualties_declared` int(11) NOT NULL DEFAULT '0',
  `game_id` int(11) NOT NULL,
  `force_size` int(11) DEFAULT NULL,
  `use_force_size` tinyint(4) NOT NULL DEFAULT '1',
  `attack_modifier` tinyint(4) NOT NULL DEFAULT '0',
  `can_retreat` tinyint(4) NOT NULL DEFAULT '0',
  `is_retreat_allowed` tinyint(4) NOT NULL DEFAULT '1',
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combatunits`
--

CREATE TABLE `combatunits` (
  `combatunit_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `strength` int(11) NOT NULL,
  `prewar_strength` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `original_owner_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `loaded_in_id` int(11) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT '1',
  `being_built` tinyint(4) NOT NULL DEFAULT '1',
  `last_roll` smallint(6) NOT NULL DEFAULT '0',
  `die` tinyint(4) NOT NULL DEFAULT '0',
  `target_id` int(11) DEFAULT NULL,
  `is_rebuild` tinyint(4) NOT NULL DEFAULT '0',
  `is_merc` tinyint(4) NOT NULL DEFAULT '0',
  `is_conventional` tinyint(4) NOT NULL DEFAULT '0',
  `is_elemental` tinyint(4) NOT NULL DEFAULT '0',
  `is_garrison` tinyint(4) NOT NULL DEFAULT '0',
  `was_loaded` int(11) NOT NULL DEFAULT '0',
  `combine_with` int(11) DEFAULT NULL,
  `combined_by` int(11) DEFAULT NULL,
  `combo_broken` tinyint(4) NOT NULL DEFAULT '0',
  `can_undeploy` tinyint(4) NOT NULL DEFAULT '0',
  `can_rebuild` tinyint(4) NOT NULL DEFAULT '1',
  `price_paid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `factories`
--

CREATE TABLE `factories` (
  `factory_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `is_damaged` tinyint(4) NOT NULL DEFAULT '0',
  `being_built` tinyint(4) NOT NULL DEFAULT '0',
  `being_repaired` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` longtext NOT NULL,
  `author_ip` varchar(128) NOT NULL DEFAULT '0.0.0.0',
  `modified_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_sections`
--

CREATE TABLE `forum_sections` (
  `section_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(500) NOT NULL,
  `sub_title` varchar(500) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics`
--

CREATE TABLE `forum_topics` (
  `topic_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(500) NOT NULL,
  `creator_id` int(10) UNSIGNED NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `num_views` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_unread`
--

CREATE TABLE `forum_unread` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gamemsg`
--

CREATE TABLE `gamemsg` (
  `msg_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `message` varchar(200) COLLATE latin1_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `player_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL,
  `title` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `description` varchar(200) COLLATE latin1_general_ci NOT NULL,
  `creator_id` int(11) NOT NULL,
  `password` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `year` int(11) NOT NULL DEFAULT '3025',
  `turn` int(11) NOT NULL DEFAULT '0',
  `player_id_playing` int(11) DEFAULT NULL,
  `phase` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `previous_phase` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT 'Movement',
  `combat_rnd` mediumint(9) NOT NULL DEFAULT '0',
  `built` tinyint(4) NOT NULL DEFAULT '0',
  `hand_size` tinyint(4) NOT NULL DEFAULT '4',
  `orderofbattle` int(11) NOT NULL,
  `build_step` int(11) NOT NULL DEFAULT '0',
  `capitals_to_win` int(11) NOT NULL DEFAULT '4',
  `rebuild_garrison_units` tinyint(4) NOT NULL DEFAULT '0',
  `destroy_jumpships` tinyint(4) NOT NULL DEFAULT '0',
  `auto_factory_dmg_mod` tinyint(4) NOT NULL DEFAULT '0',
  `use_merc_phase` tinyint(4) NOT NULL DEFAULT '0',
  `use_comstar` tinyint(4) NOT NULL DEFAULT '1',
  `use_terra_interdict` tinyint(4) NOT NULL DEFAULT '1',
  `use_terra_loot` tinyint(4) NOT NULL DEFAULT '1',
  `use_extd_jumpships` tinyint(4) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_action` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `alt_victory` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gamestats`
--

CREATE TABLE `gamestats` (
  `stat_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `tech_level` tinyint(4) NOT NULL,
  `military_size` mediumint(9) NOT NULL,
  `military_strength` mediumint(9) NOT NULL,
  `jumpship_size` mediumint(9) NOT NULL,
  `jumpship_capacity` mediumint(9) NOT NULL,
  `num_territories` mediumint(9) NOT NULL,
  `tax_revenue` mediumint(9) NOT NULL,
  `cbills` mediumint(9) NOT NULL,
  `round` int(11) NOT NULL,
  `turn_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game_help`
--

CREATE TABLE `game_help` (
  `help_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `description` longtext NOT NULL,
  `reply` longtext NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `game_owner_votes`
--

CREATE TABLE `game_owner_votes` (
  `vote_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `description` varchar(100) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jumpships`
--

CREATE TABLE `jumpships` (
  `jumpship_id` int(11) NOT NULL,
  `moves_this_turn` int(11) NOT NULL DEFAULT '0',
  `capacity` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `being_built` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `can_undeploy` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `leaders`
--

CREATE TABLE `leaders` (
  `leader_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `combat` tinyint(4) NOT NULL,
  `military` tinyint(4) NOT NULL,
  `admin` tinyint(4) NOT NULL,
  `loyalty` tinyint(4) NOT NULL,
  `combat_used` tinyint(4) NOT NULL DEFAULT '0',
  `military_used` tinyint(4) NOT NULL DEFAULT '0',
  `loaded_in_id` int(11) DEFAULT NULL,
  `was_loaded` int(11) NOT NULL DEFAULT '0',
  `original_house_id` int(11) DEFAULT NULL,
  `controlling_house_id` int(11) DEFAULT NULL,
  `allegiance_to_house_id` int(11) DEFAULT NULL,
  `just_bribed` int(11) NOT NULL DEFAULT '0',
  `associated_units` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `official_leader` tinyint(4) NOT NULL DEFAULT '0',
  `can_undeploy` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `map`
--

CREATE TABLE `map` (
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `default_resource` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `top` float NOT NULL,
  `left` float NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `default_is_regional` tinyint(4) NOT NULL DEFAULT '0',
  `default_is_capital` tinyint(4) NOT NULL DEFAULT '0',
  `world_id` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mercoffers`
--

CREATE TABLE `mercoffers` (
  `offer_id` int(11) NOT NULL,
  `merc_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `offer` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meta`
--

CREATE TABLE `meta` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movement_logs`
--

CREATE TABLE `movement_logs` (
  `log_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_type` enum('combatunit','leader','jumpship') NOT NULL,
  `prev_location_id` int(11) NOT NULL,
  `move_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orderofbattle`
--

CREATE TABLE `orderofbattle` (
  `orderofbattle_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `description` varchar(200) COLLATE latin1_general_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `draft` tinyint(4) NOT NULL DEFAULT '1',
  `use_merc_phase` tinyint(4) NOT NULL DEFAULT '0',
  `auto_factory_dmg_mod` tinyint(4) NOT NULL DEFAULT '0',
  `destroy_jumpships` tinyint(4) NOT NULL DEFAULT '0',
  `year` int(11) NOT NULL DEFAULT '3025',
  `capitals_to_win` int(11) NOT NULL DEFAULT '4',
  `use_comstar` tinyint(4) NOT NULL DEFAULT '1',
  `use_terra_interdict` tinyint(4) NOT NULL DEFAULT '1',
  `use_terra_loot` tinyint(4) NOT NULL DEFAULT '1',
  `world_id` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderofbattledata`
--

CREATE TABLE `orderofbattledata` (
  `data_id` int(11) NOT NULL,
  `oob_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `arg0column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg0data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg1column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg1data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg2column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg2data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg3column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg3data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg4column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg4data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg5column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg5data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg6column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg6data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg7column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg7data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg8column` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `arg8data` varchar(50) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paths`
--

CREATE TABLE `paths` (
  `path_id` int(11) NOT NULL,
  `origin_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `peripherybids`
--

CREATE TABLE `peripherybids` (
  `bid_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `nation_id` int(11) NOT NULL,
  `offer` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `faction` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `turn_order` int(11) NOT NULL DEFAULT '0',
  `setup_order` int(11) NOT NULL,
  `color` varchar(7) COLLATE latin1_general_ci NOT NULL DEFAULT '#FF0000',
  `text_color` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT 'white',
  `money` int(11) NOT NULL DEFAULT '0',
  `tech_level` int(11) NOT NULL DEFAULT '0',
  `combat_done` tinyint(4) NOT NULL DEFAULT '0',
  `house_interdict` tinyint(4) NOT NULL DEFAULT '0',
  `tech_bonus` smallint(6) NOT NULL DEFAULT '0',
  `original_capital` int(11) NOT NULL,
  `official_capital` int(11) NOT NULL,
  `free_bribes` tinyint(4) NOT NULL DEFAULT '0',
  `eliminate` tinyint(4) NOT NULL DEFAULT '0',
  `captured` tinyint(4) NOT NULL DEFAULT '0',
  `may_build_elementals` varchar(25) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_admin_swap`
--

CREATE TABLE `player_admin_swap` (
  `swap_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `public_chat`
--

CREATE TABLE `public_chat` (
  `chat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(300) NOT NULL,
  `color` varchar(10) NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `territories`
--

CREATE TABLE `territories` (
  `territory_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL DEFAULT '1',
  `is_contested` tinyint(4) NOT NULL DEFAULT '0',
  `player_id` int(11) DEFAULT NULL,
  `map_id` int(11) NOT NULL,
  `is_periphery` tinyint(4) NOT NULL DEFAULT '0',
  `garrison_name` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resource` int(11) NOT NULL DEFAULT '1',
  `is_regional` tinyint(4) NOT NULL DEFAULT '0',
  `is_capital` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  `ip_address` char(16) COLLATE latin1_general_ci NOT NULL,
  `username` varchar(25) COLLATE latin1_general_ci NOT NULL,
  `password` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `salt` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `activation_code` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `forgotten_password_code` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `remember_code` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `send_me_email` tinyint(4) NOT NULL DEFAULT '0',
  `email_on_private_message` tinyint(4) NOT NULL DEFAULT '0',
  `auto_kill_all` tinyint(4) NOT NULL DEFAULT '0',
  `auto_kill_order` tinyint(4) NOT NULL DEFAULT '0',
  `forum_posts_per_page` smallint(6) NOT NULL DEFAULT '25',
  `forum_auto_subscribe_created` tinyint(4) NOT NULL DEFAULT '1',
  `forum_auto_subscribe_posted` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `victory_conditions`
--

CREATE TABLE `victory_conditions` (
  `condition_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `type` enum('Regional','Economic','Technology','Industrial','Leader','Military','Capital','Survive','Territory') NOT NULL,
  `duration` int(11) NOT NULL DEFAULT '0',
  `threshold` int(11) NOT NULL DEFAULT '0',
  `current_duration` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `worlds`
--

CREATE TABLE `worlds` (
  `world_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_draft` tinyint(4) NOT NULL DEFAULT '1',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `arrows`
--
ALTER TABLE `arrows`
  ADD UNIQUE KEY `world_id` (`world_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `bugs`
--
ALTER TABLE `bugs`
  ADD PRIMARY KEY (`bug_id`);

--
-- Indexes for table `bug_comments`
--
ALTER TABLE `bug_comments`
  ADD PRIMARY KEY (`bug_comment_id`),
  ADD KEY `bug_id` (`bug_id`,`user_id`);

--
-- Indexes for table `bug_karma`
--
ALTER TABLE `bug_karma`
  ADD PRIMARY KEY (`bug_karma_id`),
  ADD KEY `bug_id` (`bug_id`,`user_id`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`card_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `card_types`
--
ALTER TABLE `card_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `combatbonus`
--
ALTER TABLE `combatbonus`
  ADD PRIMARY KEY (`combatbonus_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `combatlog`
--
ALTER TABLE `combatlog`
  ADD PRIMARY KEY (`combatlog_id`);

--
-- Indexes for table `combatunits`
--
ALTER TABLE `combatunits`
  ADD PRIMARY KEY (`combatunit_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `factories`
--
ALTER TABLE `factories`
  ADD PRIMARY KEY (`factory_id`),
  ADD KEY `location` (`location_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `topic_id` (`topic_id`,`author_id`);

--
-- Indexes for table `forum_sections`
--
ALTER TABLE `forum_sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `section_id` (`section_id`,`creator_id`);

--
-- Indexes for table `forum_unread`
--
ALTER TABLE `forum_unread`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gamemsg`
--
ALTER TABLE `gamemsg`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`);

--
-- Indexes for table `gamestats`
--
ALTER TABLE `gamestats`
  ADD PRIMARY KEY (`stat_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `game_help`
--
ALTER TABLE `game_help`
  ADD PRIMARY KEY (`help_id`);

--
-- Indexes for table `game_owner_votes`
--
ALTER TABLE `game_owner_votes`
  ADD PRIMARY KEY (`vote_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jumpships`
--
ALTER TABLE `jumpships`
  ADD PRIMARY KEY (`jumpship_id`),
  ADD KEY `location` (`location_id`);

--
-- Indexes for table `leaders`
--
ALTER TABLE `leaders`
  ADD PRIMARY KEY (`leader_id`),
  ADD KEY `game_id` (`game_id`,`location_id`),
  ADD KEY `location` (`location_id`);

--
-- Indexes for table `map`
--
ALTER TABLE `map`
  ADD PRIMARY KEY (`map_id`);

--
-- Indexes for table `mercoffers`
--
ALTER TABLE `mercoffers`
  ADD PRIMARY KEY (`offer_id`);

--
-- Indexes for table `meta`
--
ALTER TABLE `meta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movement_logs`
--
ALTER TABLE `movement_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `orderofbattle`
--
ALTER TABLE `orderofbattle`
  ADD PRIMARY KEY (`orderofbattle_id`);

--
-- Indexes for table `orderofbattledata`
--
ALTER TABLE `orderofbattledata`
  ADD PRIMARY KEY (`data_id`);

--
-- Indexes for table `paths`
--
ALTER TABLE `paths`
  ADD PRIMARY KEY (`path_id`);

--
-- Indexes for table `peripherybids`
--
ALTER TABLE `peripherybids`
  ADD PRIMARY KEY (`bid_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`);

--
-- Indexes for table `player_admin_swap`
--
ALTER TABLE `player_admin_swap`
  ADD PRIMARY KEY (`swap_id`),
  ADD UNIQUE KEY `swap_id` (`swap_id`);

--
-- Indexes for table `public_chat`
--
ALTER TABLE `public_chat`
  ADD PRIMARY KEY (`chat_id`);

--
-- Indexes for table `territories`
--
ALTER TABLE `territories`
  ADD PRIMARY KEY (`territory_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `victory_conditions`
--
ALTER TABLE `victory_conditions`
  ADD PRIMARY KEY (`condition_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `worlds`
--
ALTER TABLE `worlds`
  ADD PRIMARY KEY (`world_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bugs`
--
ALTER TABLE `bugs`
  MODIFY `bug_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bug_comments`
--
ALTER TABLE `bug_comments`
  MODIFY `bug_comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bug_karma`
--
ALTER TABLE `bug_karma`
  MODIFY `bug_karma_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `card_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `card_types`
--
ALTER TABLE `card_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `combatbonus`
--
ALTER TABLE `combatbonus`
  MODIFY `combatbonus_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `combatlog`
--
ALTER TABLE `combatlog`
  MODIFY `combatlog_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `combatunits`
--
ALTER TABLE `combatunits`
  MODIFY `combatunit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `factories`
--
ALTER TABLE `factories`
  MODIFY `factory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_sections`
--
ALTER TABLE `forum_sections`
  MODIFY `section_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `topic_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_unread`
--
ALTER TABLE `forum_unread`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gamemsg`
--
ALTER TABLE `gamemsg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gamestats`
--
ALTER TABLE `gamestats`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_help`
--
ALTER TABLE `game_help`
  MODIFY `help_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_owner_votes`
--
ALTER TABLE `game_owner_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jumpships`
--
ALTER TABLE `jumpships`
  MODIFY `jumpship_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaders`
--
ALTER TABLE `leaders`
  MODIFY `leader_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `map`
--
ALTER TABLE `map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mercoffers`
--
ALTER TABLE `mercoffers`
  MODIFY `offer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meta`
--
ALTER TABLE `meta`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movement_logs`
--
ALTER TABLE `movement_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderofbattle`
--
ALTER TABLE `orderofbattle`
  MODIFY `orderofbattle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderofbattledata`
--
ALTER TABLE `orderofbattledata`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paths`
--
ALTER TABLE `paths`
  MODIFY `path_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peripherybids`
--
ALTER TABLE `peripherybids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_admin_swap`
--
ALTER TABLE `player_admin_swap`
  MODIFY `swap_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `public_chat`
--
ALTER TABLE `public_chat`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `territories`
--
ALTER TABLE `territories`
  MODIFY `territory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `victory_conditions`
--
ALTER TABLE `victory_conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `worlds`
--
ALTER TABLE `worlds`
  MODIFY `world_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`topic_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `forum_sections` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
