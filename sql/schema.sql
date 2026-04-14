CREATE DATABASE IF NOT EXISTS servicepro_db CHARACTER SET utf8mb4;
USE servicepro_db;

-- GÉOGRAPHIE
CREATE TABLE Region (
    id_region INT AUTO_INCREMENT PRIMARY KEY,
    nom_region VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Departement (
    id_departement INT AUTO_INCREMENT PRIMARY KEY,
    nom_departement VARCHAR(100) NOT NULL,
    id_region INT NOT NULL,
    FOREIGN KEY (id_region) REFERENCES Region(id_region) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Ville (
    id_ville INT AUTO_INCREMENT PRIMARY KEY,
    nom_ville VARCHAR(100) NOT NULL,
    id_departement INT NOT NULL,
    FOREIGN KEY (id_departement) REFERENCES Departement(id_departement) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Quartier (
    id_quartier INT AUTO_INCREMENT PRIMARY KEY,
    nom_quartier VARCHAR(100) NOT NULL,
    id_ville INT NOT NULL,
    FOREIGN KEY (id_ville) REFERENCES Ville(id_ville) ON DELETE CASCADE
) ENGINE=InnoDB;

-- UTILISATEURS
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(100) NOT NULL,
    prenom_utilisateur VARCHAR(150) NOT NULL,
    email_utilisateur VARCHAR(150) UNIQUE NOT NULL,
    password_utilisateur VARCHAR(255) NOT NULL,
    tel_utilisateur VARCHAR(20),
    est_client BOOLEAN DEFAULT TRUE,
    est_prestataire BOOLEAN DEFAULT FALSE,
    est_admin BOOLEAN DEFAULT FALSE,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_quartier INT,
    FOREIGN KEY (id_quartier) REFERENCES Quartier(id_quartier) ON DELETE SET NULL
) ENGINE=InnoDB;

-- CATALOGUE
CREATE TABLE Categorie (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL,
    icone_categorie VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE Service (
    id_service INT AUTO_INCREMENT PRIMARY KEY,
    nom_service VARCHAR(100) NOT NULL,
    id_categorie INT NOT NULL,
    FOREIGN KEY (id_categorie) REFERENCES Categorie(id_categorie) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PRESTATIONS
CREATE TABLE Prestation (
    id_prestation INT AUTO_INCREMENT PRIMARY KEY,
    titre_prestation VARCHAR(200) NOT NULL,
    description_prestation TEXT,
    prix_prestation DECIMAL(10, 2) NOT NULL,
    image_prestation VARCHAR(255),
    datecrea_prestation DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_service INT NOT NULL,
    id_utilisateur INT NOT NULL,
    FOREIGN KEY (id_service) REFERENCES Service(id_service) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- COMMANDES ET AVIS
CREATE TABLE Commande (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10, 2) NOT NULL,
    statut VARCHAR(50) DEFAULT 'En attente',
    id_utilisateur INT NOT NULL,
    id_quartier INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_quartier) REFERENCES Quartier(id_quartier)
) ENGINE=InnoDB;

CREATE TABLE Cibler (
    id_commande INT,
    id_prestation INT,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    quantite INT DEFAULT 1,
    note_evaluation INT CHECK (note_evaluation BETWEEN 1 AND 5),
    commentaire_evaluation TEXT,
    PRIMARY KEY (id_commande, id_prestation),
    FOREIGN KEY (id_commande) REFERENCES Commande(id_commande) ON DELETE CASCADE,
    FOREIGN KEY (id_prestation) REFERENCES Prestation(id_prestation) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DONNÉES DE TEST
INSERT INTO Region (nom_region) VALUES ('Lagunes');
INSERT INTO Departement (nom_departement) VALUES ('Abidjan');
INSERT INTO Ville (nom_ville) VALUES ('Abidjan');
INSERT INTO Quartier (nom_quartier) VALUES ('Angré'), ('Riviera'), ('Selmer');

INSERT INTO Categorie (nom_categorie, icone_categorie) VALUES 
('Maison & Travaux', 'bi-house'), ('Beauté & Mode', 'bi-scissors');

INSERT INTO Service (id_categorie, nom_service) VALUES 
('Plomberie'), ('Électricité'), ('Coiffure');