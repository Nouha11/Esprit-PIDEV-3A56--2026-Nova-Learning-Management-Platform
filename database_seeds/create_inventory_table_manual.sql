-- ============================================
-- MANUAL TABLE CREATION - Run this FIRST before creating the entity
-- ============================================
-- This creates the table manually so Doctrine won't try to generate migrations for it

-- Drop table if exists (for clean install)
DROP TABLE IF EXISTS book_library_inventory;

-- Create the book_library_inventory table
CREATE TABLE book_library_inventory (
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

-- Insert sample data (optional)
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(1, 1, 5, 3),
(1, 2, 3, 2),
(2, 1, 10, 7),
(2, 3, 5, 0);

SELECT 'Table book_library_inventory created successfully!' AS status;
