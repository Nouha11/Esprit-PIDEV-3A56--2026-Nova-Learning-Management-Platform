-- Migration script to convert from OneToOne to Single Table Inheritance
-- Step 1: Add the new columns to user table
ALTER TABLE user ADD user_type VARCHAR(255) DEFAULT 'user';
ALTER TABLE user ADD first_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE user ADD last_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE user ADD bio LONGTEXT DEFAULT NULL;
ALTER TABLE user ADD university VARCHAR(100) DEFAULT NULL;
ALTER TABLE user ADD major VARCHAR(100) DEFAULT NULL;
ALTER TABLE user ADD academic_level VARCHAR(50) DEFAULT NULL;
ALTER TABLE user ADD profile_picture VARCHAR(255) DEFAULT NULL;
ALTER TABLE user ADD interests JSON DEFAULT NULL;
ALTER TABLE user ADD total_xp INT DEFAULT 0;
ALTER TABLE user ADD total_tokens INT DEFAULT 0;
ALTER TABLE user ADD level INT DEFAULT 1;
ALTER TABLE user ADD expertise JSON DEFAULT NULL;
ALTER TABLE user ADD qualifications LONGTEXT DEFAULT NULL;
ALTER TABLE user ADD years_of_experience INT DEFAULT NULL;
ALTER TABLE user ADD hourly_rate NUMERIC(10, 2) DEFAULT NULL;
ALTER TABLE user ADD is_available TINYINT DEFAULT NULL;

-- Step 2: Migrate student data
UPDATE user u
INNER JOIN student_profile sp ON u.student_profile_id = sp.id
SET 
    u.user_type = 'student',
    u.first_name = sp.first_name,
    u.last_name = sp.last_name,
    u.bio = sp.bio,
    u.university = sp.university,
    u.major = sp.major,
    u.academic_level = sp.academic_level,
    u.profile_picture = sp.profile_picture,
    u.interests = sp.interests,
    u.total_xp = sp.total_xp,
    u.total_tokens = sp.total_tokens,
    u.level = sp.level
WHERE u.student_profile_id IS NOT NULL;

-- Step 3: Migrate tutor data
UPDATE user u
INNER JOIN tutor_profile tp ON u.tutor_profile_id = tp.id
SET 
    u.user_type = 'tutor',
    u.first_name = tp.first_name,
    u.last_name = tp.last_name,
    u.bio = tp.bio,
    u.profile_picture = tp.profile_picture,
    u.expertise = tp.expertise,
    u.qualifications = tp.qualifications,
    u.years_of_experience = tp.years_of_experience,
    u.hourly_rate = tp.hourly_rate,
    u.is_available = tp.is_available
WHERE u.tutor_profile_id IS NOT NULL;

-- Step 4: Drop foreign key constraints
ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492125FF59;
ALTER TABLE user DROP FOREIGN KEY FK_8D93D649430AF9E;

-- Step 5: Drop indexes
DROP INDEX UNIQ_8D93D6492125FF59 ON user;
DROP INDEX UNIQ_8D93D649430AF9E ON user;

-- Step 6: Drop the old profile ID columns
ALTER TABLE user DROP COLUMN student_profile_id;
ALTER TABLE user DROP COLUMN tutor_profile_id;

-- Step 7: Drop the old profile tables
DROP TABLE student_profile;
DROP TABLE tutor_profile;

-- Step 8: Make user_type NOT NULL
ALTER TABLE user MODIFY user_type VARCHAR(255) NOT NULL;
