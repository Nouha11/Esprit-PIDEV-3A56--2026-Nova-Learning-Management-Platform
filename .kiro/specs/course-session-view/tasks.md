# Implementation Plan: Course Session View

## Overview

This implementation plan breaks down the Course Session View feature into discrete coding tasks. The approach follows a layered implementation strategy: data model modifications first, then services, then controllers, and finally frontend integration. Each task builds incrementally to ensure the system remains functional throughout development.

## Tasks

- [x] 1. Update data models and create migration
  - [x] 1.1 Add energy field to StudentProfile entity
    - Add `energy` column (integer, default 100) with getter/setter
    - Ensure energy is capped between 0 and 100 in setter
    - _Requirements: 5.1, 5.3_
  
  - [x] 1.2 Add course relationship to Resource entity
    - Add `course` ManyToOne relationship to Resource entity
    - Add inverse OneToMany relationship to Course entity
    - Update constructors to initialize collections
    - _Requirements: 9.1, 9.3, 10.1, 10.2_
  
  - [x] 1.3 Create database migration
    - Generate migration for energy column in student_profile table
    - Generate migration for course_id column in resource table
    - Add foreign key constraint and index on resource.course_id
    - _Requirements: 5.1, 10.1_

- [x] 2. Implement core services
  - [x] 2.1 Create EnrollmentService in StudySession folder
    - Implement `isEnrolled(User, Course): bool` method
    - Implement `getEnrolledCourses(User): Collection` method
    - Add service registration in services.yaml
    - _Requirements: 1.1, 1.2_
  
  - [ ]* 2.2 Write property test for EnrollmentService
    - **Property 1: Start Course Button Enrollment Visibility**
    - **Validates: Requirements 1.1, 1.2, 1.4**
  
  - [x] 2.3 Create EnergyMonitorService in StudySession folder
    - Implement `getCurrentEnergy(User): int` method with default 100
    - Implement `isEnergyDepleted(User): bool` method
    - Implement `depleteEnergy(User, int): void` method
    - Implement `restoreEnergy(User, int): void` method with cap at 100
    - Add service registration in services.yaml
    - _Requirements: 5.1, 5.3, 6.1, 7.4_
  
  - [ ]* 2.4 Write property test for EnergyMonitorService
    - **Property 9: Energy Restoration After Game**
    - **Validates: Requirements 7.4**
  
  - [x] 2.5 Create CourseResourceService in StudySession folder
    - Implement `getCourseResources(Course): array` method
    - Implement `getResourceUrl(Resource): string` method
    - Add service registration in services.yaml
    - _Requirements: 9.1, 9.3, 10.3_
  
  - [ ]* 2.6 Write property test for CourseResourceService
    - **Property 12: PDF Resources Display**
    - **Property 14: Multiple Resources Per Course**
    - **Validates: Requirements 9.3, 10.2**

- [x] 3. Implement CourseSessionController
  - [x] 3.1 Create controller with view action
    - Create CourseSessionController in src/Controller/Front/StudySession
    - Implement `view(int $courseId): Response` method
    - Add enrollment verification with error handling
    - Load course, student profile, and resources
    - Initialize Pomodoro timer state
    - _Requirements: 1.1, 2.1, 2.2, 2.3_
  
  - [ ]* 3.2 Write unit tests for view action
    - Test enrolled student can access view
    - Test non-enrolled student is redirected
    - Test invalid course ID throws exception
    - _Requirements: 1.1, 1.2_
  
  - [x] 3.3 Implement energy check AJAX endpoint
    - Add `checkEnergy(int $courseId): JsonResponse` method
    - Return JSON with current energy and depleted status
    - Add error handling for missing student profile
    - _Requirements: 5.3, 6.1_
  
  - [ ]* 3.4 Write property test for energy check endpoint
    - **Property 3: Energy Display Accuracy**
    - **Property 4: Energy Bar Reactivity**
    - **Validates: Requirements 5.1, 5.3**

- [x] 4. Create course session view template
  - [x] 4.1 Create base template structure
    - Create templates/front/course/view.html.twig
    - Add page layout with header and main content area
    - Include Bootstrap/CSS framework classes
    - _Requirements: 2.3_
  
  - [x] 4.2 Add Pomodoro timer component
    - Include existing Pomodoro timer component
    - Pass course ID and session data to timer
    - Ensure timer is initialized on page load
    - _Requirements: 3.1, 3.2_
  
  - [x] 4.3 Add course progress bar
    - Create progress bar element with percentage display
    - Bind to course progress data from controller
    - Style with visual indicator (Bootstrap progress bar)
    - _Requirements: 4.1, 4.2, 4.3_
  
  - [x] 4.4 Add energy bar display
    - Create energy bar element with percentage display
    - Add color coding (green > yellow > red based on level)
    - Include energy value text display
    - _Requirements: 5.1, 5.2_
  
  - [x] 4.5 Add course content placeholder
    - Create content area div with placeholder text "Course content here"
    - Style for prominent positioning
    - Design for future expansion
    - _Requirements: 8.1, 8.2_
  
  - [x] 4.6 Add PDF resources section
    - Create resources section with heading
    - Loop through resources and render as download links
    - Display original filename for each resource
    - Show "No resources available" message when empty
    - _Requirements: 9.1, 9.3, 9.5_

