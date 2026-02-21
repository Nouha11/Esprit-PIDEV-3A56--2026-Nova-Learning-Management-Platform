-- Award missed level milestones to students who already passed those levels
-- This script checks each student's current level and awards any milestones they should have earned

-- Award Level 5 milestone to students at level 5 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 5
  AND sp.level >= 5
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 10 milestone to students at level 10 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 10
  AND sp.level >= 10
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 15 milestone to students at level 15 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 15
  AND sp.level >= 15
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 20 milestone to students at level 20 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 20
  AND sp.level >= 20
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 25 milestone to students at level 25 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 25
  AND sp.level >= 25
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 30 milestone to students at level 30 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 30
  AND sp.level >= 30
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 35 milestone to students at level 35 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 35
  AND sp.level >= 35
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 40 milestone to students at level 40 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 40
  AND sp.level >= 40
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 45 milestone to students at level 45 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 45
  AND sp.level >= 45
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 50 milestone to students at level 50 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 50
  AND sp.level >= 50
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 55 milestone to students at level 55 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 55
  AND sp.level >= 55
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Award Level 60 milestone to students at level 60 or higher who don't have it yet
INSERT INTO student_earned_rewards (student_profile_id, reward_id)
SELECT sp.id, r.id
FROM student_profile sp
CROSS JOIN reward r
WHERE r.type = 'LEVEL_MILESTONE'
  AND r.required_level = 60
  AND sp.level >= 60
  AND NOT EXISTS (
    SELECT 1 FROM student_earned_rewards ser
    WHERE ser.student_profile_id = sp.id
    AND ser.reward_id = r.id
  );

-- Verify the awards
SELECT 
    sp.id,
    sp.first_name,
    sp.last_name,
    sp.level,
    r.name as milestone_name,
    r.required_level,
    r.value as tokens_awarded
FROM student_earned_rewards ser
JOIN student_profile sp ON ser.student_profile_id = sp.id
JOIN reward r ON ser.reward_id = r.id
WHERE r.type = 'LEVEL_MILESTONE'
ORDER BY sp.id, r.required_level;
