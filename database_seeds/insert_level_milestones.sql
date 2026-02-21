-- Level Milestone Rewards
-- Creates milestones for each rank change (every 5 levels)
-- Token rewards start at 50 and increase by 50 for each rank

-- Clear existing level milestones (optional - comment out if you want to keep existing ones)
-- DELETE FROM reward WHERE type = 'LEVEL_MILESTONE';

-- Level 5 - Apprentice
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Apprentice Achievement',
    'Congratulations on reaching Level 5! You are now an Apprentice. Keep learning and growing!',
    'LEVEL_MILESTONE',
    50,
    5,
    NULL,
    1
);

-- Level 10 - Skilled
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Skilled Achievement',
    'You have reached Level 10 and earned the Skilled rank! Your dedication is paying off.',
    'LEVEL_MILESTONE',
    100,
    10,
    NULL,
    1
);

-- Level 15 - Adept
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Adept Achievement',
    'Level 15 unlocked! You are now an Adept. Your proficiency is impressive!',
    'LEVEL_MILESTONE',
    150,
    15,
    NULL,
    1
);

-- Level 20 - Professional
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Professional Achievement',
    'Welcome to Level 20! You have achieved Professional status. Excellence is your standard.',
    'LEVEL_MILESTONE',
    200,
    20,
    NULL,
    1
);

-- Level 25 - Expert
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Expert Achievement',
    'Level 25 reached! You are now an Expert. Your mastery is undeniable.',
    'LEVEL_MILESTONE',
    250,
    25,
    NULL,
    1
);

-- Level 30 - Veteran
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Veteran Achievement',
    'You have reached Level 30 and earned Veteran status! Your experience speaks volumes.',
    'LEVEL_MILESTONE',
    300,
    30,
    NULL,
    1
);

-- Level 35 - Elite
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Elite Achievement',
    'Level 35 unlocked! Welcome to the Elite ranks. You are among the best!',
    'LEVEL_MILESTONE',
    350,
    35,
    NULL,
    1
);

-- Level 40 - Master
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Master Achievement',
    'Congratulations on reaching Level 40! You are now a Master. Near-perfect execution!',
    'LEVEL_MILESTONE',
    400,
    40,
    NULL,
    1
);

-- Level 45 - Grandmaster
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Grandmaster Achievement',
    'Level 45 achieved! You have earned the prestigious Grandmaster rank. Exceptional skill!',
    'LEVEL_MILESTONE',
    450,
    45,
    NULL,
    1
);

-- Level 50 - Champion
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Champion Achievement',
    'You have reached Level 50! Champion status unlocked. Tournament-level performance!',
    'LEVEL_MILESTONE',
    500,
    50,
    NULL,
    1
);

-- Level 55 - Legend
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Legend Achievement',
    'Level 55 reached! You are now a Legend. Your achievements are legendary!',
    'LEVEL_MILESTONE',
    550,
    55,
    NULL,
    1
);

-- Level 60 - Mythic
INSERT INTO reward (name, description, type, value, required_level, icon, is_active) 
VALUES (
    'Mythic Achievement',
    'MAXIMUM LEVEL REACHED! You have achieved Mythic status - the ultimate achievement. You are a true master!',
    'LEVEL_MILESTONE',
    600,
    60,
    NULL,
    1
);

-- Verify the inserted milestones
SELECT 
    id,
    name,
    required_level,
    value as token_reward,
    is_active
FROM reward 
WHERE type = 'LEVEL_MILESTONE' 
ORDER BY required_level ASC;
