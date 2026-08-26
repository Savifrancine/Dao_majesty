-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 02 juil. 2026 à 18:52
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dao`
--

-- --------------------------------------------------------

--
-- Structure de la table `autorites_contractantes`
--

CREATE TABLE `autorites_contractantes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bordereaux`
--

CREATE TABLE `bordereaux` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_document_id` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `column_defs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Définitions dynamiques des colonnes' CHECK (json_valid(`column_defs`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `bordereaux`
--

INSERT INTO `bordereaux` (`id`, `dossier_document_id`, `titre`, `column_defs`, `created_at`, `updated_at`) VALUES
(1, 29, 'Bordereau prix unitaire', NULL, '2026-04-13 12:38:57', '2026-04-13 12:38:57'),
(5, 37, 'Bordereau prix unitaire', NULL, '2026-05-03 17:26:41', '2026-05-03 17:26:41'),
(6, 38, 'Programme d\'activités', NULL, '2026-05-03 17:27:10', '2026-05-03 17:27:10'),
(8, 33, 'Fourniture', NULL, '2026-05-05 14:30:34', '2026-05-05 14:30:34'),
(9, 40, 'Calendrier d\'exécution', NULL, '2026-06-02 07:34:08', '2026-06-02 07:34:08'),
(16, 30, 'Les boissons', NULL, '2026-06-03 09:16:05', '2026-06-03 09:16:05'),
(17, 43, 'Les boissons', NULL, '2026-06-03 09:23:04', '2026-06-03 09:23:04'),
(18, 42, 'Les boissons', NULL, '2026-06-03 09:51:17', '2026-06-03 09:51:17'),
(20, 45, 'Les boissons', NULL, '2026-06-03 11:02:58', '2026-06-03 11:02:58'),
(21, 46, 'Les boissons', NULL, '2026-06-03 11:38:57', '2026-06-03 11:38:57'),
(23, 54, 'Bordereau des prix pour les fournitures à importer', NULL, '2026-06-23 15:09:57', '2026-06-23 15:09:57'),
(27, 57, 'INSTALLATION ET MAINTENANCE', NULL, '2026-06-23 17:22:57', '2026-06-23 17:22:57'),
(28, 57, 'FORMATION DES UTILISATEURS', NULL, '2026-06-23 17:22:57', '2026-06-23 17:22:57'),
(29, 58, 'INSTALLATION ET MAINTENANCE', NULL, '2026-06-23 20:37:13', '2026-06-23 20:37:13'),
(30, 58, 'FORMATION DES UTILISATEURS', NULL, '2026-06-23 20:37:13', '2026-06-23 20:37:13'),
(31, 59, 'Listes des Fournitures et Calendrier de livraison', NULL, '2026-06-23 21:16:48', '2026-06-23 21:16:48'),
(33, 60, 'Cadres de sous détails des prix unitaire', NULL, '2026-06-24 12:18:23', '2026-06-24 12:18:23');

-- --------------------------------------------------------

--
-- Structure de la table `bordereau_lignes`
--

CREATE TABLE `bordereau_lignes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bordereau_id` bigint(20) UNSIGNED NOT NULL,
  `designation` varchar(255) NOT NULL,
  `unite_physique` varchar(255) DEFAULT NULL,
  `total_materiel` varchar(255) DEFAULT NULL,
  `location_amort` varchar(255) DEFAULT NULL,
  `matiere_frais` varchar(255) DEFAULT NULL,
  `main_oeuvre` varchar(255) DEFAULT NULL,
  `deborse_sec` varchar(255) DEFAULT NULL,
  `coef_c1` varchar(255) DEFAULT NULL,
  `coef_k` varchar(255) DEFAULT NULL,
  `prix_vente_htva` varchar(255) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(12,2) NOT NULL,
  `montant` decimal(12,2) NOT NULL,
  `cout_benin` decimal(18,2) DEFAULT NULL COMMENT 'Coût main-d''œuvre locale, matière premières et composants du Bénin/UEMOA',
  `site` varchar(255) DEFAULT NULL,
  `date_prestation` varchar(255) DEFAULT NULL,
  `frequence` varchar(255) DEFAULT NULL COMMENT 'Fréquence de réalisation du service',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `specifications_techniques` longtext DEFAULT NULL,
  `specifications_obligatoires` longtext DEFAULT NULL,
  `specifications_proposees` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `bordereau_lignes`
--

INSERT INTO `bordereau_lignes` (`id`, `bordereau_id`, `designation`, `unite_physique`, `total_materiel`, `location_amort`, `matiere_frais`, `main_oeuvre`, `deborse_sec`, `coef_c1`, `coef_k`, `prix_vente_htva`, `quantite`, `prix_unitaire`, `montant`, `cout_benin`, `site`, `date_prestation`, `frequence`, `created_at`, `updated_at`, `specifications_techniques`, `specifications_obligatoires`, `specifications_proposees`) VALUES
(1, 1, 'Habit', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 20000.00, 20000.00, NULL, NULL, NULL, NULL, '2026-04-13 12:38:57', '2026-04-13 12:38:57', NULL, NULL, NULL),
(2, 1, 'Chaussure', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 25000.00, 25000.00, NULL, NULL, NULL, NULL, '2026-04-13 12:38:57', '2026-04-13 12:38:57', NULL, NULL, NULL),
(3, 1, 'Accessoire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5000.00, 5000.00, NULL, NULL, NULL, NULL, '2026-04-13 12:38:57', '2026-04-13 12:38:57', NULL, NULL, NULL),
(6, 5, 'JHKHL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 20000.00, 20000.00, NULL, NULL, NULL, NULL, '2026-05-03 17:26:41', '2026-05-03 17:26:41', NULL, NULL, NULL),
(7, 6, 'GHK?VKH', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 20000.00, 20000.00, NULL, '', '', NULL, '2026-05-03 17:27:10', '2026-05-03 17:27:10', NULL, NULL, NULL),
(9, 8, 'Habit', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 20000.00, 20000.00, NULL, NULL, NULL, NULL, '2026-05-05 14:30:34', '2026-05-05 14:30:34', NULL, NULL, NULL),
(10, 8, 'Sac', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 24000.00, 24000.00, NULL, NULL, NULL, NULL, '2026-05-05 14:30:35', '2026-05-05 14:30:35', NULL, NULL, NULL),
(11, 9, 'oui', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 20000.00, 20000.00, NULL, NULL, NULL, NULL, '2026-06-02 07:34:08', '2026-06-02 07:34:08', NULL, NULL, NULL),
(13, 16, 'Fanta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 600.00, 600.00, NULL, NULL, NULL, NULL, '2026-06-03 09:16:05', '2026-06-03 09:16:05', NULL, NULL, NULL),
(14, 16, 'coca', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 500.00, 500.00, NULL, NULL, NULL, NULL, '2026-06-03 09:16:05', '2026-06-03 09:16:05', NULL, NULL, NULL),
(15, 16, 'Savana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2000.00, 2000.00, NULL, NULL, NULL, NULL, '2026-06-03 09:16:05', '2026-06-03 09:16:05', NULL, NULL, NULL),
(16, 17, 'Fanta', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 30, 600.00, 18000.00, NULL, 'Majesty', '2 M', NULL, '2026-06-03 09:23:04', '2026-06-03 09:23:04', NULL, NULL, NULL),
(17, 17, 'coca', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 500.00, 25000.00, NULL, 'Majesty', '2 M', NULL, '2026-06-03 09:23:04', '2026-06-03 09:23:04', NULL, NULL, NULL),
(18, 17, 'Savana', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 80, 2000.00, 160000.00, NULL, 'Majesty', '2 M', NULL, '2026-06-03 09:23:05', '2026-06-03 09:23:05', NULL, NULL, NULL),
(19, 18, 'Fanta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 30, 600.00, 18000.00, NULL, NULL, '12 mois', NULL, '2026-06-03 09:51:17', '2026-06-03 09:51:17', NULL, NULL, NULL),
(20, 18, 'coca', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 500.00, 25000.00, NULL, NULL, '12 mois', NULL, '2026-06-03 09:51:17', '2026-06-03 09:51:17', NULL, NULL, NULL),
(21, 18, 'Savana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 80, 2000.00, 160000.00, NULL, NULL, '12 mois', NULL, '2026-06-03 09:51:17', '2026-06-03 09:51:17', NULL, NULL, NULL),
(28, 21, 'Fanta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-06-03 11:38:57', '2026-06-03 11:38:57', NULL, NULL, NULL),
(29, 21, 'coca', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-06-03 11:38:57', '2026-06-03 11:38:57', NULL, NULL, NULL),
(30, 21, 'Savana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-06-03 11:38:57', '2026-06-03 11:38:57', NULL, NULL, NULL),
(33, 23, 'Automate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 35424000.00, 106272000.00, 0.00, NULL, 'DEUX MOIS', NULL, '2026-06-23 15:09:57', '2026-06-23 15:09:57', NULL, NULL, NULL),
(34, 23, 'Bain-Marie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 239701.00, 239701.00, 0.00, NULL, 'DEUX MOIS', NULL, '2026-06-23 15:09:57', '2026-06-23 15:09:57', NULL, NULL, NULL),
(38, 27, 'installation d\'automate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 700000.00, 2100000.00, NULL, NULL, '2 mois', 'Une seule fois', '2026-06-23 17:22:57', '2026-06-23 17:22:57', NULL, NULL, NULL),
(39, 27, 'installation de Bain-Marie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 100000.00, 100000.00, NULL, NULL, '2 mois', 'Une seule fois', '2026-06-23 17:22:57', '2026-06-23 17:22:57', NULL, NULL, NULL),
(40, 28, 'Formation des utilisateurs au STS Zou', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, 800000.00, 6400000.00, NULL, NULL, '5 jours', 'Une seule fois', '2026-06-23 17:22:57', '2026-06-23 17:22:57', NULL, NULL, NULL),
(41, 29, 'Installation d\'automate', 'U', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 0.00, 0.00, NULL, 'Services de trannsfusion sannguine ouémé', 'Deux mois / Trois mois', NULL, '2026-06-23 20:37:13', '2026-06-23 20:37:13', NULL, NULL, NULL),
(42, 29, 'Installation de Bain-Marie', 'U', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 0.00, NULL, 'Services de transfusion sanguine: Borgou(Parakou)', 'Deux mois / Trois mois', NULL, '2026-06-23 20:37:13', '2026-06-23 20:37:13', NULL, NULL, NULL),
(43, 30, 'Formationns des utilisateurs STS zou', 'U', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 0.00, 0.00, NULL, 'Services de trannsfusion sannguine Zou', '3 jours / 5 jours', NULL, '2026-06-23 20:37:13', '2026-06-23 20:37:13', NULL, NULL, NULL),
(44, 31, 'Automate', 'U', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 0.00, 0.00, NULL, 'Services de transfusion sanguine ouémé', 'Deux mois / Trois mois / Deux mois', NULL, '2026-06-23 21:16:48', '2026-06-23 21:16:48', NULL, NULL, NULL),
(45, 31, 'Bain-Marie', 'U', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 0.00, NULL, 'Services de transfusion sanguine: Borgou(Parakou)', 'Deux mois / Trois mois / Deux mois', NULL, '2026-06-23 21:16:48', '2026-06-23 21:16:48', NULL, NULL, NULL),
(46, 33, 'Automate', 'Entière', '17584.04', '3567885', '0.01', '7135770', '10721239.05', '609.7142', '', '335445550.01', 1, 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-06-24 12:18:23', '2026-06-24 12:18:23', NULL, NULL, NULL),
(47, 20, 'Fanta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 30, 600.00, 18000.00, NULL, NULL, '12 mois', NULL, '2026-06-30 12:39:05', '2026-06-30 12:39:05', NULL, NULL, NULL),
(48, 20, 'coca', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 500.00, 25000.00, NULL, NULL, '12 mois', NULL, '2026-06-30 12:39:05', '2026-06-30 12:39:05', NULL, NULL, NULL),
(49, 20, 'Savana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 80, 2000.00, 160000.00, NULL, NULL, '12 mois', NULL, '2026-06-30 12:39:05', '2026-06-30 12:39:05', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `champs_documents`
--

CREATE TABLE `champs_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type_document_id` bigint(20) UNSIGNED NOT NULL,
  `nom_champ` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `type` enum('text','number','date') NOT NULL DEFAULT 'text',
  `ordre` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `champs_documents`
--

INSERT INTO `champs_documents` (`id`, `type_document_id`, `nom_champ`, `label`, `type`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 'date', 'Date', 'date', 1, NULL, NULL),
(2, 1, 'numero_drf', 'Numéro DRF', 'text', 2, NULL, NULL),
(3, 1, 'agence', 'Agence/Destinataire', 'text', 3, NULL, NULL),
(4, 1, 'contenu', 'Contenu de la lettre', 'text', 4, NULL, NULL),
(5, 1, 'nom_signataire', 'Nom du Signataire', 'text', 5, NULL, NULL),
(6, 1, 'titre_signataire', 'Titre/Fonction', 'text', 6, NULL, NULL),
(7, 1, 'entite', 'Entité', 'text', 7, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `chiffres_affaires`
--

CREATE TABLE `chiffres_affaires` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `annee` int(11) NOT NULL,
  `montant` decimal(20,2) NOT NULL DEFAULT 0.00,
  `monnaie` varchar(255) NOT NULL DEFAULT 'F CFA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chiffres_affaires`
