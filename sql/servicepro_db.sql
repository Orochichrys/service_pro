-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 14 avr. 2026 à 19:42
-- Version du serveur : 8.0.45-0ubuntu0.24.04.1
-- Version de PHP : 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `servicepro_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `Categorie`
--

CREATE TABLE `Categorie` (
  `id_categorie` int NOT NULL,
  `nom_categorie` varchar(100) NOT NULL,
  `icone_categorie` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Categorie`
--

INSERT INTO `Categorie` (`id_categorie`, `nom_categorie`, `icone_categorie`) VALUES
(1, 'Maison & Travaux', 'bi-house'),
(2, 'Beauté & Mode', 'bi-scissors'),
(3, 'Événementiel', 'bi-calendar-event');

-- --------------------------------------------------------

--
-- Structure de la table `Cibler`
--

CREATE TABLE `Cibler` (
  `id_commande` int NOT NULL,
  `id_prestation` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `quantite` int DEFAULT '1',
  `note_evaluation` int DEFAULT NULL,
  `commentaire_evaluation` text
) ;

--
-- Déchargement des données de la table `Cibler`
--

INSERT INTO `Cibler` (`id_commande`, `id_prestation`, `prix_unitaire`, `quantite`, `note_evaluation`, `commentaire_evaluation`) VALUES
(1, 1, 10000.00, 1, 4, 'Très bon électricien. Il a installé trois nouvelles prises dans mon salon. Il connaît bien son travail et respecte les normes de sécurité. Un peu de retard sur l\'heure du rendez-vous, mais le résultat est impeccable.');

-- --------------------------------------------------------

--
-- Structure de la table `Commande`
--

