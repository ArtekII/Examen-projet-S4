---- INIT TABLE v1 ----
CREATE TABLE operateurs(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_operateur VARCHAR(255) NOT NULL,
    prefixe VARCHAR(255) NOT NULL
);

CREATE TABLE type_operations(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle VARCHAR(255) NOT NULL
);

CREATE TABLE clients(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_client VARCHAR(255) NOT NULL,
    numero VARCHAR(255) NOT NULL
);

CREATE TABLE configurations_transaction(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    borne_min DECIMAL(10, 2) DEFAULT 0.00,
    borne_max DECIMAL(10, 2) DEFAULT 0.00,
    montant_frais DECIMAL(10, 2) DEFAULT 0.00
);

CREATE TABLE operation_mouvement(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_emetteur INTEGER NOT NULL,
    id_beneficiaire INTEGER NOT NULL,
    id_operateur INTEGER NOT NULL,
    id_type_operation INTEGER NOT NULL,
    montant DECIMAL(10, 2) DEFAULT 0.00,
    date_operation DATETIME NOT NULL,
    FOREIGN KEY (id_emetteur) REFERENCES clients(id),
    FOREIGN KEY (id_beneficiaire) REFERENCES clients(id),
    FOREIGN KEY (id_operateur) REFERENCES operateurs(id),
    FOREIGN KEY (id_type_operation) REFERENCES type_operations(id)
);

CREATE TABLE solde_clients(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_client INTEGER NOT NULL,
    solde DECIMAL(10, 2) DEFAULT 0.00,
    date_creation DATETIME NOT NULL,
    FOREIGN KEY (id_client) REFERENCES clients(id)
);

---- INIT DATA v1 ----
INSERT INTO clients (nom_client, numero) VALUES ('Client A', '0380000000');
INSERT INTO clients (nom_client, numero) VALUES ('Client B', '0370000000');