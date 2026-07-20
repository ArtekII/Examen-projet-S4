
-- DELETE FROM operation_mouvement;
-- DELETE FROM clients;
-- DELETE FROM operateurs;
-- DELETE FROM type_operations;

-- -- -- Réinitialise le compteur auto-increment pour ces tables
-- DELETE FROM sqlite_sequence WHERE name IN ('clients', 'operateurs', 'type_operations', 'operation_mouvement');


-- ============================================
-- OPERATEURS
-- ============================================

INSERT INTO operateurs (nom_operateur, prefixe)
VALUES ('Orange Money', '032');

INSERT INTO operateurs (nom_operateur, prefixe)
VALUES ('MVola', '034');

INSERT INTO operateurs (nom_operateur, prefixe)
VALUES ('Airtel Money', '033');


-- ============================================
-- TYPES OPERATIONS
-- ============================================

INSERT INTO type_operations (id, libelle)
VALUES (1, 'Depot');

INSERT INTO type_operations (id, libelle)
VALUES (2, 'Retrait');

INSERT INTO type_operations (id, libelle)
VALUES (3, 'Transfert');


-- ============================================
-- CLIENTS
-- ============================================

INSERT INTO clients (nom_client, numero)
VALUES ('Client A', '0340000000');

INSERT INTO clients (nom_client, numero)
VALUES ('Client B', '0330000000');

INSERT INTO clients (nom_client, numero)
VALUES ('Client C', '0321111111');

INSERT INTO clients (nom_client, numero)
VALUES ('Client D', '0342222222');


-- ============================================
-- DEPOTS
-- ============================================

-- Client A +100000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(1,1,1,1,100000,'2026-07-20 08:00:00');


-- Client B +50000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(2,2,2,1,50000,'2026-07-20 08:10:00');


-- Client C +70000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(3,3,3,1,70000,'2026-07-20 08:20:00');


-- ============================================
-- RETRAITS
-- ============================================

-- Client A -20000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(1,1,1,2,20000,'2026-07-20 09:00:00');


-- Client C -10000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(3,3,3,2,10000,'2026-07-20 09:15:00');


-- ============================================
-- TRANSFERTS
-- ============================================

-- Client A -> Client B : 15000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(1,2,1,3,15000,'2026-07-20 10:00:00');


-- Client B -> Client C : 10000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(2,3,2,3,10000,'2026-07-20 10:30:00');


-- Client C -> Client A : 5000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(3,1,3,3,5000,'2026-07-20 11:00:00');


-- Client A -> Client D : 25000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(1,4,1,3,25000,'2026-07-20 11:30:00');


-- Client D -> Client B : 5000
INSERT INTO operation_mouvement
(id_emetteur,id_beneficiaire,id_operateur,id_type_operation,montant,date_operation)
VALUES
(4,2,2,3,5000,'2026-07-20 12:00:00');




-- -- ============================================
-- -- CONFIGURATIONS RETRAIT (id_type_operation = 2)
-- -- ============================================

-- Retrait de 0 à 100 000 : frais 500
INSERT INTO configurations_transaction
(borne_min, borne_max, montant_frais, id_type_operation)
VALUES
(0.00, 100000.00, 500.00, 2);


-- Retrait de 100 001 à 500 000 : frais 1000
INSERT INTO configurations_transaction
(borne_min, borne_max, montant_frais, id_type_operation)
VALUES
(100001.00, 500000.00, 1000.00, 2);


-- Retrait de 500 001 à 1 000 000 : frais 2000
INSERT INTO configurations_transaction
(borne_min, borne_max, montant_frais, id_type_operation)
VALUES
(500001.00, 1000000.00, 2000.00, 2);



-- ============================================
-- CONFIGURATIONS TRANSFERT (id_type_operation = 3)
-- ============================================

-- Transfert de 0 à 100 000 : frais 1000
INSERT INTO configurations_transaction
(borne_min, borne_max, montant_frais, id_type_operation)
VALUES
(0.00, 100000.00, 1000.00, 3);


-- Transfert de 100 001 à 500 000 : frais 2500
INSERT INTO configurations_transaction
(borne_min, borne_max, montant_frais, id_type_operation)
VALUES
(100001.00, 500000.00, 2500.00, 3);


-- Transfert de 500 001 à 1 000 000 : frais 5000
INSERT INTO configurations_transaction
(borne_min, borne_max, montant_frais, id_type_operation)
VALUES
(500001.00, 1000000.00, 5000.00, 3);