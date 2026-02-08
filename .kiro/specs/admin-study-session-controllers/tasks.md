# Implementation Plan: Admin StudySession Module Controllers

## Overview

This implementation plan creates three admin controller classes with supporting service layer classes for managing the StudySession module. The implementation follows the existing Symfony patterns in the codebase, including service layer abstraction, CSRF protection, flash messages, and form validation. Tasks are organized to build incrementally, with testing integrated throughout.

## Tasks

- [ ] 1. Create service layer classes for business logic
  - [x] 1.1 Create CourseService with CRUD operations
    - Create `src/Service/StudySession/CourseService.php`
    - Implement `createCourse()`, `updateCourse()`, `deleteCourse()`, `togglePublish()` methods
    - Inject EntityManagerInterface and CourseRepository
    - Add validation for deleting courses with existing plannings
    - _Requirements: 1.2, 1.3, 1.5, 1.6, 4.1_
  
  - [ ]* 1.2 Write property test for CourseService CRUD operations
    - **Property 1: Course CRUD Operations Persist Correctly**
    - **Validates: Requirements 1.2, 1.3**
  
  - [ ]* 1.3 Write unit tests for CourseService edge cases
    - Test deleting course with no plannings succeeds
    - Test deleting course with existing plannings throws exception
    - _Requirements: 1.5_
  
  - [x] 1.4 Create PlanningService with status management
    - Create `src/Service/StudySession/PlanningService.php`
    - Implement `updateStatus()`, `cancelPlanning()`, `findByFilters()` methods
    - Inject EntityManagerInterface and PlanningRepository
    - Add status validation against allowed values
    - _Requirements: 2.3, 2.4, 2.5, 2.6, 4.2_
  
  - [ ]* 1.5 Write property test for PlanningService status validation
    - **Property 6: Planning Status Updates Validate Against Allowed Values**
    - **Validates: Requirements 2.3**
  
  - [ ]* 1.6 Write property test for planning cancellation
    - **Property 7: Planning Cancellation Sets Correct Status**
    - **Validates: Requirements 2.4**
  
  - [x] 1.7 Create StudySessionService with analytics
    - Create `src/Service/StudySession/StudySessionService.php`
    - Implement `findByFilters()` method with user, burnoutRisk, date range filters
    - Implement `getAnalytics()` method with aggregate calculations
    - Inject EntityManagerInterface and StudySessionRepository
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 3.7, 4.3_
  
  - [ ]* 1.8 Write property test for analytics calculations
    - **Property 9: Analytics Calculations Are Accurate**
    - **Validates: Requirements 3.6**
  
  - [ ]* 1.9 Write property test for analytics grouping
    - **Property 10: Analytics Grouping Partitions Data Correctly**
    - **Validates: Requirements 3.7**

- [ ] 2. Checkpoint - Ensure service layer tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 3. Create form types for admin interfaces
  - [x] 3.1 Create CourseFormType
    - Create `src/Form/Admin/CourseFormType.php`
    - Add all Course entity fields with appropriate form field types
    - Configure validation constraints matching entity annotations
    - _Requirements: 7.1, 7.5_
  
  - [x] 3.2 Create PlanningStatusFormType
    - Create `src/Form/Admin/PlanningStatusFormType.php`
    - Add status field with ChoiceType (SCHEDULED, COMPLETED, MISSED, CANCELLED)
    - _Requirements: 2.3, 7.2_
  
  - [ ]* 3.3 Write property test for form validation
    - **Property 12: Form Validation Enforces Entity Constraints**
    - **Validates: Requirements 7.1, 7.2, 7.3**

