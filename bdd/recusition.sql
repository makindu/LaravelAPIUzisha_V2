-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 02 oct. 2022 à 10:06
-- Version du serveur : 10.4.24-MariaDB
-- Version de PHP : 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `recusition`
--

-- --------------------------------------------------------

--
-- Structure de la table `affectation_users`
--

CREATE TABLE `affectation_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attemptactivationaccounts`
--

CREATE TABLE `attemptactivationaccounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_chiefdepartments`
--

CREATE TABLE `decision_chiefdepartments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `response` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `decision_chiefdepartments`
--

INSERT INTO `decision_chiefdepartments` (`id`, `response`, `user_id`, `request_id`, `created_at`, `updated_at`) VALUES
(1, '1', 1, 1, '2022-10-01 08:04:32', '2022-10-01 08:04:32');

-- --------------------------------------------------------

--
-- Structure de la table `decision_decisionteams`
--

CREATE TABLE `decision_decisionteams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `response` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_teams`
--

CREATE TABLE `decision_teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `access` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `decision_teams`
--

INSERT INTO `decision_teams` (`id`, `access`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'rw', 1, '2022-09-28 09:19:31', '2022-09-28 09:19:31'),
(2, 'ro', 3, '2022-09-28 09:20:27', '2022-09-28 09:20:27');

-- --------------------------------------------------------

--
-- Structure de la table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `created_at`, `updated_at`) VALUES
(1, 'finances', '2022-09-28 09:14:57', '2022-09-28 09:14:57'),
(2, 'I.T', '2022-09-28 09:15:03', '2022-09-28 09:15:03'),
(3, 'Logistique', '2022-09-28 09:15:08', '2022-09-28 09:15:08');

-- --------------------------------------------------------

--
-- Structure de la table `details_requests`
--

CREATE TABLE `details_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `qte` int(11) NOT NULL,
  `detail_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pu` double NOT NULL,
  `tot` double NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `funds`
--

CREATE TABLE `funds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sold` double DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `money_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `funds`
--

