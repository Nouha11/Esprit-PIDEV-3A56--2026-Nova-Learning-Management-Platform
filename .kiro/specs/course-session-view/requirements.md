# Requirements Document: Course Session View

## Introduction

The Course Session View feature provides students with an immersive learning environment where they can actively engage with course content. This is distinct from the study session planning feature - while planning creates calendar entries for future study time, the course session view is where actual learning happens. The view integrates time management tools (Pomodoro timer), progress tracking, energy management, and access to course resources in a unified interface.

## Glossary

- **Course_Session_View**: The interactive learning interface where students engage with course content in real-time
- **Study_Session**: A calendar/reminder entry for planned future study time (separate from course sessions)
- **Pomodoro_Timer**: A 25-minute focus timer component for time management during study
- **Energy_Bar**: A visual indicator of the student's current energy level that depletes during study
- **Energy_Restore_Game**: A mini-game categorized as "MINI_GAME" that restores student energy
- **Course_Progress**: A measure of how much of a course a student has completed
- **PDF_Resource**: Downloadable PDF files uploaded by admin/tutor and linked to courses
- **Student_Profile**: The entity containing student data including energy levels
- **Enrolled_Student**: A student who has joined/enrolled in a specific course
- **Course_Completion**: The event when a student finishes all course content (triggers XP, tokens, badges)

## Requirements

### Requirement 1: Start Course Button Visibility

**User Story:** As an enrolled student, I want to see a "Start Course" button on my dashboard, so that I can easily launch the course session view for courses I'm taking.

#### Acceptance Criteria

1. WHEN a student views the dashboard, THE System SHALL display a "Start Course" button for each enrolled course
2. WHEN a student is not enrolled in a course, THE System SHALL NOT display the "Start Course" button for that course
3. WHEN the "Start Course" button is displayed, THE System SHALL position it alongside existing "View Details" and "Plan a Session" buttons
4. THE System SHALL verify student enrollment status before rendering the "Start Course" button

### Requirement 2: Course Session View Navigation

**User Story:** As a student, I want to navigate to a dedicated course session view when I click "Start Course", so that I can focus on learning without distractions.

#### Acceptance Criteria

1. WHEN a student clicks the "Start Course" button, THE System SHALL navigate to the course session view page
2. THE System SHALL pass the course identifier to the course session view
3. THE System SHALL load the course session view with all required components initialized

### Requirement 3: Pomodoro Timer Integration

**User Story:** As a student, I want to use a Pomodoro timer during my course session, so that I can manage my study time effectively.

#### Acceptance Criteria

1. WHEN the course session view loads, THE System SHALL display the existing Pomodoro timer component
2. THE Pomodoro_Timer SHALL be fully functional with start, pause, and reset controls
3. THE Pomodoro_Timer SHALL track 25-minute focus intervals
4. WHEN a Pomodoro interval completes, THE System SHALL update the Pomodoro count

### Requirement 4: Course Progress Display

**User Story:** As a student, I want to see my progress through the course, so that I can track how much I've completed and how much remains.

#### Acceptance Criteria

1. WHEN the course session view loads, THE System SHALL display the student's current progress for the specific course
2. THE System SHALL render progress as a visual indicator (progress bar or equivalent)
3. THE System SHALL calculate progress as a percentage of course completion
4. WHEN course progress changes, THE System SHALL update the progress display

### Requirement 5: Energy Bar Display and Reactivity

**User Story:** As a student, I want to see my current energy level during study, so that I know when I need to take a break.

#### Acceptance Criteria

1. WHEN the course session view loads, THE System SHALL display the student's current energy level
2. THE Energy_Bar SHALL render as a visual indicator showing energy percentage
3. THE Energy_Bar SHALL update in real-time as energy changes
4. THE System SHALL fetch energy level from the Student_Profile entity
5. WHEN energy level changes in the database, THE Energy_Bar SHALL reflect the change immediately

### Requirement 6: Energy Depletion Modal