- [ ] 4. Create AdminCourseController with full CRUD
  - [x] 4.1 Create controller class and index action
    - Create `src/Controller/Admin/StudySession/AdminCourseController.php`
    - Add route attribute `#[Route('/admin/courses')]`
    - Add commented-out `#[IsGranted('ROLE_ADMIN')]` attribute
    - Inject CourseService and CourseRepository in constructor
    - Implement `index()` method to list all courses
    - _Requirements: 1.1, 5.1, 6.1_
  
  - [x] 4.2 Implement new and create actions
    - Implement `new()` method with GET and POST handling
    - Create CourseFormType instance
    - Handle form submission with validation
    - Call CourseService::createCourse() on success
    - Add success/error flash messages
    - Redirect to index on success
    - _Requirements: 1.2, 1.7, 1.8, 7.1, 7.3_
  
  - [x] 4.3 Implement show action
    - Implement `show(Course $course)` method
    - Load course with related plannings
    - Pass to template
    - _Requirements: 1.4_
  
  - [x] 4.4 Implement edit and update actions
    - Implement `edit(Request $request, Course $course)` method
    - Create CourseFormType instance with existing course
    - Handle form submission with validation
    - Call CourseService::updateCourse() on success
    - Add success/error flash messages
    - _Requirements: 1.3, 1.7, 1.8, 7.1, 7.3_
  
  - [x] 4.5 Implement delete action with CSRF protection
    - Implement `delete(Request $request, Course $course)` method
    - Validate CSRF token from request
    - Call CourseService::deleteCourse() on valid token
    - Add success/error flash messages
    - Redirect to index
    - _Requirements: 1.5, 1.7, 5.2, 5.3_
  
  - [x] 4.6 Implement toggle publish action
    - Implement `togglePublish(Request $request, Course $course)` method
    - Call CourseService::togglePublish()
    - Add success flash message
    - Redirect to index or show
    - _Requirements: 1.6, 1.7_
  
  - [ ]* 4.7 Write property test for CSRF protection
    - **Property 2: CSRF Protection for Destructive Operations**
    - **Validates: Requirements 1.5, 5.2, 5.3**
  
  - [ ]* 4.8 Write property test for publication toggle idempotence
    - **Property 3: Publication Toggle Inverts State**
    - **Validates: Requirements 1.6**
  
  - [ ]* 4.9 Write unit tests for controller responses
    - Test index returns correct template with courses
    - Test show returns correct template with course details
    - Test new/edit return correct template with form
    - _Requirements: 1.1, 1.4, 9.4, 9.5, 9.6_

- [ ] 5. Create AdminPlanningController with status management
  - [x] 5.1 Create controller class and index action
    - Create `src/Controller/Admin/StudySession/AdminPlanningController.php`
    - Add route attribute `#[Route('/admin/planning')]`
    - Add commented-out `#[IsGranted('ROLE_ADMIN')]` attribute
    - Inject PlanningService and PlanningRepository in constructor
    - Implement `index(Request $request)` method with filter support
    - Extract query parameters: status, dateFrom, dateTo
    - Call PlanningService::findByFilters()
    - _Requirements: 2.1, 2.5, 2.6, 5.1, 6.2_
  
  - [x] 5.2 Implement show action
    - Implement `show(Planning $planning)` method
    - Load planning with related course and study sessions
    - Pass to template
    - _Requirements: 2.2_
  
  - [x] 5.3 Implement edit status action
    - Implement `editStatus(Request $request, Planning $planning)` method
    - Create PlanningStatusFormType instance
    - Handle form submission with validation
    - Call PlanningService::updateStatus() on success
    - Add success/error flash messages
    - _Requirements: 2.3, 2.7, 7.2, 7.3_
  
  - [x] 5.4 Implement cancel action with CSRF protection
    - Implement `cancel(Request $request, Planning $planning)` method
    - Validate CSRF token from request
    - Call PlanningService::cancelPlanning() on valid token
    - Add success flash message
    - Redirect to index
    - _Requirements: 2.4, 2.7, 5.2, 5.3_
  
  - [ ]* 5.5 Write property test for filtering behavior
    - **Property 8: Filtering Returns Only Matching Records**
    - **Validates: Requirements 2.5, 2.6, 3.3, 3.4, 3.5**
  
  - [ ]* 5.6 Write unit tests for planning controller
    - Test index with filters returns filtered results
    - Test show returns correct template with planning details
    - Test cancel with invalid CSRF is rejected
    - _Requirements: 2.1, 2.2, 5.3_

- [ ] 6. Create AdminStudySessionController with analytics
  - [x] 6.1 Create controller class and index action
    - Create `src/Controller/Admin/StudySession/AdminStudySessionController.php`
    - Add route attribute `#[Route('/admin/study-sessions')]`
    - Add commented-out `#[IsGranted('ROLE_ADMIN')]` attribute
    - Inject StudySessionService and StudySessionRepository in constructor
    - Implement `index(Request $request)` method with filter support
    - Extract query parameters: userId, burnoutRisk, dateFrom, dateTo
    - Call StudySessionService::findByFilters()
    - _Requirements: 3.1, 3.3, 3.4, 3.5, 5.1, 6.3_
  
  - [x] 6.2 Implement show action
    - Implement `show(StudySession $studySession)` method
    - Load study session with related user and planning
    - Pass to template
    - _Requirements: 3.2_
  
  - [x] 6.3 Implement analytics action
    - Implement `analytics(Request $request)` method
    - Extract query parameters: dateFrom, dateTo, groupBy
    - Call StudySessionService::getAnalytics()
    - Pass statistics array to template
    - _Requirements: 3.6, 3.7_
  
  - [ ]* 6.4 Write unit tests for study session controller
    - Test index with filters returns filtered results
    - Test show returns correct template with session details
    - Test analytics returns correct template with statistics
    - _Requirements: 3.1, 3.2, 3.6_