INSERT INTO `funds` (`id`, `sold`, `description`, `money_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 8900000, 'Caisse principale CDF', 2, 2, '2022-09-28 09:18:52', '2022-09-28 09:18:52'),
(2, 9e35, 'Caisse secondaire en CDF', 2, 2, '2022-09-28 09:19:11', '2022-09-28 09:19:11');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(51, '2012_09_14_212612_create_departements_table', 1),
(52, '2014_10_12_000000_create_users_table', 1),
(53, '2014_10_12_100000_create_password_resets_table', 1),
(54, '2019_08_19_000000_create_failed_jobs_table', 1),
(55, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(56, '2022_09_14_212949_create_sub_departements_table', 1),
(57, '2022_09_14_213657_create_systeminfos_table', 1),
(58, '2022_09_14_214025_create_money_conversions_table', 1),
(59, '2022_09_14_214458_create_nbrdecisionteam_validations_table', 1),
(60, '2022_09_14_214651_create_requests_table', 1),
(61, '2022_09_14_215614_create_request_files_table', 1),
(62, '2022_09_14_220121_create_request_references_table', 1),
(63, '2022_09_14_220450_create_moneys_table', 1),
(64, '2022_09_14_220451_create_funds_table', 1),
(65, '2022_09_14_220746_create_request_serveds_table', 1),
(66, '2022_09_14_221235_create_self_references_table', 1),
(67, '2022_09_14_221436_create_validatedbydecisionteams_table', 1),
(68, '2022_09_14_222039_create_details_requests_table', 1),
(69, '2022_09_14_222625_create_decision_teams_table', 1),
(70, '2022_09_14_223322_create_decision_decisionteams_table', 1),
(71, '2022_09_14_223821_create_decision_chiefdepartments_table', 1),
(72, '2022_09_14_225049_create_comments_table', 1),
(73, '2022_09_14_225425_create_attemptactivationaccounts_table', 1),
(74, '2022_09_14_225652_create_affectation_users_table', 1),
(75, '2022_09_27_100405_create_request_histories_table', 1);

-- --------------------------------------------------------

--
-- Structure de la table `moneys`
--

CREATE TABLE `moneys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `abreviation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `principal` int(11) DEFAULT NULL,
  `money_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `moneys`
--

INSERT INTO `moneys` (`id`, `abreviation`, `principal`, `money_name`, `created_at`, `updated_at`) VALUES
(1, 'USD', 1, 'Dollars amercains', '2022-09-28 09:15:24', '2022-09-28 09:15:24'),
(2, 'CDF', NULL, 'Francs Congolais', '2022-09-28 09:15:35', '2022-09-28 09:15:35');

-- --------------------------------------------------------

--
-- Structure de la table `money_conversions`
--

CREATE TABLE `money_conversions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `money_id1` int(11) NOT NULL,
  `money_id2` int(11) NOT NULL,
  `rate` double(8,2) NOT NULL,
  `operator` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `money_conversions`
--

INSERT INTO `money_conversions` (`id`, `money_id1`, `money_id2`, `rate`, `operator`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1.00, 1, '2022-09-28 09:15:51', '2022-09-28 09:15:51'),
(2, 1, 2, 1.00, 1, '2022-09-28 09:17:46', '2022-09-28 09:17:46'),
(3, 2, 2, 1.00, 1, '2022-09-28 09:18:00', '2022-09-28 09:18:00'),
(4, 2, 1, 200.00, 1, '2022-09-28 09:18:13', '2022-09-28 09:18:13');

-- --------------------------------------------------------

--
-- Structure de la table `nbrdecisionteam_validations`
--

CREATE TABLE `nbrdecisionteam_validations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nbr` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` double DEFAULT NULL,
  `request_money` int(11) DEFAULT NULL,
  `rate` double DEFAULT NULL,
  `converted_amount` double DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `conversion_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `requests`
--

INSERT INTO `requests` (`id`, `title`, `type`, `description`, `total`, `request_money`, `rate`, `converted_amount`, `status`, `user_id`, `department_id`, `conversion_id`, `created_at`, `updated_at`) VALUES
(1, 'Test uploading', 'urgent', 'uploading testing', 8900000, 1, 1, 0, 'sent', 1, 1, 0, '2022-09-28 09:21:15', '2022-10-01 07:35:06'),
(2, 'Downloading', 'normal', 'Test Downloading', 100000, 2, 1, 0, 'sent', 1, 1, 0, '2022-09-28 09:49:43', '2022-09-28 09:49:43'),
(3, 'Hamburger', 'normal', 'Achat hamburgers', 67000000, 2, 1, 0, 'sent', 1, 1, 0, '2022-10-01 07:34:16', '2022-10-01 07:34:16'),
(4, 'Voyage Dubai', 'urgent', 'Histoire de vision', 50000, 1, 1, 0, 'sent', 1, 1, 0, '2022-10-01 07:36:18', '2022-10-01 07:36:18'),
(5, 'Validation request', 'normal', 'Test ajout', 0, 0, 1, 0, 'sent', 1, 1, 0, '2022-10-01 09:08:36', '2022-10-01 09:08:36'),
(6, 'Tournage Clip', 'urgent', 'Test Clip audio', 560000000, 2, 1, 0, 'sent', 1, 1, 0, '2022-10-01 09:24:51', '2022-10-01 09:24:51');

-- --------------------------------------------------------

--
-- Structure de la table `request_files`
--

CREATE TABLE `request_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `request_histories`
--

CREATE TABLE `request_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `amount` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `fund_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `request_references`
--

CREATE TABLE `request_references` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` int(11) NOT NULL,
  `reference_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `request_serveds`
--

CREATE TABLE `request_serveds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `amount` double NOT NULL,
  `served_by` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `money_id` bigint(20) UNSIGNED NOT NULL,
  `fund_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `self_references`
--

CREATE TABLE `self_references` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sub_departements`
--

CREATE TABLE `sub_departements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_child` int(11) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `systeminfos`
--

CREATE TABLE `systeminfos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `names` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_mail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `user_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint(20) DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `user_name`, `user_mail`, `email_verified_at`, `user_phone`, `user_password`, `user_type`, `status`, `department_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', NULL, '+243991657181909', '0000', 'admin', 'desabled', NULL, NULL, '2022-09-28 09:13:38', '2022-09-28 09:13:38'),
(2, 'caissier', 'caissier@gmail.com', NULL, '+2439915187161', '1111', 'ceo', 'desabled', NULL, NULL, '2022-09-28 09:14:09', '2022-09-28 09:14:09'),
(3, 'comptable', 'comptable@gmail.com', NULL, '+2438761567890', '2222', 'ceo', 'desabled', NULL, NULL, '2022-09-28 09:14:47', '2022-09-28 09:14:47');

-- --------------------------------------------------------

--
-- Structure de la table `validatedbydecisionteams`
--

CREATE TABLE `validatedbydecisionteams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `response` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `affectation_users`
--
ALTER TABLE `affectation_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affectation_users_user_id_foreign` (`user_id`),
  ADD KEY `affectation_users_department_id_foreign` (`department_id`);

--
-- Index pour la table `attemptactivationaccounts`
--
ALTER TABLE `attemptactivationaccounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attemptactivationaccounts_user_id_foreign` (`user_id`);

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_request_id_foreign` (`request_id`),
  ADD KEY `comments_user_id_foreign` (`user_id`);

--
-- Index pour la table `decision_chiefdepartments`
--
ALTER TABLE `decision_chiefdepartments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `decision_chiefdepartments_user_id_foreign` (`user_id`),
  ADD KEY `decision_chiefdepartments_request_id_foreign` (`request_id`);

--
-- Index pour la table `decision_decisionteams`
--
ALTER TABLE `decision_decisionteams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `decision_decisionteams_user_id_foreign` (`user_id`),
  ADD KEY `decision_decisionteams_request_id_foreign` (`request_id`);

--
-- Index pour la table `decision_teams`
--
ALTER TABLE `decision_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `decision_teams_user_id_foreign` (`user_id`);

--
-- Index pour la table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `details_requests`
--
ALTER TABLE `details_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `details_requests_request_id_foreign` (`request_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `funds`
--
ALTER TABLE `funds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funds_money_id_foreign` (`money_id`),
  ADD KEY `funds_user_id_foreign` (`user_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `moneys`
--
ALTER TABLE `moneys`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `money_conversions`
--
ALTER TABLE `money_conversions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `money_conversions_operator_foreign` (`operator`);

--
-- Index pour la table `nbrdecisionteam_validations`
--
ALTER TABLE `nbrdecisionteam_validations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requests_user_id_foreign` (`user_id`),
  ADD KEY `requests_department_id_foreign` (`department_id`);

--
-- Index pour la table `request_files`
--
ALTER TABLE `request_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_files_request_id_foreign` (`request_id`);

--
-- Index pour la table `request_histories`
--
ALTER TABLE `request_histories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `request_references`
--
ALTER TABLE `request_references`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_references_request_id_foreign` (`request_id`);

--
-- Index pour la table `request_serveds`
--
ALTER TABLE `request_serveds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_serveds_request_id_foreign` (`request_id`),
  ADD KEY `request_serveds_money_id_foreign` (`money_id`),
  ADD KEY `request_serveds_fund_id_foreign` (`fund_id`);

--
-- Index pour la table `self_references`
--
ALTER TABLE `self_references`
  ADD PRIMARY KEY (`id`),
  ADD KEY `self_references_request_id_foreign` (`request_id`);

--
-- Index pour la table `sub_departements`
--
ALTER TABLE `sub_departements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_departements_department_id_foreign` (`department_id`);

--
-- Index pour la table `systeminfos`
--
ALTER TABLE `systeminfos`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_user_name_unique` (`user_name`),
  ADD UNIQUE KEY `users_user_mail_unique` (`user_mail`),
  ADD UNIQUE KEY `users_user_phone_unique` (`user_phone`);

--
-- Index pour la table `validatedbydecisionteams`
--
ALTER TABLE `validatedbydecisionteams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `validatedbydecisionteams_request_id_foreign` (`request_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `affectation_users`
--
ALTER TABLE `affectation_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `attemptactivationaccounts`
--
ALTER TABLE `attemptactivationaccounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `decision_chiefdepartments`
--
ALTER TABLE `decision_chiefdepartments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `decision_decisionteams`
--
ALTER TABLE `decision_decisionteams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `decision_teams`
--
ALTER TABLE `decision_teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `details_requests`
--
ALTER TABLE `details_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `funds`
--
ALTER TABLE `funds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT pour la table `moneys`
--
ALTER TABLE `moneys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `money_conversions`
--
ALTER TABLE `money_conversions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `nbrdecisionteam_validations`
--
ALTER TABLE `nbrdecisionteam_validations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `request_files`
--
ALTER TABLE `request_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `request_histories`
--
ALTER TABLE `request_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `request_references`
--
ALTER TABLE `request_references`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `request_serveds`
--
ALTER TABLE `request_serveds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `self_references`
--
ALTER TABLE `self_references`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sub_departements`
--
ALTER TABLE `sub_departements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `systeminfos`
--
ALTER TABLE `systeminfos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `validatedbydecisionteams`
--
ALTER TABLE `validatedbydecisionteams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `affectation_users`
--
ALTER TABLE `affectation_users`
  ADD CONSTRAINT `affectation_users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affectation_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `attemptactivationaccounts`
--
ALTER TABLE `attemptactivationaccounts`
  ADD CONSTRAINT `attemptactivationaccounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `decision_chiefdepartments`
--
ALTER TABLE `decision_chiefdepartments`
  ADD CONSTRAINT `decision_chiefdepartments_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `decision_chiefdepartments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `decision_decisionteams`
--
ALTER TABLE `decision_decisionteams`
  ADD CONSTRAINT `decision_decisionteams_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `decision_decisionteams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `decision_teams`
--
ALTER TABLE `decision_teams`
  ADD CONSTRAINT `decision_teams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `details_requests`
--
ALTER TABLE `details_requests`
  ADD CONSTRAINT `details_requests_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `funds`
--
ALTER TABLE `funds`
  ADD CONSTRAINT `funds_money_id_foreign` FOREIGN KEY (`money_id`) REFERENCES `moneys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `funds_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `money_conversions`
--
ALTER TABLE `money_conversions`
  ADD CONSTRAINT `money_conversions_operator_foreign` FOREIGN KEY (`operator`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `request_files`
--
ALTER TABLE `request_files`
  ADD CONSTRAINT `request_files_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `request_references`
--
ALTER TABLE `request_references`
  ADD CONSTRAINT `request_references_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `request_serveds`
--
ALTER TABLE `request_serveds`
  ADD CONSTRAINT `request_serveds_fund_id_foreign` FOREIGN KEY (`fund_id`) REFERENCES `funds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_serveds_money_id_foreign` FOREIGN KEY (`money_id`) REFERENCES `moneys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_serveds_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `self_references`
--
ALTER TABLE `self_references`
  ADD CONSTRAINT `self_references_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sub_departements`
--
ALTER TABLE `sub_departements`
  ADD CONSTRAINT `sub_departements_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `validatedbydecisionteams`
--
ALTER TABLE `validatedbydecisionteams`
  ADD CONSTRAINT `validatedbydecisionteams_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