- [x] 5. Implement energy depletion modal
  - [x] 5.1 Create modal HTML structure
    - Add modal div with Bootstrap modal classes
    - Include depletion message text
    - Add "Go to Games" button linking to /games
    - Make modal non-dismissible (no close button, backdrop static)
    - _Requirements: 6.1, 6.2, 6.3, 6.4_
  
  - [ ]* 5.2 Write unit test for modal structure
    - Test modal contains correct message
    - Test modal has link to /games
    - Test modal cannot be dismissed
    - _Requirements: 6.2, 6.3, 6.4_

- [x] 6. Implement energy bar JavaScript component
  - [x] 6.1 Create EnergyBar class
    - Create public/js/energy-bar.js
    - Implement constructor with initial energy and check URL
    - Implement `updateDisplay()` method for visual updates
    - Implement `startMonitoring()` method with 5-second polling
    - Implement `checkEnergy()` AJAX method
    - Implement `showDepletionModal()` method
    - _Requirements: 5.3, 6.1_
  
  - [ ]* 6.2 Write property test for energy monitoring
    - **Property 5: Zero Energy Modal Trigger**
    - **Validates: Requirements 6.1**
  
  - [x] 6.3 Add course interaction blocking
    - Disable course content interactions when energy is 0
    - Add visual overlay or disabled state
    - Re-enable when energy is restored
    - _Requirements: 6.5_
  
  - [ ]* 6.4 Write property test for interaction blocking
    - **Property 6: Course Interaction Blocking**
    - **Validates: Requirements 6.5**

- [x] 7. Update dashboard to show Start Course button
  - [x] 7.1 Modify dashboard template
    - Open templates/front/course/index.html.twig
    - Add "Start Course" button to course cards
    - Position alongside "View Details" and "Plan a Session" buttons
    - Use EnrollmentService to check enrollment status
    - Only render button for enrolled courses
    - _Requirements: 1.1, 1.2, 1.3_
  
  - [ ]* 7.2 Write property test for button visibility
    - **Property 1: Start Course Button Enrollment Visibility**
    - **Validates: Requirements 1.1, 1.2, 1.4**

- [ ] 8. Implement energy restore game integration
  - [ ] 8.1 Update GameController to filter energy restore games
    - Modify /games route to accept source parameter
    - When source is "energy-depletion", filter to MINI_GAME category
    - Filter to games with energyPoints > 0
    - _Requirements: 7.1, 7.2, 7.3_
  
  - [ ]* 8.2 Write property test for game filtering
    - **Property 8: Energy Restore Games Display**
    - **Validates: Requirements 7.1, 7.3**
  
  - [ ] 8.3 Update game completion handler
    - Modify game completion logic to call EnergyMonitorService
    - Restore energy by game's energyPoints value
    - Redirect back to course session after energy restore
    - _Requirements: 7.4, 7.5_
  
  - [ ]* 8.4 Write property test for energy restoration
    - **Property 9: Energy Restoration After Game**
    - **Validates: Requirements 7.4**

- [x] 9. Implement course completion rewards
  - [x] 9.1 Create CourseCompletionService
    - Create service to handle course completion logic
    - Implement `completeCourse(User, Course): void` method
    - Award XP using existing XP service
    - Award tokens using existing token service
    - Check and award badges using existing badge service
    - Trigger addFlash() messages for each reward
    - Add service registration in services.yaml
    - _Requirements: 11.1, 11.2, 11.3, 11.4_
  
  - [ ]* 9.2 Write property test for course completion rewards
    - **Property 17: Course Completion Rewards**
    - **Validates: Requirements 11.1, 11.2, 11.3, 11.4**
  
  - [x] 9.3 Verify session planning isolation
    - Review StudySessionController to ensure no reward logic
    - Ensure study session planning doesn't call reward services
    - Ensure no energy modifications in planning workflow
    - _Requirements: 11.5, 11.6, 11.7, 12.2_
  
  - [ ]* 9.4 Write property test for session planning isolation
    - **Property 18: Session Planning Isolation**
    - **Validates: Requirements 11.5, 11.6, 11.7**

- [ ] 10. Implement PDF resource management
  - [ ] 10.1 Update resource upload to support course linking
    - Modify ResourceController upload action
    - Add course selection to upload form
    - Link uploaded resource to selected course
    - _Requirements: 10.3_
  
  - [ ]* 10.2 Write property test for resource-course linking
    - **Property 15: Resource-Course Linking**
    - **Validates: Requirements 10.3**
  
  - [ ] 10.3 Implement resource download endpoint
    - Add download action to ResourceController
    - Verify user is enrolled in resource's course
    - Stream PDF file with appropriate headers
    - Handle file not found errors
    - _Requirements: 9.4, 10.4_
  
  - [ ]* 10.4 Write property test for resource access control
    - **Property 16: Resource Access Control**
    - **Validates: Requirements 10.4**

- [ ] 11. Add routing configuration
  - [ ] 11.1 Register course session routes
    - Add route for course_session_view: /course/{courseId}/session
    - Add route for course_session_energy_check: /course/{courseId}/session/energy-check
    - Ensure routes require ROLE_STUDENT
    - _Requirements: 2.1, 5.3_

- [ ] 12. Checkpoint - Ensure all tests pass
  - Run all unit tests and property tests
  - Verify no regressions in existing functionality
  - Test enrollment verification workflow
  - Test energy depletion and restoration workflow
  - Test PDF resource access workflow
  - Ask the user if questions arise

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- The implementation follows a bottom-up approach: data → services → controllers → views
- Existing Pomodoro timer component is reused, not reimplemented
- Energy monitoring uses polling (5-second interval) for simplicity
- Course completion logic is separated from session planning logic
