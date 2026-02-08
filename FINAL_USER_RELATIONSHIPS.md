# Final User Relationships - Corrected

## User Entity Relationships (OneToMany)

The User entity now has the following **OneToMany** relationships as per the class diagram:

### 1. User → Post (OneToMany)
- **Description**: A User can create many Posts
- **Mapped By**: `author` in Post entity
- **Database**: `post.author_id` → `user.id`

### 2. User → Course (OneToMany)
- **Description**: A User (instructor) can teach many Courses
- **Mapped By**: `instructor` in Course entity
- **Database**: `course.instructor_id` → `user.id`

### 3. User → Book (OneToMany)
- **Description**: A User can own many Books
- **Mapped By**: `user` in Book entity
- **Database**: `books.user_id` → `user.id`

### 4. User → Game (OneToMany)
- **Description**: A User can create/own many Games
- **Mapped By**: `user` in Game entity
- **Database**: `game.user_id` → `user.id`

### 5. User → Question (OneToMany)
- **Description**: A User can create many Questions
- **Mapped By**: `user` in Question entity
- **Database**: `question.user_id` → `user.id`

## User Profiles (OneToOne)

### 6. User ↔ StudentProfile (OneToOne)
- **Description**: A User can have one StudentProfile
- **Note**: Student inherits User attributes
- **Database**: `user.student_profile_id` → `student_profile.id`

### 7. User ↔ TutorProfile (OneToOne)
- **Description**: A User can have one TutorProfile
- **Note**: Tutor inherits User attributes
- **Database**: `user.tutor_profile_id` → `tutor_profile.id`

## Removed Relationships

The following relationships were **removed** as they are not connected to User in the class diagram:

- ❌ User → StudySession (removed)
- ❌ User → Comment (removed)

## Database Schema

### Tables Modified:
1. **books** - Added `user_id` (FK → user.id, nullable)
2. **game** - Added `user_id` (FK → user.id, nullable)
3. **question** - Added `user_id` (FK → user.id, nullable)
4. **course** - Already has `instructor_id` (FK → user.id, nullable)

### Join Tables Removed:
- ❌ `user_books` (no longer needed - now OneToMany)
- ❌ `user_games` (no longer needed - now OneToMany)

## Entity Files Modified

### 1. User Entity (`src/Entity/users/User.php`)
**Added Collections**:
- `posts` (OneToMany → Post)
- `courses` (OneToMany → Course)
- `books` (OneToMany → Book)
- `games` (OneToMany → Game)
- `questions` (OneToMany → Question)

**Removed Collections**:
- `studySessions` (removed)
- `comments` (removed)

**Methods Added**:
- `getPosts()`, `addPost()`, `removePost()`
- `getCourses()`, `addCourse()`, `removeCourse()`
- `getBooks()`, `addBook()`, `removeBook()`
- `getGames()`, `addGame()`, `removeGame()`
- `getQuestions()`, `addQuestion()`, `removeQuestion()`

**Methods Removed**:
- `getStudySessions()`, `addStudySession()`, `removeStudySession()`
- `getComments()`, `addComment()`, `removeComment()`

### 2. Book Entity (`src/Entity/Library/Book.php`)
- Changed from ManyToMany to ManyToOne
- Added `user` property (ManyToOne → User)
- Added `getUser()` and `setUser()` methods
- Removed `users` collection and related methods

### 3. Game Entity (`src/Entity/Gamification/Game.php`)
- Changed from ManyToMany to ManyToOne
- Added `user` property (ManyToOne → User)
- Added `getUser()` and `setUser()` methods
- Removed `users` collection and related methods

### 4. Question Entity (`src/Entity/Quiz/Question.php`)
- Added `user` property (ManyToOne → User)
- Added `getUser()` and `setUser()` methods

## Summary

✅ **All relationships are now OneToMany** as per the class diagram
✅ **Student and Tutor profiles** are connected via OneToOne (inheritance pattern)
✅ **Removed unnecessary relationships** (StudySession, Comment)
✅ **Added Question relationship** to User
✅ **Database schema updated** successfully
✅ **Cache cleared**

## User Relationships Overview

```
User (1) -----> (Many) Post
User (1) -----> (Many) Course
User (1) -----> (Many) Book
User (1) -----> (Many) Game
User (1) -----> (Many) Question
User (1) <----> (1) StudentProfile
User (1) <----> (1) TutorProfile
```

All relationships match the class diagram requirements!
