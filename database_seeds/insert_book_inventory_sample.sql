-- Sample inventory data for books in libraries
-- This adds inventory information for existing books

-- Assuming book IDs 1-10 exist and library IDs 1-10 exist
-- Adjust the IDs based on your actual data

-- Book 1 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(1, 1, 5, 3),  -- National Library of Tunisia: 5 total, 3 available
(1, 2, 3, 2),  -- Carthage Municipal Library: 3 total, 2 available
(1, 5, 4, 4);  -- Bizerte Public Library: 4 total, 4 available (all available)

-- Book 2 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(2, 1, 10, 7),  -- National Library of Tunisia: 10 total, 7 available
(2, 3, 5, 0),   -- Sfax Regional Library: 5 total, 0 available (all borrowed)
(2, 4, 3, 1);   -- Sousse Cultural Center Library: 3 total, 1 available

-- Book 3 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(3, 2, 2, 2),   -- Carthage Municipal Library: 2 total, 2 available
(3, 6, 4, 3),   -- Kairouan Heritage Library: 4 total, 3 available
(3, 7, 6, 5);   -- Monastir University Library: 6 total, 5 available

-- Book 4 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(4, 1, 8, 4),   -- National Library of Tunisia: 8 total, 4 available
(4, 5, 2, 1);   -- Bizerte Public Library: 2 total, 1 available

-- Book 5 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(5, 3, 7, 6),   -- Sfax Regional Library: 7 total, 6 available
(5, 4, 5, 3),   -- Sousse Cultural Center Library: 5 total, 3 available
(5, 7, 3, 2);   -- Monastir University Library: 3 total, 2 available

-- Book 6 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(6, 1, 12, 10), -- National Library of Tunisia: 12 total, 10 available
(6, 2, 4, 4),   -- Carthage Municipal Library: 4 total, 4 available
(6, 6, 3, 0);   -- Kairouan Heritage Library: 3 total, 0 available

-- Book 7 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(7, 4, 6, 5),   -- Sousse Cultural Center Library: 6 total, 5 available
(7, 5, 4, 2);   -- Bizerte Public Library: 4 total, 2 available

-- Book 8 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(8, 1, 15, 12), -- National Library of Tunisia: 15 total, 12 available
(8, 3, 8, 6),   -- Sfax Regional Library: 8 total, 6 available
(8, 7, 5, 4);   -- Monastir University Library: 5 total, 4 available

-- Book 9 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(9, 2, 3, 1),   -- Carthage Municipal Library: 3 total, 1 available
(9, 4, 4, 3),   -- Sousse Cultural Center Library: 4 total, 3 available
(9, 6, 2, 2);   -- Kairouan Heritage Library: 2 total, 2 available

-- Book 10 in multiple libraries
INSERT INTO book_library_inventory (book_id, library_id, total_copies, available_copies) VALUES
(10, 1, 20, 15), -- National Library of Tunisia: 20 total, 15 available
(10, 3, 10, 8),  -- Sfax Regional Library: 10 total, 8 available
(10, 5, 5, 3),   -- Bizerte Public Library: 5 total, 3 available
(10, 7, 7, 6);   -- Monastir University Library: 7 total, 6 available