--

INSERT INTO `chiffres_affaires` (`id`, `dossier_id`, `annee`, `montant`, `monnaie`, `created_at`, `updated_at`) VALUES
(1, NULL, 2022, 70166216.00, 'F CFA', '2026-06-10 20:21:34', '2026-06-10 20:21:34'),
(2, NULL, 2023, 140027253.00, 'F CFA', '2026-06-11 09:35:58', '2026-06-11 09:35:58'),
(3, NULL, 2024, 124338037.00, 'F CFA', '2026-06-11 09:37:07', '2026-06-11 09:37:07');

-- --------------------------------------------------------

--
-- Structure de la table `daos`
--

CREATE TABLE `daos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `code_postal` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `daos`
--

INSERT INTO `daos` (`id`, `nom`, `description`, `email`, `telephone`, `adresse`, `ville`, `code_postal`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Achat', 'ventes de produit', 'achat@gmail.com', '97202020', 'calavi', 'calavi', NULL, 1, '2026-02-04 17:17:03', '2026-02-04 17:17:03');

-- --------------------------------------------------------

--
-- Structure de la table `documents_fichiers`
--

CREATE TABLE `documents_fichiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_document_id` bigint(20) UNSIGNED NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `documents_fichiers`
--

INSERT INTO `documents_fichiers` (`id`, `dossier_document_id`, `chemin_fichier`, `utilisateur_id`, `created_at`, `updated_at`) VALUES
(1, 12, 'dossiers/documents/ouhv30TYdiUll0qnA2CJsyxxHybrfssBqVjfMabW.pdf', 1, '2026-02-20 12:35:27', '2026-02-20 12:35:27'),
(2, 13, 'dossiers/documents/dossier_29_declaration_1771940719.pdf', 1, '2026-02-24 12:45:22', '2026-02-24 12:45:22'),
(3, 13, 'dossiers/documents/dossier_29_declaration_1771940726.pdf', 1, '2026-02-24 12:45:26', '2026-02-24 12:45:26'),
(4, 13, 'dossiers/documents/dossier_29_declaration_1771944501.pdf', 1, '2026-02-24 13:48:24', '2026-02-24 13:48:24'),
(5, 13, 'dossiers/documents/dossier_29_declaration_1772105915.pdf', 1, '2026-02-26 10:38:42', '2026-02-26 10:38:42'),
(6, 19, 'dossiers/documents/dossier_10_declaration_1772536200.pdf', 1, '2026-03-03 10:10:07', '2026-03-03 10:10:07'),
(7, 20, 'dossiers/documents/CsJZ4VzBDB7i1yckXxT30zg93o0oscVRRvLOdYwc.pdf', 1, '2026-03-03 10:14:35', '2026-03-03 10:14:35'),
(8, 14, 'dossiers/documents/uDwDc1Y98bYRbOY1UWETtj90CLDSVYChatdKjacC.pdf', 1, '2026-03-04 14:02:10', '2026-03-04 14:02:10'),
(9, 15, 'dossiers/documents/oawK12g16iVaKjsNxgvCVTe0DrO1Jo1zQcmoaxVW.avif', 1, '2026-03-04 14:02:44', '2026-03-04 14:02:44'),
(10, 15, 'dossiers/documents/H9xmyrSam7wyEARoFucs40EVY01ZpO4Llj0i6yfa.jpg', 1, '2026-03-04 14:04:08', '2026-03-04 14:04:08'),
(11, 21, 'dossiers/documents/dossier_35_declaration_1773756965.pdf', 1, '2026-03-17 13:16:11', '2026-03-17 13:16:11'),
(13, 22, 'dossiers/documents/bePI0o0bRhYV708pyan0NR8aBWuBaTi02D0UE0GH.pdf', 1, '2026-03-17 13:18:56', '2026-03-17 13:18:56'),
(14, 21, 'dossiers/documents/dossier_35_declaration_1773757246.pdf', 1, '2026-03-17 13:20:46', '2026-03-17 13:20:46'),
(15, 23, 'dossiers/documents/Sw8rDxpWA3Q0SWJBFx53nOmdP7sMAFyw4p3PVWsr.pdf', 1, '2026-03-17 13:21:09', '2026-03-17 13:21:09'),
(17, 21, 'dossiers/documents/dossier_35_declaration_1773757489.pdf', 1, '2026-03-17 13:24:49', '2026-03-17 13:24:49'),
(18, 24, 'dossiers/documents/tEdYmnilRcTjDhI6M98TMJCZqSYbtYMkuq8QLTBS.jpg', 1, '2026-03-17 16:35:18', '2026-03-17 16:35:18'),
(19, 25, 'dossiers/documents/dossier_39_declaration_1773837842.pdf', 1, '2026-03-18 11:44:02', '2026-03-18 11:44:02'),
(20, 26, 'dossiers/documents/0SZVu2ZA3EYxO6KUcJ1TH9JVvrOTj3kPykI5Botd.pdf', 1, '2026-03-18 11:45:20', '2026-03-18 11:45:20'),
(21, 27, 'dossiers/documents/dossier_41_declaration_1774898213.pdf', 1, '2026-03-30 18:17:00', '2026-03-30 18:17:00'),
(22, 28, 'dossiers/documents/WeT7b2RBACC10DzNVOi1ydgVTvcEuH04j845UME6.pdf', 1, '2026-03-30 18:17:58', '2026-03-30 18:17:58'),
(24, 36, 'dossiers/documents/8AYeeQk40oOpoue6oKqpvuFljDeE9MhdJ1z8Fyvy.png', 1, '2026-05-03 17:26:19', '2026-05-03 17:26:19'),
(25, 47, 'dossiers/documents/dossier_40_formulaire_candidat_1780626031.pdf', 1, '2026-06-05 01:20:31', '2026-06-05 01:20:31'),
(26, 48, 'dossiers/documents/dossier_40_declaration_1781104898.pdf', 1, '2026-06-10 14:21:38', '2026-06-10 14:21:38'),
(27, 49, 'dossiers/documents/dossier_40_chiffres_affaires_1781309923.pdf', 1, '2026-06-12 23:18:47', '2026-06-12 23:18:47'),
(29, 55, 'dossiers/documents/MWkifQmuucRTzNetow4HqddUx3n1JlmvfczpwKoi.pdf', 1, '2026-06-23 16:21:26', '2026-06-23 16:21:26'),
(30, 61, 'dossiers/documents/dossier_40_tableau_resume_1782321716.pdf', 1, '2026-06-24 16:21:59', '2026-06-24 16:21:59'),
(31, 63, 'dossiers/documents/3c559TPB72i80uye5OFo17CH7tm3MrxyxlRsMC3N.pdf', 1, '2026-06-29 18:39:46', '2026-06-29 18:39:46'),
(32, 64, 'dossiers/documents/ABIUh2jj1GaZrO5TrCC0iIgp8vmOqcGuPQNXT5sE.pdf', 1, '2026-06-29 18:40:10', '2026-06-29 18:40:10');

-- --------------------------------------------------------

--
-- Structure de la table `documents_textes`
--

CREATE TABLE `documents_textes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_document_id` bigint(20) UNSIGNED NOT NULL,
  `contenu` longtext DEFAULT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type_dossier_id` bigint(20) UNSIGNED NOT NULL,
  `entreprise_id` bigint(20) UNSIGNED NOT NULL,
  `nom_dossier` varchar(255) NOT NULL,
  `titre_dossier` varchar(255) DEFAULT NULL,
  `type_offre` varchar(255) DEFAULT NULL,
  `objectif` text DEFAULT NULL,
  `lot` varchar(255) DEFAULT NULL,
  `public_prive` enum('public','prive') NOT NULL DEFAULT 'public',
  `statut` enum('en_cours','termine','genere') NOT NULL DEFAULT 'en_cours',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type_marche_id` bigint(20) UNSIGNED DEFAULT NULL,
  `procedure_id` bigint(20) UNSIGNED DEFAULT NULL,
  `numero_ao` varchar(255) DEFAULT NULL,
  `date_ao` date DEFAULT NULL,
  `objet_marche` text DEFAULT NULL,
  `lots` varchar(255) DEFAULT NULL,
  `titre_lot` varchar(255) DEFAULT NULL,
  `autres_details` text DEFAULT NULL,
  `mois_depot` varchar(255) DEFAULT NULL,
  `annee_depot` varchar(255) DEFAULT NULL,
  `autorite_contractante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_financement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_step` varchar(255) DEFAULT NULL,
  `annee_gestion` year(4) DEFAULT NULL,
  `ville_signature` varchar(255) DEFAULT NULL,
  `date_signature` date DEFAULT NULL,
  `mois_edition` varchar(255) DEFAULT NULL,
  `page_garde_path` varchar(255) DEFAULT NULL,
  `republique` varchar(255) DEFAULT NULL,
  `ministere` varchar(255) DEFAULT NULL,
  `direction` varchar(255) DEFAULT NULL,
  `services_projet` varchar(255) DEFAULT NULL,
  `destinataires` text DEFAULT NULL,
  `reference_dossier` varchar(255) DEFAULT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `date_lancement` date DEFAULT NULL,
  `date_soumission` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dossiers`
