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

1 = depot
2 = retrait
3 = transfert
CREATE TABLE clients(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_client VARCHAR(255) NOT NULL,
    numero VARCHAR(255) NOT NULL
);

CREATE TABLE configurations_transaction(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    borne_min DECIMAL(10, 2) DEFAULT 0.00,
    borne_max DECIMAL(10, 2) DEFAULT 0.00,
    montant_frais DECIMAL(10, 2) DEFAULT 0.00,
    id_type_operation INTEGER NOT NULL,
    FOREIGN KEY (id_type_operation) REFERENCES type_operations(id)
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


---- INIT DATA v1 ----
INSERT INTO clients (nom_client, numero) VALUES ('Client A', '0380000000');
INSERT INTO clients (nom_client, numero) VALUES ('Client B', '0370000000');

---- View ----
-- V1
CREATE VIEW "v_get_solde_client" AS
SELECT
    c.id,
    c.nom_client,
    COALESCE(SUM(
        CASE 
            WHEN om.id_type_operation = 1 THEN om.montant
            WHEN om.id_type_operation = 2 THEN -om.montant
            WHEN om.id_type_operation = 3 AND om.id_beneficiaire = c.id THEN om.montant
            WHEN om.id_type_operation = 3 AND om.id_emetteur = c.id THEN -om.montant
        END
    ), 0) AS SOLDE
FROM clients c
LEFT JOIN operation_mouvement om 
    ON c.id = om.id_emetteur OR c.id = om.id_beneficiaire
GROUP BY c.id, c.nom_client;

CREATE VIEW v_gains_frais AS
SELECT
    om.id AS id_operation,
    om.date_operation,
    om.id_type_operation,
    t.libelle AS type_operation,
    om.montant,
    ct.montant_frais
FROM operation_mouvement om
JOIN type_operations t 
    ON t.id = om.id_type_operation
LEFT JOIN configurations_transaction ct
    ON ct.id_type_operation = om.id_type_operation
    AND om.montant BETWEEN ct.borne_min AND ct.borne_max
ORDER BY om.date_operation;

CREATE VIEW v_total_gains_frais AS
SELECT
    COALESCE(SUM(montant_frais), 0) AS total_gains
FROM v_gains_frais;



-- En cas de delete aza adino manao anity commande ity sinon tsy mifanaraka le id
DELETE FROM sqlite_sequence WHERE name IN ('clients', 'operateurs', 'type_operations', 'operation_mouvement');


