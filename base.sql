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

CREATE TABLE configurations_commission(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_operateur INTEGER NOT NULL,
    pourcentage_commission DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (id_operateur) REFERENCES operateurs(id)
)

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

-- CREATE TABLE solde_clients(
--     id INTEGER PRIMARY KEY AUTOINCREMENT,
--     id_client INTEGER NOT NULL,
--     solde DECIMAL(10, 2) DEFAULT 0.00,
--     date_creation DATETIME NOT NULL,
--     FOREIGN KEY (id_client) REFERENCES clients(id)
-- );

---- INIT DATA v1 ----
INSERT INTO type_operations (libelle) VALUES ('Depot');
INSERT INTO type_operations (libelle) VALUES ('Retrait');
-- INSERT INTO type_operations (libelle) VALUES ('Transfert');

-- CREATE VIEW "v_get_solde_client" AS
-- WITH mouvements_avec_frais AS (
--     SELECT
--         om.*,
--         COALESCE((
--             SELECT ct.montant_frais
--             FROM configurations_transaction ct
--             WHERE ct.id_type_operation = om.id_type_operation
--               AND om.montant BETWEEN ct.borne_min AND ct.borne_max
--             ORDER BY ct.id ASC
--             LIMIT 1
--         ), 0) AS montant_frais
--     FROM operation_mouvement om
-- )
-- SELECT
--     c.id,
--     c.nom_client,
--     COALESCE(SUM(
--         CASE 
--             WHEN om.id_type_operation = 1 THEN om.montant - om.montant_frais
--             WHEN om.id_type_operation = 2 THEN -(om.montant + om.montant_frais)
--             WHEN om.id_type_operation = 3 AND om.id_beneficiaire = c.id THEN om.montant
--             WHEN om.id_type_operation = 3 AND om.id_emetteur = c.id THEN -(om.montant + om.montant_frais)
--         END
--     ), 0) AS SOLDE
-- FROM clients c
-- LEFT JOIN mouvements_avec_frais om
--     ON c.id = om.id_emetteur OR c.id = om.id_beneficiaire
-- GROUP BY c.id, c.nom_client;


-- Nouvelle view efa refacto
CREATE VIEW v_get_solde_client AS
WITH mouvements_avec_frais AS (
    SELECT
        om.*,
        COALESCE((
            SELECT ct.montant_frais
            FROM configurations_transaction ct
            WHERE ct.id_type_operation = om.id_type_operation
            AND om.montant BETWEEN ct.borne_min AND ct.borne_max
            ORDER BY ct.id ASC
            LIMIT 1
        ), 0) AS montant_frais
    FROM operation_mouvement om
)
SELECT
    c.id,
    c.nom_client,
    COALESCE(SUM(
        CASE 
            WHEN om.id_type_operation = 1 THEN om.montant - om.montant_frais
            WHEN om.id_type_operation = 2 THEN -(om.montant + om.montant_frais)
            WHEN om.id_type_operation = 3 AND om.id_beneficiaire = c.id THEN om.montant
            WHEN om.id_type_operation = 3 AND om.id_emetteur = c.id THEN -(om.montant + om.montant_frais)
        END
    ), 0) AS SOLDE
FROM clients c
LEFT JOIN mouvements_avec_frais om
    ON c.id = om.id_emetteur OR c.id = om.id_beneficiaire
WHERE substr(c.numero, 1, 3) = (SELECT prefixe FROM operateurs WHERE nom_operateur = 'Orange Money')
GROUP BY c.id, c.nom_client;


CREATE VIEW "v_get_historique_client" AS
SELECT
    om.id AS operation_id,
    om.id_emetteur,
    c1.nom_client AS emetteur,
    om.id_beneficiaire,
    c2.nom_client AS beneficiaire,
    o.libelle AS type_operation,
    om.montant,
    om.date_operation,
    CASE
        WHEN o.libelle = 'Depot' THEN 'Recu'
        WHEN o.libelle = 'Retrait' THEN 'Envoye'
        WHEN o.libelle = 'Transfert' AND c1.id = om.id_emetteur THEN 'Envoye'
        WHEN o.libelle = 'Transfert' AND c1.id = om.id_beneficiaire THEN 'Recu'
    END AS sens
FROM
    operation_mouvement om
JOIN clients c1 ON om.id_emetteur = c1.id
LEFT JOIN clients c2 ON om.id_beneficiaire = c2.id
JOIN type_operations o ON om.id_type_operation = o.id;

-- CREATE VIEW v_gains_frais AS
-- SELECT
--     om.id AS id_operation,
--     om.date_operation,
--     om.id_type_operation,
--     t.libelle AS type_operation,
--     om.montant,
--     ct.montant_frais
-- FROM operation_mouvement om
-- JOIN type_operations t 
--     ON t.id = om.id_type_operation
-- LEFT JOIN configurations_transaction ct
--     ON ct.id_type_operation = om.id_type_operation
--     AND om.montant BETWEEN ct.borne_min AND ct.borne_max
-- ORDER BY om.date_operation;


-- Nouvelle view gains_frais efa refacto

CREATE VIEW v_gains_frais AS
SELECT
    c.nom_client as nom_client,
    om.id AS id_operation,
    om.date_operation,
    om.id_type_operation,
    t.libelle AS type_operation,
    om.montant,
    ct.montant_frais
FROM operation_mouvement om
JOIN type_operations t 
    ON t.id = om.id_type_operation
JOIN clients c
    ON c.id = om.id_emetteur
LEFT JOIN configurations_transaction ct
    ON ct.id_type_operation = om.id_type_operation
    AND om.montant BETWEEN ct.borne_min AND ct.borne_max
WHERE substr(c.numero, 1, 3) = (SELECT prefixe FROM operateurs WHERE nom_operateur = 'Orange Money')
ORDER BY om.date_operation;

CREATE VIEW v_total_gains_frais AS
SELECT
    COALESCE(SUM(montant_frais), 0) AS total_gains
FROM v_gains_frais;



-- En cas de delete aza adino manao anity commande ity sinon tsy mifanaraka le id
DELETE FROM sqlite_sequence WHERE name IN ('clients', 'operateurs', 'type_operations', 'operation_mouvement');
