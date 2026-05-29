-- ============================================================
--  RoomBook — Base de données
--  Système de gestion des réservations de salles
-- ============================================================

DROP DATABASE IF EXISTS roombook;
CREATE DATABASE roombook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE roombook;

-- ------------------------------------------------------------
-- Table : utilisateurs
-- role        : admin | client
-- type_client : prof  | etudiant | autre   (rempli si role=client)
-- ------------------------------------------------------------
CREATE TABLE utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM('admin','client') NOT NULL DEFAULT 'client',
    type_client     ENUM('prof','etudiant','autre') DEFAULT 'autre',
    telephone       VARCHAR(30)  DEFAULT NULL,
    departement     VARCHAR(100) DEFAULT NULL,
    bio             TEXT         DEFAULT NULL,
    cree_le         DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Table : salles
-- ------------------------------------------------------------
CREATE TABLE salles (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    capacite        INT NOT NULL,
    localisation    VARCHAR(100) DEFAULT NULL,
    type_salle      ENUM('cours','tp','reunion','amphi') DEFAULT 'cours',
    equipements     VARCHAR(255) DEFAULT NULL
);

-- ------------------------------------------------------------
-- Table : reservations
-- ------------------------------------------------------------
CREATE TABLE reservations (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT NULL,
    salle_id            INT NOT NULL,
    date_reservation    DATE NOT NULL,
    heure_debut         TIME NOT NULL,
    heure_fin           TIME NOT NULL,
    responsable         VARCHAR(100) NOT NULL,
    motif               TEXT,
    statut              ENUM('en_attente','confirmee','refusee','annulee') DEFAULT 'en_attente',
    cree_le             DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (salle_id)       REFERENCES salles(id)       ON DELETE CASCADE
);

-- ============================================================
--  Données initiales
-- ============================================================

-- Salles
INSERT INTO salles (nom, capacite, localisation, type_salle, equipements) VALUES
('Salle A',            30,  'Bloc A',          'cours',   'Tableau, Projecteur'),
('Salle B',            40,  'Bloc B',          'cours',   'Tableau, Projecteur, Climatisation'),
('Salle Informatique', 25,  'Bloc Info',       'tp',      '25 PC, Projecteur, Imprimante'),
('Amphi 1',           120,  'Bloc Principal',  'amphi',   'Micro, Projecteur, Estrade'),
('Salle C',            35,  'Bloc C',          'cours',   'Tableau'),
('Salle de Réunion',   12,  'Administration',  'reunion', 'Table ronde, Écran TV');

-- Utilisateurs (mot de passe pour tous : "password")
-- Hash bcrypt vérifié de "password"
INSERT INTO utilisateurs (nom, email, mot_de_passe, role, type_client, departement) VALUES
('Administrateur',  'admin@roombook.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin',  'autre',    'Direction'),
('Karim Benali',    'prof@roombook.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'client', 'prof',     'Informatique'),
('Sara El Amrani',  'etudiant@roombook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'client', 'etudiant', 'Informatique');

-- Quelques réservations d'exemple
INSERT INTO reservations
(utilisateur_id, salle_id, date_reservation, heure_debut, heure_fin, responsable, motif, statut) VALUES
(2, 1, CURDATE() + INTERVAL 1 DAY, '08:00:00', '10:00:00', 'Karim Benali',   'Cours Algorithmique',   'confirmee'),
(3, 3, CURDATE() + INTERVAL 2 DAY, '14:00:00', '16:00:00', 'Sara El Amrani', 'TP Base de données',    'en_attente'),
(2, 4, CURDATE() + INTERVAL 3 DAY, '10:00:00', '12:00:00', 'Karim Benali',   'Conférence IA',         'confirmee');