**User Story:** As a student, I want to be notified immediately when my energy reaches zero, so that I can restore it by playing a game.

#### Acceptance Criteria

1. WHEN the Energy_Bar reaches 0, THE System SHALL trigger a modal immediately with no delay
2. THE System SHALL display the message "Your energy is depleted and you need to play a mini game to restore it" in the modal
3. THE System SHALL include a button or link in the modal that navigates to /games
4. THE System SHALL prevent the modal from being dismissed until the student acknowledges it
5. IF energy reaches 0, THEN THE System SHALL not allow continued course interaction until energy is restored

### Requirement 7: Energy Restore Game Selection

**User Story:** As a student, I want to play an energy restore game when my energy is depleted, so that I can continue studying.

#### Acceptance Criteria

1. WHEN a student navigates to /games from the energy depletion modal, THE System SHALL display available energy restore games
2. THE System SHALL fetch games from the Game entity where category equals "MINI_GAME"
3. THE System SHALL filter games to show only those with energyPoints greater than 0
4. WHEN a student completes an energy restore game, THE System SHALL increase the student's energy by the game's energyPoints value
5. THE System SHALL update the Student_Profile energy field after game completion

### Requirement 8: Course Content Placeholder

**User Story:** As a developer, I want to reserve space for course content in the session view, so that it can be implemented in future iterations.

#### Acceptance Criteria

1. WHEN the course session view loads, THE System SHALL display a content area for course materials
2. THE System SHALL render the placeholder text "Course content here" in the content area
3. THE content area SHALL be positioned prominently in the course session view layout
4. THE content area SHALL be designed to accommodate future content expansion

### Requirement 9: PDF Resources Display

**User Story:** As a student, I want to access downloadable PDF resources during my course session, so that I can reference study materials.

#### Acceptance Criteria

1. WHEN the course session view loads, THE System SHALL display a PDF resources section
2. THE System SHALL fetch PDF resources linked to the current course from the Resource entity
3. THE System SHALL render each PDF resource as a downloadable link with the original filename
4. WHEN a student clicks a PDF resource link, THE System SHALL initiate a download of the PDF file
5. IF no PDF resources exist for the course, THEN THE System SHALL display a message indicating no resources are available

### Requirement 10: Course-Resource Data Model Relationship

**User Story:** As a system administrator, I want PDF resources to be properly linked to courses, so that students can access course-specific materials.

#### Acceptance Criteria

1. THE System SHALL support a relationship between Course and Resource entities
2. THE System SHALL allow multiple PDF resources to be associated with a single course
3. WHEN a resource is uploaded, THE System SHALL link it to the specified course
4. THE System SHALL validate that resources are accessible only through their associated courses

### Requirement 11: Reward Timing Separation

**User Story:** As a student, I want to receive XP, tokens, and badges only when I complete a course, so that rewards reflect actual learning achievement rather than just planning.

#### Acceptance Criteria

1. WHEN a student completes a course, THE System SHALL award XP to the student
2. WHEN a student completes a course, THE System SHALL award tokens to the student
3. WHEN a student completes a course, THE System SHALL check for and award any earned badges
4. WHEN a student completes a course, THE System SHALL trigger addFlash() messages for rewards
5. WHEN a student creates or starts a study session, THE System SHALL NOT award XP, tokens, or badges
6. WHEN a student plans a study session, THE System SHALL NOT trigger reward-related addFlash() messages
7. THE System SHALL update the student's energy level during course sessions, not during session planning

### Requirement 12: Session Type Distinction

**User Story:** As a developer, I want clear separation between study session planning and course sessions, so that the system correctly handles each type of interaction.

#### Acceptance Criteria

1. THE System SHALL maintain separate workflows for study session planning and course session execution
2. WHEN a student uses "Plan a Session", THE System SHALL create a calendar entry without triggering learning mechanics
3. WHEN a student uses "Start Course", THE System SHALL initiate the learning experience with timer, progress, and energy mechanics
4. THE System SHALL not conflate study session planning with course session execution in any controller or service logic