- [ ] 7. Checkpoint - Ensure controller tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Create Twig templates for admin interfaces
  - [x] 8.1 Create course admin templates
    - Create `templates/admin/course/index.html.twig` (list view)
    - Create `templates/admin/course/new.html.twig` (create form)
    - Create `templates/admin/course/edit.html.twig` (edit form)
    - Create `templates/admin/course/show.html.twig` (detail view)
    - Include flash message display in all templates
    - Follow existing admin template structure
    - _Requirements: 1.1, 1.4, 9.1, 9.4, 9.5, 9.6, 9.7_
  
  - [x] 8.2 Create planning admin templates
    - Create `templates/admin/planning/index.html.twig` (list view with filters)
    - Create `templates/admin/planning/show.html.twig` (detail view)
    - Create `templates/admin/planning/edit_status.html.twig` (status form)
    - Include flash message display in all templates
    - _Requirements: 2.1, 2.2, 9.2, 9.4, 9.5, 9.7_
  
  - [ ] 8.3 Create study session admin templates
    - Create `templates/admin/study_session/index.html.twig` (list view with filters)
    - Create `templates/admin/study_session/show.html.twig` (detail view)
    - Create `templates/admin/study_session/analytics.html.twig` (analytics dashboard)
    - Include flash message display in all templates
    - _Requirements: 3.1, 3.2, 3.6, 9.3, 9.4, 9.6, 9.7_

- [ ] 9. Implement flash message handling
  - [ ]* 9.1 Write property test for success flash messages
    - **Property 4: Successful Operations Display Flash Messages**
    - **Validates: Requirements 1.7, 2.7, 8.1, 8.2, 8.3, 8.4**
  
  - [ ]* 9.2 Write property test for error flash messages
    - **Property 5: Failed Operations Display Error Messages**
    - **Validates: Requirements 1.8, 4.4, 8.5**
  
  - [ ]* 9.3 Write property test for flash message categories
    - **Property 13: Flash Messages Use Correct Categories**
    - **Validates: Requirements 8.7**

- [ ] 10. Implement authorization and security
  - [ ]* 10.1 Write property test for authorization
    - **Property 11: Authorization Denies Unauthorized Access**
    - **Validates: Requirements 5.1, 5.4**
  
  - [ ]* 10.2 Write unit tests for route accessibility
    - Test all routes are accessible at correct URLs
    - Test routes respond to correct HTTP methods
    - _Requirements: 6.1, 6.2, 6.3, 6.6_

- [ ] 11. Add repository custom query methods
  - [x] 11.1 Add CourseRepository filter methods
    - Add `findByFilters(?string $difficulty, ?string $category, ?bool $isPublished)` method
    - Use QueryBuilder to construct dynamic query
    - _Requirements: 1.1_
  
  - [x] 11.2 Add PlanningRepository filter methods
    - Add `findByFilters(?string $status, ?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo)` method
    - Use QueryBuilder to construct dynamic query
    - _Requirements: 2.5, 2.6_
  
  - [x] 11.3 Add StudySessionRepository filter and analytics methods
    - Add `findByFilters(?int $userId, ?string $burnoutRisk, ?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo)` method
    - Add `getAggregateStatistics(?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo)` method
    - Add `getGroupedStatistics(string $groupBy, ?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo)` method
    - Use QueryBuilder with aggregate functions (COUNT, AVG, SUM)
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 3.7_

- [ ] 12. Final checkpoint - Integration testing
  - [ ]* 12.1 Write integration tests for complete workflows
    - Test creating course → creating planning → viewing analytics
    - Test editing course → verifying changes persist
    - Test cancelling planning → verifying status change
    - _Requirements: All_
  
  - [ ] 12.2 Ensure all tests pass
    - Run full test suite
    - Verify all property tests pass with 100+ iterations
    - Ask the user if questions arise

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties with minimum 100 iterations
- Unit tests validate specific examples and edge cases
- Service layer is created first to enable controller testing with real business logic
- Templates are created last since they depend on controllers being functional
