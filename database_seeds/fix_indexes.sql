-- Fix index names to match what Doctrine expects
-- This prevents RENAME INDEX errors in future migrations

-- Fix book_library table indexes
ALTER TABLE book_library DROP INDEX IF EXISTS fk_book_library_library;
ALTER TABLE book_library DROP INDEX IF EXISTS fk_book_library_book;
ALTER TABLE book_library ADD INDEX IF NOT EXISTS IDX_32A0B02AFE2541D7 (library_id);
ALTER TABLE book_library ADD INDEX IF NOT EXISTS IDX_32A0B02A16A2B381 (book_id);

-- Drop book_library_inventory table (not being used)
DROP TABLE IF EXISTS book_library_inventory;

SELECT 'Indexes fixed! Run migrations now.' AS status;