--

INSERT INTO `dossiers` (`id`, `type_dossier_id`, `entreprise_id`, `nom_dossier`, `titre_dossier`, `type_offre`, `objectif`, `lot`, `public_prive`, `statut`, `created_at`, `updated_at`, `type_marche_id`, `procedure_id`, `numero_ao`, `date_ao`, `objet_marche`, `lots`, `titre_lot`, `autres_details`, `mois_depot`, `annee_depot`, `autorite_contractante_id`, `source_financement_id`, `reference_step`, `annee_gestion`, `ville_signature`, `date_signature`, `mois_edition`, `page_garde_path`, `republique`, `ministere`, `direction`, `services_projet`, `destinataires`, `reference_dossier`, `ref`, `date_lancement`, `date_soumission`) VALUES
(1, 1, 1, 'Fourniture 2026', NULL, NULL, 'OUI OUI', 'LOT 1', 'public', 'en_cours', '2026-02-05 12:04:10', '2026-02-05 12:04:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 1, 2, 'Daoo 2', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-09 19:35:52', '2026-02-09 19:35:52', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', NULL, 'Février', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', NULL, 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'PERSONNE RESPONSABLE DES FINANCES', 'AAO N°2232U3890055', NULL, '2026-02-01', NULL),
(6, 1, 2, 'DAO Art et Cinéma', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-11 15:29:53', '2026-02-11 15:29:53', NULL, NULL, NULL, NULL, NULL, 'lot1', 'acquisiton', NULL, 'Mai', '2027', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'Des arts et de la culture', 'adac', 'PROJET D\'APPUI AUX MAISONS DE PRODUCTION', 'Tout acteurs culturels', 'AAO N°2232U3890055', NULL, '2026-02-20', NULL),
(7, 1, 2, 'DAO Art et Cinéma', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-11 15:29:57', '2026-02-11 15:29:57', NULL, NULL, NULL, NULL, NULL, 'lot1', 'acquisiton', NULL, 'Mai', '2027', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'Des arts et de la culture', 'adac', 'PROJET D\'APPUI AUX MAISONS DE PRODUCTION', 'Tout acteurs culturels', 'AAO N°2232U3890055', NULL, '2026-02-20', NULL),
(8, 1, 2, 'DAO 4', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-12 13:46:34', '2026-02-12 13:46:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 1, 2, 'DAO 2', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-20 10:28:52', '2026-02-20 10:28:52', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne   Digne', 'AAO N°2232U3890055', NULL, '2026-02-16', NULL),
(10, 1, 2, 'DAO 4', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-20 11:35:39', '2026-02-20 11:35:39', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne digne', 'AAO N°2232U3890055', NULL, '2026-02-16', NULL),
(11, 1, 2, 'DAO 4', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-20 12:26:55', '2026-02-20 12:26:55', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne digne', 'AAO N°2232U3890055', NULL, '2026-02-16', NULL),
(12, 1, 2, 'DAO 5', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-21 08:24:17', '2026-02-21 08:24:17', NULL, NULL, NULL, NULL, NULL, 'Lot 1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne digne', 'AAO N°2232U3890055', NULL, '2026-02-16', NULL),
(13, 1, 2, 'DAO 5', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-02-21 08:26:40', '2026-02-21 08:26:40', NULL, NULL, NULL, NULL, NULL, 'Lot 1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne digne', 'AAO N°2232U3890055', NULL, '2026-02-16', NULL),
(14, 1, 2, 'DAO 2', 'ACQUISITION DE MATERIELS', NULL, NULL, NULL, 'public', 'en_cours', '2026-02-21 09:23:01', '2026-02-21 09:23:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 1, 2, 'DAO 2', 'ACQUISITION DE MATERIELS', NULL, NULL, NULL, 'public', 'en_cours', '2026-02-21 09:23:07', '2026-02-21 09:23:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 1, 2, 'DAO 2', 'ACQUISITION DE MATERIELS', NULL, NULL, NULL, 'public', 'en_cours', '2026-02-21 09:23:12', '2026-02-21 09:23:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 10:22:58', '2026-02-24 10:22:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(18, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:23:05', '2026-02-24 11:23:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(19, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:28:58', '2026-02-24 11:28:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(20, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:30:59', '2026-02-24 11:30:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(21, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:40:03', '2026-02-24 11:40:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(22, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:40:27', '2026-02-24 11:40:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(23, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:42:26', '2026-02-24 11:42:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(24, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 11:55:24', '2026-02-24 11:55:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(25, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 12:12:48', '2026-02-24 12:12:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(28, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 12:29:35', '2026-02-24 12:29:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(29, 1, 2, 'Daoo 1', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 12:43:00', '2026-02-24 12:43:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ghhf-22345', NULL, NULL),
(30, 1, 2, 'OUI', NULL, NULL, NULL, NULL, 'public', 'en_cours', '2026-02-24 14:12:26', '2026-02-24 14:12:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GIhf-2569', NULL, NULL),
(31, 1, 2, 'DAO 2', 'Fourtinures', NULL, NULL, NULL, 'public', 'en_cours', '2026-02-25 12:57:36', '2026-02-25 12:57:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 1, 2, 'DAO 2', 'Fourtinures', NULL, NULL, NULL, 'public', 'en_cours', '2026-02-25 13:06:36', '2026-02-25 13:06:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 1, 2, 'YES', 'ACQUISITION DE MATERIELS', NULL, NULL, NULL, 'public', 'en_cours', '2026-02-25 16:55:43', '2026-02-25 16:55:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 1, 2, 'YES', 'ACQUISITION DE MATERIELS', NULL, NULL, NULL, 'public', 'genere', '2026-02-25 16:55:49', '2026-03-04 15:36:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 1, 2, 'Test', 'ACQUISITION DE MATERIELS', NULL, NULL, NULL, 'public', 'genere', '2026-03-17 13:14:18', '2026-03-17 16:36:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 1, 2, 'Test 2', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-03-18 10:18:05', '2026-03-18 10:18:05', NULL, NULL, NULL, NULL, NULL, 'Lot 1', 'MATERIELS DE CONTROLES', NULL, 'Mars', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne ressources', 'AAO N°2232U3890055', 'S DLCSSA_109561', '2026-03-01', '2026-03-18'),
(37, 1, 2, 'Test 2', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-03-18 10:56:03', '2026-03-18 10:56:03', NULL, NULL, NULL, NULL, NULL, 'Lot 1', 'MATERIELS DE CONTROLES', NULL, 'Mars', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne ressources', 'AAO N°2232U3890055', 'S DLCSSA_109561', '2026-03-01', '2026-03-18'),
(38, 1, 2, 'Test 2', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'en_cours', '2026-03-18 11:35:46', '2026-03-18 11:35:46', NULL, NULL, NULL, NULL, NULL, 'Lot 1', 'MATERIELS DE CONTROLES', NULL, 'Mars', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne ressources', 'AAO N°2232U3890055', 'S DLCSSA_109561', '2026-03-01', '2026-03-18'),
(39, 1, 2, 'Test 2', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'genere', '2026-03-18 11:39:50', '2026-03-30 15:45:07', NULL, NULL, NULL, NULL, NULL, 'Lot 1', 'MATERIELS DE CONTROLES', NULL, 'Mars', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Personne ressources', 'AAO N°2232U3890055', 'S DLCSSA_109561', '2026-03-01', '2026-03-18'),
(40, 1, 2, 'Test 3', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'genere', '2026-03-30 17:36:12', '2026-06-30 21:01:40', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Pesonnes digne', 'AAO N°2232U3890055', 'S DLCSSA_109563', '2026-03-02', '2026-03-30'),
(41, 1, 2, 'Test 3', 'ACQUISITION DE MATERIELS', 'offre technique', NULL, NULL, 'public', 'termine', '2026-03-30 18:15:17', '2026-05-08 07:10:23', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', NULL, 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'Pesonnes digne', 'AAO N°2232U3890055', 'S DLCSSA_109563', '2026-03-02', '2026-03-30'),
(42, 1, 1, 'test', 'ttttt', 'offre technique', NULL, NULL, 'public', 'termine', '2026-05-03 17:24:41', '2026-06-02 07:01:24', NULL, NULL, NULL, NULL, NULL, 'lot1', 'MATERIELS DE CONTROLES', 'gytywdggj', 'Mai', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'republique du benin', 'MINISTERE DES FINANCES', 'fianances', 'PROJET D\'APPUI DE LA COMPETITIVITE DES FILIERES', 'eyruit', 'AAO N°2232U3890055', 'gfhhjkt', '2026-05-04', '2026-05-10'),
(43, 1, 1, 'POUELLE CALAVI', 'DEMANDE DE RESEIGNNEMENTS ET DE PRIX (DRP)', 'OFFRE TECHIQUE', NULL, NULL, 'public', 'en_cours', '2026-07-01 12:35:36', '2026-07-01 12:35:36', NULL, NULL, NULL, NULL, NULL, 'LOT3', 'ACQUISITIO ET ISTALLATIO DE VINGT (20) ECHOGRAPHES AU PROFIT DE LHOPITAL DE ZONE DE CALAVI', 'Origine des Fonds: Fonds propres\r\nFadec', 'JUILLET', '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REPUBLIQUE DU BENI', 'MINNISTERE DE LA SATE', 'AGECE NATIONALE DE TRASFUSION SAGUINE', NULL, 'LA PERSOE RESPONSABLE DES MARCHES PUBLICS', 'ADRP NUMERO G-23456', NULL, '2026-03-02', '2026-07-23');

-- --------------------------------------------------------

--
-- Structure de la table `dossier_documents`
--

CREATE TABLE `dossier_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `type_document_id` bigint(20) UNSIGNED NOT NULL,
  `ordre` int(11) DEFAULT NULL,
  `statut` enum('vide','en_cours','complete') NOT NULL DEFAULT 'vide',
  `content` text DEFAULT NULL,
  `declaration_content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dossier_documents`
--

INSERT INTO `dossier_documents` (`id`, `dossier_id`, `type_document_id`, `ordre`, `statut`, `content`, `declaration_content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'complete', '[{\"poste\":\"Responsable\",\"nom\":\"Test Pers\"},{\"poste\":\"Assistant\",\"nom\":\"Pers 2\"}]', NULL, '2026-02-05 12:04:28', '2026-06-17 10:43:11'),
(2, 1, 2, 2, 'vide', NULL, NULL, '2026-02-05 12:04:28', '2026-02-05 12:04:28'),
(3, 1, 3, 3, 'vide', NULL, NULL, '2026-02-05 12:04:28', '2026-02-05 12:04:28'),
(9, 5, 1, 1, 'vide', NULL, NULL, '2026-02-09 19:45:32', '2026-02-09 19:45:32'),
(10, 5, 2, 2, 'vide', NULL, NULL, '2026-02-09 19:45:32', '2026-02-09 19:45:32'),
(11, 5, 3, 3, 'vide', NULL, NULL, '2026-02-09 19:45:32', '2026-02-09 19:45:32'),
(12, 11, 4, 1, 'vide', NULL, NULL, '2026-02-20 12:35:17', '2026-02-20 12:35:17'),
(13, 29, 16, 1, 'complete', NULL, NULL, '2026-02-24 12:44:51', '2026-02-26 10:38:42'),
(14, 34, 4, 1, 'vide', NULL, NULL, '2026-02-25 16:56:12', '2026-02-25 16:56:12'),
(15, 34, 5, 1, 'complete', NULL, NULL, '2026-02-25 16:56:12', '2026-03-04 18:28:38'),
(16, 18, 16, 1, 'vide', NULL, NULL, '2026-02-26 10:29:10', '2026-02-26 10:29:10'),
(17, 18, 12, 2, 'vide', NULL, NULL, '2026-02-26 10:29:11', '2026-02-26 10:29:11'),
(18, 18, 13, 3, 'vide', NULL, NULL, '2026-02-26 10:29:11', '2026-02-26 10:29:11'),
(19, 10, 16, 1, 'complete', NULL, NULL, '2026-03-03 10:08:54', '2026-03-03 10:10:07'),
(20, 10, 4, 1, 'vide', NULL, NULL, '2026-03-03 10:08:58', '2026-03-03 10:14:34'),
(21, 35, 16, 1, 'complete', NULL, NULL, '2026-03-17 13:14:46', '2026-03-17 13:24:49'),
(22, 35, 4, 1, 'complete', NULL, NULL, '2026-03-17 13:14:46', '2026-03-17 16:35:30'),
(23, 35, 6, 1, 'complete', NULL, NULL, '2026-03-17 13:14:46', '2026-03-17 13:25:35'),
(24, 35, 10, 1, 'complete', NULL, NULL, '2026-03-17 13:20:16', '2026-03-17 16:35:18'),
(25, 39, 16, 1, 'complete', NULL, NULL, '2026-03-18 11:40:16', '2026-03-18 11:44:02'),
(26, 39, 8, 1, 'complete', NULL, NULL, '2026-03-18 11:40:16', '2026-03-18 11:45:20'),
(27, 41, 16, 1, 'complete', NULL, NULL, '2026-03-30 18:15:44', '2026-03-30 18:17:00'),
(28, 41, 14, 1, 'complete', NULL, NULL, '2026-03-30 18:15:44', '2026-03-30 18:17:58'),
(29, 41, 3, 1, 'complete', NULL, NULL, '2026-03-30 18:15:44', '2026-04-13 12:39:00'),
(30, 40, 3, 1, 'complete', NULL, NULL, '2026-04-13 13:42:29', '2026-06-03 09:16:06'),
(31, 32, 3, 1, 'vide', NULL, NULL, '2026-04-17 15:30:26', '2026-04-17 15:30:26'),
(32, 32, 17, 2, 'vide', NULL, NULL, '2026-04-17 15:30:26', '2026-04-17 15:30:26'),
(33, 8, 3, 1, 'complete', NULL, NULL, '2026-04-23 18:00:50', '2026-05-05 14:30:35'),
(34, 8, 17, 2, 'vide', NULL, NULL, '2026-04-23 18:00:51', '2026-04-23 18:00:51'),
(36, 42, 15, 1, 'complete', NULL, NULL, '2026-05-03 17:25:21', '2026-05-03 17:26:19'),
(37, 42, 3, 1, 'complete', NULL, NULL, '2026-05-03 17:25:21', '2026-05-03 17:26:41'),
(38, 42, 17, 1, 'complete', NULL, NULL, '2026-05-03 17:25:21', '2026-05-03 17:27:10'),
(39, 38, 18, 1, 'vide', NULL, NULL, '2026-05-07 20:09:00', '2026-05-07 20:09:00'),
(40, 42, 19, 1, 'complete', NULL, NULL, '2026-06-02 07:15:34', '2026-06-02 07:34:08'),
(42, 40, 18, 2, 'complete', NULL, NULL, '2026-06-02 11:08:47', '2026-07-01 02:55:23'),
(43, 40, 17, 3, 'complete', NULL, NULL, '2026-06-02 11:08:47', '2026-07-01 02:55:23'),
(45, 40, 19, 4, 'complete', NULL, NULL, '2026-06-03 10:57:52', '2026-07-01 02:55:23'),
(46, 40, 20, 5, 'complete', NULL, NULL, '2026-06-03 11:19:49', '2026-07-01 02:55:23'),
(47, 40, 21, 6, 'complete', NULL, NULL, '2026-06-05 01:19:27', '2026-07-01 02:55:23'),
(48, 40, 16, 7, 'complete', NULL, NULL, '2026-06-10 14:20:23', '2026-07-01 02:55:23'),
(49, 40, 22, 8, 'complete', NULL, NULL, '2026-06-11 10:25:32', '2026-07-01 02:55:23'),
(50, 40, 23, 9, 'complete', 'Nous soussigné STE MAJESTY SERVICES & EQUIPEMENTS SARL,\r\nci-après dénommé « le Soumissionnaire » :\r\n\r\nattestons avoir pris connaissance des dispositions relatives à la lutte contre la corruption, les conflits d’intérêt, la répression de l’enrichissement illicite, l’éthique professionnelle et tous autres actes similaires prévus au code d’éthique et de déontologie dans la commande publique en République du Bénin et prenons solennellement l’engagement de les respecter sous peine de subir les sanctions prévues à cet effet.\r\ndéclarons sur l’honneur n’avoir pratiqué dans le cadre du présent marché, aucune collusion avec d’autres soumissionnaires en vue de présenter des offres dont les montants seraient anormalement élevés.\r\nnous engageons, en notre nom propre, au nom de notre société et de nos préposés,\r\n[Insérer, en cas de sous-traitance : « ainsi qu’au nom de nos sous-traitants »], à nous abstenir de toute pratique liée à la corruption active et/ou passive dans le cadre de ce marché.\r\nnous engageons personnellement et engageons notre société ainsi que nos préposés,\r\n[Insérer, en cas de sous-traitance : « ainsi qu’au nom de nos sous-traitants »], à communiquer par écrit à l’Autorité contractante, à la Direction nationale de contrôle des marchés publics (DNCMP) et à l’Autorité de régulation des marchés publics (ARMP) et ce, en toute bonne foi :\r\ntout incident remettant en cause, de quelque manière que ce soit, l’exécution du présent marché ;\r\nl’existence d’un éventuel conflit d’intérêt.\r\nnous engageons personnellement et engageons notre société ainsi que nos préposés à nous abstenir de proposer ou de donner, directement ou indirectement, des avantages en nature et/ou en espèces, antérieurement ou postérieurement à la soumission de notre candidature.\r\nreconnaissons qu’en cas de manquement aux engagements ci-dessus, nous nous exposons aux sanctions prévues à l’article 123 de la loi n°2020-26 du 29 septembre 2020 portant Code des marchés publics en République du Bénin, ou par tous les autres textes réglementaires en République du Bénin, ainsi qu’aux sanctions de disqualification ou d’exclusion de toute activité en matière de marchés publics que pourrait prononcer l’Autorité de régulation des marchés publics (ARMP).\r\n\r\nLe présent engagement fait partie intégrante du marché.\r\n\r\nNom : TCHABY Onésime Godwin Akambi Adjè Tchègoun agissant au nom et pour le compte de\r\nMAJESTY SERVICES & EQUIPEMENTS SARL en qualité de Gérant\r\n\r\nSigné\r\n\r\nFait à Cotonou le 17/07/2025', NULL, '2026-06-16 12:33:35', '2026-07-01 02:55:26'),
(52, 40, 24, 10, 'complete', '[{\"poste\":\"S\\u00e9cr\\u00e9taire\",\"nom\":\"Lucr\\u00e8ce SAVI\"},{\"poste\":\"Gestionnaire des ressources humaines\",\"nom\":\"Caron ADJANOHOUN\"}]', NULL, '2026-06-17 11:16:00', '2026-07-01 02:55:26'),
(54, 40, 25, 11, 'complete', NULL, NULL, '2026-06-23 15:07:51', '2026-07-01 02:55:28'),
(55, 40, 2, 12, 'complete', NULL, NULL, '2026-06-23 16:16:22', '2026-07-01 02:55:29'),
(57, 40, 26, 13, 'complete', NULL, NULL, '2026-06-23 17:19:36', '2026-07-01 02:55:29'),
(58, 40, 27, 14, 'complete', NULL, NULL, '2026-06-23 19:49:52', '2026-07-01 02:55:29'),
(59, 40, 28, 15, 'complete', NULL, NULL, '2026-06-23 21:10:31', '2026-07-01 02:55:30'),
(60, 40, 29, 16, 'complete', NULL, NULL, '2026-06-24 11:23:49', '2026-07-01 02:55:30'),
(61, 40, 33, 17, 'complete', '{\"a\":1000,\"b\":180,\"c\":1180,\"d\":0,\"e\":0,\"f\":0,\"g\":1000,\"h\":180,\"i\":1180}', NULL, '2026-06-24 15:59:18', '2026-07-01 02:55:30'),
(63, 40, 34, 18, 'complete', NULL, NULL, '2026-06-29 18:38:42', '2026-07-01 02:55:32'),
(64, 40, 35, 19, 'complete', NULL, NULL, '2026-06-29 18:38:42', '2026-07-01 02:55:33');

-- --------------------------------------------------------

--
-- Structure de la table `dossier_signataire`
--

CREATE TABLE `dossier_signataire` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `signataire_id` bigint(20) UNSIGNED NOT NULL,
  `role_signataire` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_utilisateurs`
--

CREATE TABLE `dossier_utilisateurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dossier_utilisateurs`
--

INSERT INTO `dossier_utilisateurs` (`id`, `dossier_id`, `utilisateur_id`, `created_at`, `updated_at`) VALUES
(1, 40, 2, '2026-07-01 09:48:10', '2026-07-01 09:48:10'),
(2, 40, 1, '2026-07-01 09:48:10', '2026-07-01 09:48:10'),
(3, 43, 2, '2026-07-01 12:35:36', '2026-07-01 12:35:36');

-- --------------------------------------------------------

--
-- Structure de la table `entreprises`
--

CREATE TABLE `entreprises` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `sigle` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `adresse_officielle` text DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pays` varchar(255) DEFAULT NULL,
  `ifu` varchar(255) DEFAULT NULL,
  `registre_path` varchar(255) DEFAULT NULL,
  `annee_enregistrement` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `fonction_responsable` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `entreprises`
--

INSERT INTO `entreprises` (`id`, `nom`, `sigle`, `adresse`, `adresse_officielle`, `telephone`, `email`, `pays`, `ifu`, `registre_path`, `annee_enregistrement`, `logo`, `responsable`, `fonction_responsable`, `created_at`, `updated_at`) VALUES
(1, 'MAJESTY', 'MAJ', '123 Avenue Principale, Yaoundé', NULL, '+237 222 111 222', 'contact@majesty.cm', NULL, NULL, NULL, NULL, '/images/logo-majesty.png', 'Jean Dupont', 'Directeur Général', '2026-02-05 11:37:30', '2026-02-05 11:37:30'),
(2, 'Majesty', 'equipement', 'kohegbo', NULL, '0197979797', NULL, 'benin', '22339447666', NULL, NULL, NULL, 'Onésime Tchaby', 'PDG', '2026-02-09 19:29:12', '2026-02-09 19:29:12');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formulaire_mats`
--

CREATE TABLE `formulaire_mats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `piece_materiel` varchar(255) NOT NULL,
  `fabricant` varchar(255) DEFAULT NULL,
  `modele_puissance` varchar(255) DEFAULT NULL,
  `capacite` varchar(255) DEFAULT NULL,
  `annee_fabrication` varchar(255) DEFAULT NULL,
  `localisation` varchar(255) DEFAULT NULL,
  `engagements` text DEFAULT NULL,
  `provenance` varchar(255) DEFAULT NULL,
  `signataire_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lieu_fait` varchar(255) DEFAULT NULL,
  `date_fait` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formulaire_mats`
--

INSERT INTO `formulaire_mats` (`id`, `utilisateur_id`, `piece_materiel`, `fabricant`, `modele_puissance`, `capacite`, `annee_fabrication`, `localisation`, `engagements`, `provenance`, `signataire_id`, `lieu_fait`, `date_fait`, `created_at`, `updated_at`) VALUES
(1, 1, 'SAC', 'MOI', 'OUI', 'COOL', '2003', 'COTONOU', 'rien', 'en_possession', 1, 'Cotonou', NULL, '2026-06-15 15:05:40', '2026-07-01 01:34:46');

-- --------------------------------------------------------

--
-- Structure de la table `formulaire_pers`
--

CREATE TABLE `formulaire_pers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `nom_candidat` varchar(255) DEFAULT NULL,
  `poste` varchar(255) DEFAULT NULL,
  `nom_personnel` varchar(255) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `nom_employeur` varchar(255) DEFAULT NULL,
  `adresse_employeur` text DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `contact_personnel` varchar(255) DEFAULT NULL,
  `telecopie` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `emploi_tenu` varchar(255) DEFAULT NULL,
  `nombre_annees_employeur` int(11) DEFAULT NULL,
  `experiences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`experiences`)),
  `signature` text DEFAULT NULL,
  `date_signature` date DEFAULT NULL,
  `lieu_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formulaire_pers`
--

INSERT INTO `formulaire_pers` (`id`, `utilisateur_id`, `nom_candidat`, `poste`, `nom_personnel`, `date_naissance`, `qualifications`, `nom_employeur`, `adresse_employeur`, `telephone`, `contact_personnel`, `telecopie`, `email`, `emploi_tenu`, `nombre_annees_employeur`, `experiences`, `signature`, `date_signature`, `lieu_signature`, `created_at`, `updated_at`) VALUES
(1, 1, 'Francine Savi', 'Directrice', 'SAVI', '2026-06-24', 'LICENCE EN SIL', 'TCHABY', 'Kowegbo', '+229 95959595', '66474875', NULL, 'savifrancine007@gmail.com', 'DIRECTRICE', 4, '[{\"de\":\"2009\",\"a\":\"2020\",\"description\":\"AFT\"}]', NULL, NULL, NULL, '2026-06-18 14:04:32', '2026-06-18 14:58:38');

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_04_145634_create_daos_table', 1),
(5, '2026_02_04_140000_create_utilisateurs_table', 2),
(6, '2026_02_05_100000_create_types_dossiers_table', 3),
(7, '2026_02_05_100100_create_entreprises_table', 3),
(8, '2026_02_05_100200_create_dossiers_table', 3),
(9, '2026_02_05_100300_create_types_documents_table', 3),
(10, '2026_02_05_100400_create_dossier_documents_table', 3),
(11, '2026_02_05_100500_create_champs_documents_table', 3),
(12, '2026_02_05_100600_create_valeurs_documents_table', 3),
(13, '2026_02_05_100700_create_documents_fichiers_table', 3),
(14, '2026_02_05_100800_create_bordereaux_table', 3),
(15, '2026_02_05_100900_create_bordereau_lignes_table', 3),
(16, '2026_02_05_101000_add_categorie_to_types_dossiers', 4),
(17, '2026_02_04_145441_create_utilisateurs_table', 5),
(18, '2026_02_04_145738_create_dossiers_table', 5),
(19, '2026_02_04_145814_create_dossier_utilisateurs_table', 5),
(20, '2026_02_04_145903_create_types_documents_table', 5),
(21, '2026_02_04_150005_create_dossier_documents_table', 5),
(22, '2026_02_04_150037_create_documents_textes_table', 5),
(23, '2026_02_04_150126_create_documents_fichiers_table', 5),
(24, '2026_02_04_150203_create_bordereaux_table', 5),
(25, '2026_02_04_150322_create_bordereau_lignes_table', 5),
(26, '2026_02_05_101100_update_types_dossiers_with_categorie', 5),
(27, '2026_02_06_101000_create_types_marches_table', 5),
(28, '2026_02_06_101100_create_procedures_table', 5),
(29, '2026_02_06_101200_create_autorites_contractantes_table', 5),
(30, '2026_02_06_101300_create_sources_financement_table', 5),
(31, '2026_02_06_101400_create_signataires_table', 5),
(32, '2026_02_06_101500_create_dossier_signataire_table', 5),
(33, '2026_02_06_101600_update_dossiers_add_page_garde_fields', 5),
(34, '2026_02_06_000001_add_page_garde_path_to_dossiers', 6),
(35, '2026_02_06_000002_add_cover_fields_to_dossiers', 6),
(36, '2026_02_06_120000_update_entreprises_add_country_ifu_registre', 6),
(37, '2026_02_06_000003_add_type_offre_drop_types_offres', 7),
(38, '2026_02_24_120000_add_ref_to_dossiers', 8),
(39, '2026_02_25_000000_create_templates_table', 9),
(40, '2026_03_18_000000_add_date_soumission_to_dossiers', 10),
(41, '2026_03_19_000000_add_declaration_content_to_dossier_documents_table', 11),
(42, '2026_04_17_000001_add_programme_activites_fields_to_bordereau_lignes_table', 12),
(43, '2026_02_05_100850_add_column_defs_to_bordereaux', 13),
(44, '2026_06_03_120000_add_specifications_to_bordereau_lignes_table', 14),
(45, '2026_06_04_130000_add_fields_to_entreprises', 15),
(46, '2026_06_04_131000_insert_formulaire_renseignements_candidat_type_document', 16),
(47, '2026_06_10_000001_create_chiffres_affaires_table', 17),
(48, '2026_06_11_000001_insert_chiffre_affaires_type_document', 18),
(49, '2026_06_15_000000_create_formulaire_mats_table', 19),
(50, '2026_06_16_000000_update_formulaire_mats_add_signataire_id', 20),
(51, '2026_06_15_000000_add_content_to_dossier_documents_table', 21),
(52, '2026_02_18_000000_create_formulaire_pers_table', 22),
(53, '2026_06_23_000000_add_cout_benin_to_bordereau_lignes', 23),
(54, '2026_06_23_000001_add_frequence_to_bordereau_lignes', 24),
(55, '2026_06_23_000002_add_cadres_fields_to_bordereau_lignes', 25);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `procedures`
--

CREATE TABLE `procedures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `procedures`
--

INSERT INTO `procedures` (`id`, `nom`, `created_at`, `updated_at`) VALUES
(1, 'bkkkkk', '2026-02-06 12:09:06', '2026-02-06 12:09:06');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('9lxMtwjcoae9o5hbly5EXLWdXaYdVQz7Re51CkQ3', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTG1NVWl5em1Ob3BQRjY5Mklra3VsajFXRnlyUGphWlllYkljWUhSOSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMiO3M6NToicm91dGUiO3M6MTQ6ImRvc3NpZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1782900649),
('DC752Iqga2Tm7oHnlm6yucznKWexteEJgcLHAuaM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSjZGM2RBUEc5MWdWbkx6M1FUdnplaWg3UTNlZFFFdjlWTHBicDJSZyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895761),
('F1ZGTxUNMmk8R70YO9gNqiuwtuaUCYC93AEcgfj0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWHJvc211eDZSWjFCSkNLVkNjWlhhN3pRd3hwZU5OQmdpS0JBbjh1ZiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895975),
('FRPbwyZAz2Po2oVYNnIHAO7ymnejx2P7o50TddnB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieFljVUFoSlJ5VzlPUUFtdU5LVnRpZjU2V3RrVWNGQmwxMzIybmpBSyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895761),
('HmgKdA8gEqxtAFPYhrmrAZ54BWU8SrgKRqJ7AdWk', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQkxWRUloclBkWDZYSUY5UFdsRG1mRUdiRkh2MThBR2QwR3dLRVpNYSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895897),
('hQXCAmvqN0rykD55dgR3p9YTagSOWDXU73sEELHX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTGRkNmt3ODhVSlhVVHFYSTZDMk45U1h4VDI4Q2xvNk9TUllhZnlTMyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895758),
('Ki6rJUdS7AT2AQDIWldIn1NYj2mpHXkSjsudPcXd', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRUNObmUwWkNVc0twME03S3FaWXptbXJUdXExb3pYYVlzWFFwYXZyYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895757),
('Mj87soysBhNbjXpjaqACHy7E6tKPMAs2wKKtDVst', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYVBxUEFWN2NJZkpiSWZoY01OSkxsdXgwN1NmcGtaZms1RXNnenBuSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kb3NzaWVycy9zaG93LzQwIjtzOjU6InJvdXRlIjtzOjEzOiJkb3NzaWVycy5zaG93Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1782912936),
('o9cG81CjBC4RO9vNblFvSHtsa81aXVPTJtZg1VBM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMUdZWERkY2lyQnJqMzhMVFhXeFgwY3A3UGkwWXFOMEI1cXFld2g3YiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895973),
('pMcglWzuzHUIUUPPK8aWytvsQVZtZYsY5nV3jIKP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT1NZMkoxVU5EY0RHZXVySFdTTUhWTktuem1xbkxZWGdkTnR5Tk51aCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895809),
('QVfMScy3bsW7Lui3uq6BeRrPyX8HXyFeDKKR7LGi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNWw5eXkzZXI5VXhXQUV6SDdzT21Cd3JxS2FGRDdJUWRQd0dnTTZoWCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895881),
('Uzx9YyubesSfTPLKvmeZZA91lQcSaU1hnZXvMqzG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRWNGZll5MU54ekMwQ0VwRzFpdHZiSTBMcGttdWtXMmlHQXFabFdvVyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895766),
('vrwi98J6lCUsGmfpd8NAOkQEaLLG36Wkj6UTW7Pk', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoickw1Q0k4NlhReFNIMk5Ib0RRbDBhMHVWNFV0d1RCWmp4N2RrVmdyRyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782895758),
('Vs1o9yYognrNtPl3try4odX3goijftLlXJ6lp2oL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVVlUS2pscjVQZUg1VnpKNXJpaVh1dFVSU1JPRW1LdFE3WGR1QnFJTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1783010873),
('VWzXts9N81qhL555dFbUcQ4IZbefHVJk0UbPSuW2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUEtWV29nQ0h2Z3BRWlN4bVBDV211N0l5U05RQkhGNmhGNFc5SGR1QiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rvc3NpZXJzLzQwL3BkZiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1783010855),
('xWRbwoauSRLCo8gvAM6D0sjmCBzOwy90Q2IpWSPd', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaDlYbHV1WHFlNTNGZ1ZjaDJ0dFRGVUJTeTFWQmZDdnVtY0Q1cmxrUCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9zc2llcnMvNDAvcGRmIjtzOjU6InJvdXRlIjtzOjEyOiJkb3NzaWVycy5wZGYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1782916583);

-- --------------------------------------------------------

--
-- Structure de la table `signataires`
--

CREATE TABLE `signataires` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `fonction` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `cachet_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `signataires`
--

INSERT INTO `signataires` (`id`, `nom`, `prenom`, `fonction`, `signature_path`, `cachet_path`, `created_at`, `updated_at`) VALUES
(1, 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun', 'Onésime Godwin Akambi Adjè Tèhègoun', 'LE GERANT', NULL, NULL, '2026-03-18 11:39:21', '2026-03-18 11:39:21');

-- --------------------------------------------------------

--
-- Structure de la table `sources_financement`
--

CREATE TABLE `sources_financement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `templates`
--

CREATE TABLE `templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `templates`
--

INSERT INTO `templates` (`id`, `nom`, `type`, `content`, `created_at`, `updated_at`) VALUES
(1, 'Modèle - Déclaration de garantie d\'offre', 'declaration', 'Nous, soussignés, déclarons que :\r\n\r\nNous reconnaissons que les offres doivent être accompagnées d\'une déclaration de garantie d\'offre.\r\n\r\nNous acceptons que nous ferons l\'objet d\'une suspension du droit de participer à la commande publique pour une période qui ne saurait être inférieure à un (01) an, si nous n\'exécutons pas une des obligations auxquelles nous sommes tenus en vertu de l\'offre, à savoir :\r\n\r\na) Si nous retirons l\'offre pendant la période de validité spécifiée dans la lettre de soumission de l\'offre ; ou\r\n\r\nb) s\'étant vu notifier l\'acceptation de l\'offre par l\'Autorité contractante pendant la période de validité telle qu\'indiquée dans la lettre de soumission de l\'offre ou prorogée par l\'Autorité contractante avant l\'expiration de cette période :\r\n\r\nsi nous n\'acceptons pas les modifications de notre offre suite à la correction des erreurs de calcul ; ou\r\n\r\nsi nous ne signons pas le marché ; ou\r\n\r\nsi nous signons le marché et refusons de l\'exécuter ; ou\r\n\r\nsi nous ne fournissons pas la garantie de bonne exécution du marché, si nous sommes tenus de le faire ainsi qu\'il est prévu dans les Instructions aux candidats ;\r\n\r\nc) si nous sommes sous le coup d\'une sanction de l\'Autorité de régulation des marchés publics ou d\'une juridiction administrative compétente, ayant pour objet la confiscation des garanties que nous avons constituées dans le cadre de la passation du marché, conformément à l\'article 123 de la loi n°2020-26 du 29 septembre 2020 portant code des marchés publics en République du Bénin.\r\n\r\nLa présente lettre de déclaration de garantie expirera si le marché ne nous est pas attribué, à la première des dates suivantes :\r\n(i) lorsque nous recevrons copie de votre notification du nom du soumissionnaire retenu, ou\r\n(ii) trente (30) jours suivant l\'expiration du délai de validité de notre offre.\r\n\r\nIl est entendu que si nous sommes un groupement d\'entreprises, la déclaration de garantie d\'offre doit être au nom du groupement qui soumet l\'offre. Si le groupement n\'a pas été formellement constitué lors du dépôt d\'offre, la déclaration de garantie de l\'offre doit être au nom de tous les futurs membres du groupement nommés dans la lettre de déclaration.', '2026-02-25 11:50:45', '2026-03-18 11:42:48'),
(2, 'Modèle - Lettre de soumission', 'lettre', '<p>Lettre de soumission pour {{societe}}</p>', '2026-02-25 11:50:45', '2026-02-25 11:50:45');

-- --------------------------------------------------------

--
-- Structure de la table `types_documents`
--

CREATE TABLE `types_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `type_formulaire` enum('formulaire','fichier','bordereau') NOT NULL DEFAULT 'formulaire',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `types_documents`
--

INSERT INTO `types_documents` (`id`, `nom`, `type_formulaire`, `created_at`, `updated_at`) VALUES
(1, 'Lettre de soumission', 'formulaire', '2026-06-16 14:10:23', '2026-06-16 14:10:23'),
(2, 'RCCM', 'fichier', '2026-06-16 14:10:23', '2026-06-16 14:10:23'),
(3, 'Bordereau prix unitaire', 'bordereau', '2026-06-16 14:10:23', '2026-06-16 14:10:23'),
(4, 'Copie legalisee de l\'Extrait du RCCM', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(5, 'Copie legalisee de l\'Identifiant Fiscal Unique (IFU)', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(6, 'Attestation de non-faillite datant de moins de trois (03) mois', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(7, 'Attestation d\'imposition ou de situation fiscale en cours de validite', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(8, 'Attestation de regularite a la CNSS', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(9, 'Attestation de non-exclusion de la commande publique', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(10, 'Engagement a respecter le code d\'ethique et de deontologie de la commande publique', 'formulaire', '2026-05-08 09:00:37', '2026-06-29 18:35:34'),
(11, 'Attestation de non-condamnation pour fraude, corruption ou fausse declaration', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(12, 'Attestation de nationalite ou document de constitution legale de l\'entreprise', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(13, 'Statuts de la societe et PV de nomination du gerant', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(14, 'Copie du quitus fiscal', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(15, 'Attestation de situation reguliere vis-a-vis des organismes de credit', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:35:34'),
(16, 'Déclaration de garantie d\'offre', 'formulaire', '2026-06-16 14:10:23', '2026-06-29 18:30:50'),
(17, 'Programme d\'activités', 'bordereau', '2026-06-16 14:10:23', '2026-06-30 11:14:29'),
(18, 'Méthodes d\'exécution', 'bordereau', '2026-06-16 14:10:23', '2026-06-30 11:14:29'),
(19, 'Calendrier d\'exécution', 'bordereau', '2026-06-16 14:10:23', '2026-06-30 11:14:30'),
(20, 'Description technique des services', 'bordereau', '2026-06-16 14:10:23', '2026-06-30 11:14:30'),
(21, 'Formulaire de renseignements sur le candidat', 'formulaire', '2026-06-16 14:10:23', '2026-06-16 14:10:23'),
(22, 'Chiffre d\'affaires annuel moyen des activités de services', 'formulaire', '2026-06-11 10:13:24', '2026-06-11 10:13:24'),
(23, 'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie', 'formulaire', '2026-06-16 14:10:23', '2026-06-16 14:10:23'),
(24, 'Liste du personnel affecté à l\'exécution du marché', 'formulaire', '2026-06-16 14:10:23', '2026-06-16 14:10:23'),
(25, 'Bordereau des prix pour les fournitures à importer', 'bordereau', '2026-06-23 12:12:20', '2026-06-23 12:12:20'),
(26, 'Bordereau des prix et calendrier d\'exécution des services connexes', 'bordereau', '2026-06-23 16:04:43', '2026-06-23 16:04:43'),
(27, 'Listes des services connexes et calendrier de réalisation', 'bordereau', '2026-06-23 19:43:39', '2026-06-23 19:43:39'),
(28, 'Listes des Fournitures et Calendrier de livraison', 'bordereau', '2026-06-23 20:50:42', '2026-06-30 11:14:29'),
(29, 'Cadres de sous détails des prix unitaire', 'bordereau', '2026-06-23 21:54:01', '2026-06-30 11:14:29'),
(30, 'Procuration spéciale', 'fichier', '2026-06-24 11:09:19', '2026-06-24 11:09:19'),
(31, 'Déclaration des Conflits d\'Intérêts', 'fichier', '2026-06-24 11:09:19', '2026-06-24 11:09:19'),
(32, 'Bordereau des prix unitaires pour les prestations de services', 'bordereau', '2026-06-24 11:09:19', '2026-06-24 11:09:19'),
(33, 'Tableau de résumé des bordereaux de prix', 'formulaire', '2026-06-24 15:58:04', '2026-06-24 15:58:04'),
(34, 'Formulaire MAT', 'formulaire', '2026-06-29 18:35:34', '2026-06-29 18:35:34'),
(35, 'Formulaire PER', 'formulaire', '2026-06-29 18:35:34', '2026-06-29 18:35:34');

-- --------------------------------------------------------

--
-- Structure de la table `types_dossiers`
--

CREATE TABLE `types_dossiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `categorie` enum('public','prive') NOT NULL DEFAULT 'public',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `types_dossiers`
--

INSERT INTO `types_dossiers` (`id`, `nom`, `categorie`, `created_at`, `updated_at`) VALUES
(1, 'DAO', 'public', '2026-02-05 11:37:30', '2026-02-05 11:37:30'),
(2, 'Demande de cotation', 'public', '2026-02-05 11:37:30', '2026-02-05 11:37:30'),
(3, 'Appel à manifestation d\'intérêt', 'prive', '2026-02-05 11:37:30', '2026-02-05 11:37:30'),
(4, 'Consultation restreinte', 'prive', '2026-02-05 11:37:30', '2026-02-05 11:37:30');

-- --------------------------------------------------------

--
-- Structure de la table `types_marches`
--

CREATE TABLE `types_marches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `types_marches`
--

INSERT INTO `types_marches` (`id`, `nom`, `created_at`, `updated_at`) VALUES
(1, 'ddhhmml', '2026-02-06 12:08:38', '2026-02-06 12:08:38'),
(2, 'Administrative', '2026-02-06 15:54:25', '2026-02-06 15:54:25');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '2026-02-05 11:37:31', '$2y$12$Ky8Y7JTsMeVMpnKd/JKSG.6KWqW2hrHePwFMxSciZl0Na7SoCO0sS', 'v37hltdiHS', '2026-02-05 11:37:32', '2026-02-05 11:37:32');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'employe',
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'Oui', 'test@yahoo.fr', '$2y$12$41Y7A8FGCHKrQatW/IkiZOe4yrNZYLJb3CFrC6xLCs.TY/DuG9Czi', 'employe', 1, '2026-02-04 17:10:47', '2026-06-11 11:57:09'),
(2, 'Admin', 'Super', 'admin@dao.local', '$2y$12$ck8rZEf0diavicG32MXkZ.bnT9wgd92NN9h.te4wE0M8vDbcsUjJu', 'admin', 1, '2026-07-01 09:44:26', '2026-07-01 09:44:26');

-- --------------------------------------------------------

--
-- Structure de la table `valeurs_documents`
--

CREATE TABLE `valeurs_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_document_id` bigint(20) UNSIGNED NOT NULL,
  `champ_document_id` bigint(20) UNSIGNED NOT NULL,
  `valeur` text DEFAULT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `autorites_contractantes`
--
ALTER TABLE `autorites_contractantes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `bordereaux`
--
ALTER TABLE `bordereaux`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bordereaux_dossier_document_id_foreign` (`dossier_document_id`);

--
-- Index pour la table `bordereau_lignes`
--
ALTER TABLE `bordereau_lignes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bordereau_lignes_bordereau_id_foreign` (`bordereau_id`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `champs_documents`
--
ALTER TABLE `champs_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `champs_documents_type_document_id_foreign` (`type_document_id`);

--
-- Index pour la table `chiffres_affaires`
--
ALTER TABLE `chiffres_affaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chiffres_affaires_dossier_id_foreign` (`dossier_id`);

--
-- Index pour la table `daos`
--
ALTER TABLE `daos`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `documents_fichiers`
--
ALTER TABLE `documents_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_fichiers_dossier_document_id_foreign` (`dossier_document_id`),
  ADD KEY `documents_fichiers_utilisateur_id_foreign` (`utilisateur_id`);

--
-- Index pour la table `documents_textes`
--
ALTER TABLE `documents_textes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_textes_dossier_document_id_foreign` (`dossier_document_id`),
  ADD KEY `documents_textes_utilisateur_id_foreign` (`utilisateur_id`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossiers_type_dossier_id_foreign` (`type_dossier_id`),
  ADD KEY `dossiers_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `dossiers_type_marche_id_foreign` (`type_marche_id`),
  ADD KEY `dossiers_procedure_id_foreign` (`procedure_id`),
  ADD KEY `dossiers_autorite_contractante_id_foreign` (`autorite_contractante_id`),
  ADD KEY `dossiers_source_financement_id_foreign` (`source_financement_id`);

--
-- Index pour la table `dossier_documents`
--
ALTER TABLE `dossier_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_documents_dossier_id_foreign` (`dossier_id`),
  ADD KEY `dossier_documents_type_document_id_foreign` (`type_document_id`);

--
-- Index pour la table `dossier_signataire`
--
ALTER TABLE `dossier_signataire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_signataire_dossier_id_foreign` (`dossier_id`),
  ADD KEY `dossier_signataire_signataire_id_foreign` (`signataire_id`);

--
-- Index pour la table `dossier_utilisateurs`
--
ALTER TABLE `dossier_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_utilisateurs_dossier_id_foreign` (`dossier_id`),
  ADD KEY `dossier_utilisateurs_utilisateur_id_foreign` (`utilisateur_id`);

--
-- Index pour la table `entreprises`
--
ALTER TABLE `entreprises`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `formulaire_mats`
--
ALTER TABLE `formulaire_mats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulaire_mats_utilisateur_id_foreign` (`utilisateur_id`),
  ADD KEY `formulaire_mats_signataire_id_foreign` (`signataire_id`);

--
-- Index pour la table `formulaire_pers`
--
ALTER TABLE `formulaire_pers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulaire_pers_utilisateur_id_foreign` (`utilisateur_id`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `procedures`
--
ALTER TABLE `procedures`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `signataires`
--
ALTER TABLE `signataires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sources_financement`
--
ALTER TABLE `sources_financement`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `types_documents`
--
ALTER TABLE `types_documents`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `types_dossiers`
--
ALTER TABLE `types_dossiers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `types_marches`
--
ALTER TABLE `types_marches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateurs_email_unique` (`email`);

--
-- Index pour la table `valeurs_documents`
--
ALTER TABLE `valeurs_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `valeurs_documents_dossier_document_id_foreign` (`dossier_document_id`),
  ADD KEY `valeurs_documents_champ_document_id_foreign` (`champ_document_id`),
  ADD KEY `valeurs_documents_utilisateur_id_foreign` (`utilisateur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `autorites_contractantes`
--
ALTER TABLE `autorites_contractantes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bordereaux`
--
ALTER TABLE `bordereaux`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `bordereau_lignes`
--
ALTER TABLE `bordereau_lignes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pour la table `champs_documents`
--
ALTER TABLE `champs_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `chiffres_affaires`
--
ALTER TABLE `chiffres_affaires`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `daos`
--
ALTER TABLE `daos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents_fichiers`
--
ALTER TABLE `documents_fichiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `documents_textes`
--
ALTER TABLE `documents_textes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `dossier_documents`
--
ALTER TABLE `dossier_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT pour la table `dossier_signataire`
--
ALTER TABLE `dossier_signataire`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_utilisateurs`
--
ALTER TABLE `dossier_utilisateurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `entreprises`
--
ALTER TABLE `entreprises`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `formulaire_mats`
--
ALTER TABLE `formulaire_mats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `formulaire_pers`
--
ALTER TABLE `formulaire_pers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `procedures`
--
ALTER TABLE `procedures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `signataires`
--
ALTER TABLE `signataires`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `sources_financement`
--
ALTER TABLE `sources_financement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `types_documents`
--
ALTER TABLE `types_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `types_dossiers`
--
ALTER TABLE `types_dossiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `types_marches`
--
ALTER TABLE `types_marches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `valeurs_documents`
--
ALTER TABLE `valeurs_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bordereaux`
--
ALTER TABLE `bordereaux`
  ADD CONSTRAINT `bordereaux_dossier_document_id_foreign` FOREIGN KEY (`dossier_document_id`) REFERENCES `dossier_documents` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bordereau_lignes`
--
ALTER TABLE `bordereau_lignes`
  ADD CONSTRAINT `bordereau_lignes_bordereau_id_foreign` FOREIGN KEY (`bordereau_id`) REFERENCES `bordereaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `champs_documents`
--
ALTER TABLE `champs_documents`
  ADD CONSTRAINT `champs_documents_type_document_id_foreign` FOREIGN KEY (`type_document_id`) REFERENCES `types_documents` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `chiffres_affaires`
--
ALTER TABLE `chiffres_affaires`
  ADD CONSTRAINT `chiffres_affaires_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_fichiers`
--
ALTER TABLE `documents_fichiers`
  ADD CONSTRAINT `documents_fichiers_dossier_document_id_foreign` FOREIGN KEY (`dossier_document_id`) REFERENCES `dossier_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_fichiers_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_textes`
--
ALTER TABLE `documents_textes`
  ADD CONSTRAINT `documents_textes_dossier_document_id_foreign` FOREIGN KEY (`dossier_document_id`) REFERENCES `dossier_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_textes_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD CONSTRAINT `dossiers_autorite_contractante_id_foreign` FOREIGN KEY (`autorite_contractante_id`) REFERENCES `autorites_contractantes` (`id`),
  ADD CONSTRAINT `dossiers_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`),
  ADD CONSTRAINT `dossiers_procedure_id_foreign` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`),
  ADD CONSTRAINT `dossiers_source_financement_id_foreign` FOREIGN KEY (`source_financement_id`) REFERENCES `sources_financement` (`id`),
  ADD CONSTRAINT `dossiers_type_dossier_id_foreign` FOREIGN KEY (`type_dossier_id`) REFERENCES `types_dossiers` (`id`),
  ADD CONSTRAINT `dossiers_type_marche_id_foreign` FOREIGN KEY (`type_marche_id`) REFERENCES `types_marches` (`id`);

--
-- Contraintes pour la table `dossier_documents`
--
ALTER TABLE `dossier_documents`
  ADD CONSTRAINT `dossier_documents_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_documents_type_document_id_foreign` FOREIGN KEY (`type_document_id`) REFERENCES `types_documents` (`id`);

--
-- Contraintes pour la table `dossier_signataire`
--
ALTER TABLE `dossier_signataire`
  ADD CONSTRAINT `dossier_signataire_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_signataire_signataire_id_foreign` FOREIGN KEY (`signataire_id`) REFERENCES `signataires` (`id`);

--
-- Contraintes pour la table `dossier_utilisateurs`
--
ALTER TABLE `dossier_utilisateurs`
  ADD CONSTRAINT `dossier_utilisateurs_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_utilisateurs_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formulaire_mats`
--
ALTER TABLE `formulaire_mats`
  ADD CONSTRAINT `formulaire_mats_signataire_id_foreign` FOREIGN KEY (`signataire_id`) REFERENCES `signataires` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `formulaire_mats_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formulaire_pers`
--
ALTER TABLE `formulaire_pers`
  ADD CONSTRAINT `formulaire_pers_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `valeurs_documents`
--
ALTER TABLE `valeurs_documents`
  ADD CONSTRAINT `valeurs_documents_champ_document_id_foreign` FOREIGN KEY (`champ_document_id`) REFERENCES `champs_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `valeurs_documents_dossier_document_id_foreign` FOREIGN KEY (`dossier_document_id`) REFERENCES `dossier_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `valeurs_documents_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