CREATE TABLE `Commande` (
  `id_commande` int NOT NULL,
  `date_commande` datetime DEFAULT CURRENT_TIMESTAMP,
  `montant_total` decimal(10,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'En attente',
  `id_utilisateur` int NOT NULL,
  `id_quartier` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Commande`
--

INSERT INTO `Commande` (`id_commande`, `date_commande`, `montant_total`, `statut`, `id_utilisateur`, `id_quartier`) VALUES
(1, '2026-04-05 15:36:43', 10000.00, 'Terminée', 2, 3);

-- --------------------------------------------------------

--
-- Structure de la table `Departement`
--

CREATE TABLE `Departement` (
  `id_departement` int NOT NULL,
  `nom_departement` varchar(100) NOT NULL,
  `id_region` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Departement`
--

INSERT INTO `Departement` (`id_departement`, `nom_departement`, `id_region`) VALUES
(1, 'Abidjan', 1);

-- --------------------------------------------------------

--
-- Structure de la table `Prestation`
--

CREATE TABLE `Prestation` (
  `id_prestation` int NOT NULL,
  `titre_prestation` varchar(200) NOT NULL,
  `description_prestation` text,
  `prix_prestation` decimal(10,2) NOT NULL,
  `image_prestation` varchar(255) DEFAULT NULL,
  `datecrea_prestation` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_service` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `statut_prestation` varchar(20) DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Prestation`
--

INSERT INTO `Prestation` (`id_prestation`, `titre_prestation`, `description_prestation`, `prix_prestation`, `image_prestation`, `datecrea_prestation`, `id_service`, `id_utilisateur`, `statut_prestation`) VALUES
(1, 'Installation et Remplacement de Disjoncteurs / Prises', 'Mise aux normes de votre tableau électrique ou ajout de nouvelles prises dans vos pièces. Sécurité garantie. Je vérifie également la mise à la terre pour protéger vos appareils électroménagers contre les surtensions.', 10000.00, 'assets/img/uploads/service_1775312607_69d11edfb7c27.webp', '2026-04-04 14:13:51', 2, 3, 'validee'),
(2, 'Maquillage Professionnel pour Mariage & Cérémonie', 'Sublimez votre beauté pour vos événements spéciaux. J\'utilise des produits de haute qualité adaptés à tous les types de peaux. Inclus : faux-cils et fixation longue durée. Déplacement à domicile possible avec frais supplémentaires selon la zone.', 25000.00, 'assets/img/uploads/service_1775345393_69d19ef1d9a09.jpg', '2026-04-04 23:29:53', 8, 4, 'validee'),
(3, 'Réparation de fuites et Débouchage Sanitaire', 'Expert en dépannage urgent. Je répare vos fuites d\'eau (robinets, tuyauteries) et je débouche vos éviers ou WC. Travail rapide, disponible même le week-end. Matériel de qualité fourni si besoin.', 10000.00, 'assets/img/uploads/service_1776188308_69de7b94ec41b.webp', '2026-04-14 17:30:15', 1, 2, 'en_attente');

-- --------------------------------------------------------

--
-- Structure de la table `Quartier`
--

CREATE TABLE `Quartier` (
  `id_quartier` int NOT NULL,
  `nom_quartier` varchar(100) NOT NULL,
  `id_ville` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Quartier`
--

INSERT INTO `Quartier` (`id_quartier`, `nom_quartier`, `id_ville`) VALUES
(1, 'Angré', 1),
(2, 'Riviera', 1),
(3, 'Selmer', 1);

-- --------------------------------------------------------

--
-- Structure de la table `Region`
--

CREATE TABLE `Region` (
  `id_region` int NOT NULL,
  `nom_region` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Region`
--

INSERT INTO `Region` (`id_region`, `nom_region`) VALUES
(1, 'Lagunes');

-- --------------------------------------------------------

--
-- Structure de la table `Service`
--

CREATE TABLE `Service` (
  `id_service` int NOT NULL,
  `nom_service` varchar(100) NOT NULL,
  `id_categorie` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Service`
--

INSERT INTO `Service` (`id_service`, `nom_service`, `id_categorie`) VALUES
(1, 'Plomberie', 1),
(2, 'Électricité', 1),
(3, 'Coiffure', 2),
(4, 'Décoration de salle', 3),
(5, 'Location de bâches et chaises', 3),
(6, 'Service Traiteur', 3),
(7, 'Photographie & Vidéo', 3),
(8, 'Maquillage', 2);

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `id_utilisateur` int NOT NULL,
  `nom_utilisateur` varchar(100) NOT NULL,
  `prenom_utilisateur` varchar(150) NOT NULL,
  `email_utilisateur` varchar(150) NOT NULL,
  `password_utilisateur` varchar(255) NOT NULL,
  `tel_utilisateur` varchar(20) DEFAULT NULL,
  `est_client` tinyint(1) DEFAULT '1',
  `est_prestataire` tinyint(1) DEFAULT '0',
  `est_admin` tinyint(1) DEFAULT '0',
  `is_validated` tinyint(1) DEFAULT '0',
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_quartier` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Utilisateur`
--

INSERT INTO `Utilisateur` (`id_utilisateur`, `nom_utilisateur`, `prenom_utilisateur`, `email_utilisateur`, `password_utilisateur`, `tel_utilisateur`, `est_client`, `est_prestataire`, `est_admin`, `is_validated`, `date_inscription`, `id_quartier`) VALUES
(1, 'Admin', 'Principal', 'admin@servicepro.ci', '$2y$10$CIcKfUm.norpTeoZT0JOeOy/Qkbi6Z963vsoXffCKy3Qq9BM3ujq2', NULL, 1, 0, 1, 0, '2026-03-31 23:13:29', NULL),
(2, 'Koffi', 'Jean-Luc', 'jlkoffi@outlook.com', '$2y$10$1/DIxcu7/7Sgg06ktVs6YOd9kcdL7TsxOzEWC/CJExSSvdhJfHWba', '0102030405', 1, 1, 0, 1, '2026-04-02 23:25:20', 3),
(3, 'Bakayoko', 'Marc', 'marc.b@gmail.com', '$2y$10$Bi6N2PFTBhGwSUSVh/Js6uAsWIcwU7Bwqh/EsnSmjXqKpEcZ1n/tO', '01909245', 0, 1, 0, 1, '2026-04-02 23:29:34', 2),
(4, 'Awa', 'Coulibaly', 'awa.design@yahoo.fr', '$2y$10$oTYw7NhMMXKNRUiEaOJdde00I4j4tNWr4J/48y5ewfzVeSx4N.Gy6', NULL, 0, 1, 0, 1, '2026-04-04 15:05:14', 1);

-- --------------------------------------------------------

--
-- Structure de la table `Ville`
--

CREATE TABLE `Ville` (
  `id_ville` int NOT NULL,
  `nom_ville` varchar(100) NOT NULL,
  `id_departement` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Ville`
--

INSERT INTO `Ville` (`id_ville`, `nom_ville`, `id_departement`) VALUES
(1, 'Abidjan', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Categorie`
--
ALTER TABLE `Categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `Cibler`
--
ALTER TABLE `Cibler`
  ADD PRIMARY KEY (`id_commande`,`id_prestation`),
  ADD KEY `id_prestation` (`id_prestation`);

--
-- Index pour la table `Commande`
--
ALTER TABLE `Commande`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_quartier` (`id_quartier`);

--
-- Index pour la table `Departement`
--
ALTER TABLE `Departement`
  ADD PRIMARY KEY (`id_departement`),
  ADD KEY `id_region` (`id_region`);

--
-- Index pour la table `Prestation`
--
ALTER TABLE `Prestation`
  ADD PRIMARY KEY (`id_prestation`),
  ADD KEY `id_service` (`id_service`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `Quartier`
--
ALTER TABLE `Quartier`
  ADD PRIMARY KEY (`id_quartier`),
  ADD KEY `id_ville` (`id_ville`);

--
-- Index pour la table `Region`
--
ALTER TABLE `Region`
  ADD PRIMARY KEY (`id_region`);

--
-- Index pour la table `Service`
--
ALTER TABLE `Service`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Index pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email_utilisateur` (`email_utilisateur`),
  ADD KEY `id_quartier` (`id_quartier`);

--
-- Index pour la table `Ville`
--
ALTER TABLE `Ville`
  ADD PRIMARY KEY (`id_ville`),
  ADD KEY `id_departement` (`id_departement`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Categorie`
--
ALTER TABLE `Categorie`
  MODIFY `id_categorie` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Commande`
--
ALTER TABLE `Commande`
  MODIFY `id_commande` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Departement`
--
ALTER TABLE `Departement`
  MODIFY `id_departement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Prestation`
--
ALTER TABLE `Prestation`
  MODIFY `id_prestation` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Quartier`
--
ALTER TABLE `Quartier`
  MODIFY `id_quartier` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Region`
--
ALTER TABLE `Region`
  MODIFY `id_region` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Service`
--
ALTER TABLE `Service`
  MODIFY `id_service` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  MODIFY `id_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `Ville`
--
ALTER TABLE `Ville`
  MODIFY `id_ville` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Cibler`
--
ALTER TABLE `Cibler`
  ADD CONSTRAINT `Cibler_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `Commande` (`id_commande`) ON DELETE CASCADE,
  ADD CONSTRAINT `Cibler_ibfk_2` FOREIGN KEY (`id_prestation`) REFERENCES `Prestation` (`id_prestation`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Commande`
--
ALTER TABLE `Commande`
  ADD CONSTRAINT `Commande_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `Commande_ibfk_2` FOREIGN KEY (`id_quartier`) REFERENCES `Quartier` (`id_quartier`);

--
-- Contraintes pour la table `Departement`
--
ALTER TABLE `Departement`
  ADD CONSTRAINT `Departement_ibfk_1` FOREIGN KEY (`id_region`) REFERENCES `Region` (`id_region`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Prestation`
--
ALTER TABLE `Prestation`
  ADD CONSTRAINT `Prestation_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `Service` (`id_service`) ON DELETE CASCADE,
  ADD CONSTRAINT `Prestation_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Quartier`
--
ALTER TABLE `Quartier`
  ADD CONSTRAINT `Quartier_ibfk_1` FOREIGN KEY (`id_ville`) REFERENCES `Ville` (`id_ville`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Service`
--
ALTER TABLE `Service`
  ADD CONSTRAINT `Service_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `Categorie` (`id_categorie`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD CONSTRAINT `Utilisateur_ibfk_1` FOREIGN KEY (`id_quartier`) REFERENCES `Quartier` (`id_quartier`) ON DELETE SET NULL;

--
-- Contraintes pour la table `Ville`
--
ALTER TABLE `Ville`
  ADD CONSTRAINT `Ville_ibfk_1` FOREIGN KEY (`id_departement`) REFERENCES `Departement` (`id_departement`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
