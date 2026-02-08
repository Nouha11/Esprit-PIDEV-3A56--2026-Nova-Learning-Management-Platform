-- SQL pour créer la table book_library_inventory
-- Exécuter ce SQL directement dans phpMyAdmin ou MySQL

-- Créer la table book_library_inventory si elle n'existe pas déjà
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
