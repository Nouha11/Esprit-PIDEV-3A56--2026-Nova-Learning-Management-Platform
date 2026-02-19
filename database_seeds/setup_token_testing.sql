-- Token System Testing Setup
-- Run this to prepare your database for testing the token spending system

-- ============================================
-- SCENARIO 1: Student with sufficient tokens
-- ============================================
-- Give student ID 5 enough tokens (50 tokens)
UPDATE student_profile SET total_tokens = 50 WHERE id = 5;

-- Make game ID 1 cost 20 tokens (affordable)
UPDATE game SET token_cost = 20 WHERE id = 1;

-- ============================================
-- SCENARIO 2: Student with insufficient tokens
-- ============================================
-- Give student ID 6 few tokens (15 tokens)
UPDATE student_profile SET total_tokens = 15 WHERE id = 6;

-- Make game ID 2 cost 30 tokens (too expensive)
UPDATE game SET token_cost = 30 WHERE id = 2;

-- ============================================
-- SCENARIO 3: Free game
-- ============================================
-- Make game ID 3 free (0 tokens)
UPDATE game SET token_cost = 0 WHERE id = 3;

-- ============================================
-- SCENARIO 4: Expensive game
-- ============================================
-- Make game ID 4 very expensive (100 tokens)
UPDATE game SET token_cost = 100 WHERE id = 4;

-- ============================================
-- SCENARIO 5: Exact token match
-- ============================================
-- Give student ID 7 exactly 25 tokens
UPDATE student_profile SET total_tokens = 25 WHERE id = 7;

-- Make game ID 5 cost exactly 25 tokens
UPDATE game SET token_cost = 25 WHERE id = 5;

-- ============================================
-- Make sure games are active
-- ============================================
UPDATE game SET is_active = 1 WHERE id IN (1, 2, 3, 4, 5);

-- ============================================
-- View current setup
-- ============================================
SELECT 
    'Students' as type,
    id,
    CONCAT(first_name, ' ', last_name) as name,
    total_tokens as tokens,
    NULL as cost
FROM student_profile
WHERE id IN (5, 6, 7)

UNION ALL

SELECT 
    'Games' as type,
    id,
    name,
    NULL as tokens,
    token_cost as cost
FROM game
WHERE id IN (1, 2, 3, 4, 5)
ORDER BY type, id;

-- ============================================
-- Quick reference for testing
-- ============================================
-- Student 5: 50 tokens  -> Game 1: 20 tokens  = CAN PLAY (30 left)
-- Student 6: 15 tokens  -> Game 2: 30 tokens  = CANNOT PLAY (need 15 more)
-- Any student           -> Game 3: 0 tokens   = CAN PLAY (free)
-- Student 5: 50 tokens  -> Game 4: 100 tokens = CANNOT PLAY (need 50 more)
-- Student 7: 25 tokens  -> Game 5: 25 tokens  = CAN PLAY (0 left)
