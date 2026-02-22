-- Fix mini game engines
-- This script updates the description field to include the correct engine tag
-- for mini games that were created from templates

-- Update Breathing Exercise
UPDATE game 
SET description = CONCAT(
    REPLACE(description, ' [Engine: reaction_clicker]', ''),
    ' [Engine: breathing]'
)
WHERE name LIKE '%Breathing Exercise%' 
AND category = 'MINI_GAME'
AND description NOT LIKE '%[Engine: breathing]%';

-- Update Quick Stretch
UPDATE game 
SET description = CONCAT(
    REPLACE(description, ' [Engine: reaction_clicker]', ''),
    ' [Engine: stretch]'
)
WHERE name LIKE '%Quick Stretch%' 
AND category = 'MINI_GAME'
AND description NOT LIKE '%[Engine: stretch]%';

-- Update Eye Rest
UPDATE game 
SET description = CONCAT(
    REPLACE(description, ' [Engine: reaction_clicker]', ''),
    ' [Engine: eye_rest]'
)
WHERE name LIKE '%Eye Rest%' 
AND category = 'MINI_GAME'
AND description NOT LIKE '%[Engine: eye_rest]%';

-- Update Hydration Break
UPDATE game 
SET description = CONCAT(
    REPLACE(description, ' [Engine: reaction_clicker]', ''),
    ' [Engine: hydration]'
)
WHERE name LIKE '%Hydration Break%' 
AND category = 'MINI_GAME'
AND description NOT LIKE '%[Engine: hydration]%';

-- Verify the changes
SELECT id, name, type, category, description 
FROM game 
WHERE category = 'MINI_GAME'
ORDER BY id;
