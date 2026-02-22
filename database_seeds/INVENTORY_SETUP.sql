-- ============================================
-- BOOK INVENTORY SYSTEM - COMPLETE SETUP SQL
-- ============================================
-- Exécuter ce fichier directement dans phpMyAdmin
-- ou via: mysql -u root -p nom_de_la_base < INVENTORY_SETUP.sql

-- 1. Créer la table book_library_inventory
CREATE TABLE IF NOT EXISTS book_library_inventory (
    id INT AUTO_INCREMENT NOT NULL,
    book_id INT NOT NULL,
    library_id INT NOT NULL,
    total_copies INT NOT NULL DEFAULT 0,
    available_copies INT NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    INDEX IDX_BOOK_INV_BOOK (book_id),
    INDEX IDX_BOOK_INV_LIBRARY (library_id),
    UNIQUE KEY unique_book_library (book_id, library_id),
    CONSTRAINT FK_BOOK_INV_BOOK FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE CASCADE,
    CONSTRAINT FK_BOOK_INV_LIBRARY FOREIGN KEY (library_id) REFERENCES libraries (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

-- 2. (Optionnel) Insérer des données de test
-- Décommenter les lignes ci-dessous si vous voulez des données de test

/*
-- Book 1 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(1, 1, 5, 3),  -- National Library of Tunisia: 5 total, 3 available
(1, 2, 3, 2),  -- Carthage Municipal Library: 3 total, 2 available
(1, 5, 4, 4);  -- Bizerte Public Library: 4 total, 4 available

-- Book 2 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(2, 1, 10, 7),  -- National Library of Tunisia: 10 total, 7 available
(2, 3, 5, 0),   -- Sfax Regional Library: 5 total, 0 available
(2, 4, 3, 1);   -- Sousse Cultural Center Library: 3 total, 1 available

-- Book 3 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(3, 2, 2, 2),   -- Carthage Municipal Library: 2 total, 2 available
(3, 6, 4, 3),   -- Kairouan Heritage Library: 4 total, 3 available
(3, 7, 6, 5);   -- Monastir University Library: 6 total, 5 available

-- Book 9 in multiple libraries (for testing)
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(9, 2, 3, 1),   -- Carthage Municipal Library: 3 total, 1 available
(9, 4, 4, 3),   -- Sousse Cultural Center Library: 4 total, 3 available
(9, 6, 2, 2);   -- Kairouan Heritage Library: 2 total, 2 available
*/

-- Vérifier que la table a été créée
SELECT 'Table book_library_inventory created successfully!' AS status;
