CREATE DATABASE IF NOT EXISTS gestion_besoins
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_besoins;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('demandeur','validateur','admin') NOT NULL DEFAULT 'demandeur',
    chef_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chef_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE types_besoins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL
);

INSERT INTO types_besoins (libelle) VALUES
('Matériel'), ('Logiciel'), ('Service'), ('Formation'), ('Autre');


CREATE TABLE demandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demandeur_id INT NOT NULL,
    type_id INT NOT NULL,
    description TEXT NOT NULL,
    urgence ENUM('faible','moyenne','urgente') NOT NULL,
    statut ENUM('en_attente','en_cours_validation','validee','rejettee','traitee')
        NOT NULL DEFAULT 'en_attente',
    validateur_id INT NULL,
    admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (demandeur_id) REFERENCES users(id),
    FOREIGN KEY (type_id) REFERENCES types_besoins(id),
    FOREIGN KEY (validateur_id) REFERENCES users(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE validation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demande_id INT NOT NULL,
    validateur_id INT NOT NULL,
    action ENUM('valide','rejete') NOT NULL,
    commentaire TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (demande_id) REFERENCES demandes(id),
    FOREIGN KEY (validateur_id) REFERENCES users(id)
);

CREATE TABLE pieces_jointes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demande_id INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (demande_id) REFERENCES demandes(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

--(mot de passe: admin123)
INSERT INTO users (nom,email,password,role)
VALUES (
  'Admin',
  'admin@example.com',
    '$2y$10$CSRCoXVeHp7yFusX3HcK/OYdAw.Aub.oVHN1OjL5r6kvjXCImQaOC',
  'admin'
);
