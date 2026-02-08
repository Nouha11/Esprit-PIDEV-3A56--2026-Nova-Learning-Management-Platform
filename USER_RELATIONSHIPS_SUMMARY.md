# User Entity Relationships Summary

## Complete Relationship Diagram

Based on the class diagram, the User entity now has the following relationships:

### 1. User ↔ StudentProfile (One-to-One)
- **Type**: OneToOne
- **Direction**: Bidirectional
- **Description**: A User can have one StudentProfile
- **Database**: `user.student_profile_id` → `student_profile.id`

### 2. User ↔ TutorProfile (One-to-One)
- **Type**: OneToOne
- **Direction**: Bidirectional
- **Description**: A User can have one TutorProfile
- **Database**: `user.tutor_profile_id` → `tutor_profile.id`

### 3. User → Post (One-to-Many)
- **Type**: OneToMany
- **Direction**: Bidirectional
- **Description**: A User can create many Posts
- **Mapped By**: `author` in Post entity
- **Database**: `post.author_id` → `user.id`

### 4. User → Comment (One-to-Many)
- **Type**: OneToMany
- **Direction**: Bidirectional
- **Description**: A User can create many Comments
- **Mapped By**: `author` in Comment entity
- **Database**: `comment.author_id` → `user.id`

### 5. User → StudySession (One-to-Many)
- **Type**: OneToMany
- **Direction**: Bidirectional
- **Description**: A User can have many StudySessions
- **Mapped By**: `user` in StudySession entity
- **Database**: `study_session.user_id` → `user.id`

### 6. User → Course (One-to-Many)
- **Type**: OneToMany
- **Direction**: Bidirectional
- **Description**: A User (instructor) can teach many Courses
- **Mapped By**: `instructor` in Course entity
- **Database**: `course.instructor_id` → `user.id`
- **NEW**: ✅ Added in this update

### 7. User → Book (One-to-Many)
- **Type**: OneToMany
- **Direction**: Bidirectional
- **Description**: A User can own many Books
- **Mapped By**: `user` in Book entity
- **Database**: `books.user_id` → `user.id`
- **UPDATED**: ✅ Changed from ManyToMany to OneToMany

### 8. User → Game (One-to-Many)
- **Type**: OneToMany
- **Direction**: Bidirectional
- **Description**: A User can create/own many Games
- **Mapped By**: `user` in Game entity
- **Database**: `game.user_id` → `user.id`
- **UPDATED**: ✅ Changed from ManyToMany to OneToMany

## Additional User Properties

### XP Field
- **Type**: Integer
- **Default**: 0
- **Description**: Experience points for gamification
- **Database**: `user.xp`

## Entity Files Modified

1. ✅ `src/Entity/users/User.php`
   - Added `courses` collection (OneToMany)
   - Added `books` collection (OneToMany)
   - Added `games` collection (OneToMany)
   - Added getter/setter methods for courses
   - Added getter/setter methods for books
   - Added getter/setter methods for games

2. ✅ `src/Entity/StudySession/Course.php`
   - Added `instructor` property (ManyToOne → User)
   - Added getter/setter methods for instructor

3. ✅ `src/Entity/Library/Book.php`
   - Added `user` property (ManyToOne → User)
   - Added getter/setter methods for user

4. ✅ `src/Entity/Gamification/Game.php`
   - Added `user` property (ManyToOne → User)
   - Added getter/setter methods for user

## Database Schema Updates

The following tables and columns were created/modified:

1. **Modified Table**: `books`
   - Added `user_id` (FK → user.id, nullable)

2. **Modified Table**: `game`
   - Added `user_id` (FK → user.id, nullable)

3. **Modified Table**: `course`
   - Added `instructor_id` (FK → user.id, nullable)

4. **Removed Tables**:
   - `user_books` (join table - no longer needed)
   - `user_games` (join table - no longer needed)

## Relationship Summary by Entity

### User has:
- 1 StudentProfile (optional)
- 1 TutorProfile (optional)
- Many Posts
- Many Comments
- Many StudySessions
- Many Courses (as instructor)
- Many Books
- Many Games

### StudentProfile belongs to:
- 1 User

### TutorProfile belongs to:
- 1 User

### Post belongs to:
- 1 User (author)

### Comment belongs to:
- 1 User (author)

### StudySession belongs to:
- 1 User

### Course belongs to:
- 1 User (instructor, optional)

### Book belongs to:
- 1 User (optional)

### Game belongs to:
- 1 User (optional)

## Notes

- The Loan entity still maintains its own relationship with User for tracking book borrowing history
- The User ↔ Book relationship represents ownership/access, while Loan represents borrowing transactions
- All relationships are properly bidirectional with cascade operations where appropriate
