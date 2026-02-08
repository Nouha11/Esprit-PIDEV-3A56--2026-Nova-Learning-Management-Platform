# Design Document: Admin StudySession Module Controllers

## Overview

This design specifies the implementation of three admin controller classes for managing the StudySession module in a Symfony PHP application. The controllers follow the existing architectural patterns established in the codebase, including:

- Service layer abstraction for business logic
- CSRF token validation for destructive operations
- Flash messages for user feedback
- Symfony Form components for data validation
- Route attributes with /admin prefix
- ROLE_ADMIN authorization (prepared but commented out)

The three controllers are:
1. **AdminCourseController**: Full CRUD operations for Course entities
2. **AdminPlanningController**: View and status management for Planning entities
3. **AdminStudySessionController**: Read-only views and analytics for StudySession entities

## Architecture

### Controller Layer
The admin controllers reside in `src/Controller/Admin/StudySession/` and handle HTTP requests, form processing, and response rendering. They delegate business logic to service classes and use repositories for data retrieval.

### Service Layer
Three service classes encapsulate business logic:
- **CourseService**: Handles course creation, updates, deletion, and publication toggling
- **PlanningService**: Manages planning status updates, cancellations, and filtering
- **StudySessionService**: Provides analytics calculations and filtered queries

### Repository Layer
Repositories provide custom query methods:
- **CourseRepository**: Find by filters (difficulty, category, publication status)
- **PlanningRepository**: Find by status, date range, and course
- **StudySessionRepository**: Find by user, burnout risk, date range, with aggregate statistics

### Form Layer
Symfony Form types handle validation:
- **CourseFormType**: Course entity form with all fields
- **PlanningStatusFormType**: Simple form for status updates
- No forms needed for StudySession (read-only)

### Template Layer
Twig templates render admin interfaces:
- `templates/admin/course/`: index, new, edit, show
- `templates/admin/planning/`: index, show, edit_status
- `templates/admin/study_session/`: index, show, analytics

## Components and Interfaces

### AdminCourseController

**Location**: `src/Controller/Admin/StudySession/AdminCourseController.php`

**Dependencies**:
- `CourseService`: Business logic for course operations
- `CourseRepository`: Data retrieval

**Routes**:
- `GET /admin/courses` → `admin_course_index`: List all courses
- `GET /admin/courses/new` → `admin_course_new`: Display create form
- `POST /admin/courses/new` → `admin_course_new`: Process create form
- `GET /admin/courses/{id}` → `admin_course_show`: Show course details
- `GET /admin/courses/{id}/edit` → `admin_course_edit`: Display edit form
- `POST /admin/courses/{id}/edit` → `admin_course_edit`: Process edit form
- `POST /admin/courses/{id}/delete` → `admin_course_delete`: Delete course (with CSRF)
- `POST /admin/courses/{id}/toggle-publish` → `admin_course_toggle_publish`: Toggle publication status

**Methods**:

```php
public function index(): Response
// Returns: Rendered template with all courses

public function new(Request $request): Response
// Returns: Form view or redirect to index after creation

public function show(Course $course): Response
// Returns: Rendered template with course details and related plannings

public function edit(Request $request, Course $course): Response
// Returns: Form view or redirect to index after update

public function delete(Request $request, Course $course): Response
// Returns: Redirect to index with flash message

public function togglePublish(Request $request, Course $course): Response
// Returns: Redirect to index or show with flash message
```

### AdminPlanningController

**Location**: `src/Controller/Admin/StudySession/AdminPlanningController.php`

**Dependencies**:
- `PlanningService`: Business logic for planning operations
- `PlanningRepository`: Data retrieval

**Routes**:
- `GET /admin/planning` → `admin_planning_index`: List all planning sessions (with optional filters)
- `GET /admin/planning/{id}` → `admin_planning_show`: Show planning details
- `GET /admin/planning/{id}/edit-status` → `admin_planning_edit_status`: Display status update form
- `POST /admin/planning/{id}/edit-status` → `admin_planning_edit_status`: Process status update
- `POST /admin/planning/{id}/cancel` → `admin_planning_cancel`: Cancel planning session

**Methods**:

```php
public function index(Request $request): Response
// Query params: status, dateFrom, dateTo
// Returns: Rendered template with filtered planning sessions

public function show(Planning $planning): Response
// Returns: Rendered template with planning details, course, and study sessions

public function editStatus(Request $request, Planning $planning): Response
// Returns: Form view or redirect to show after update

public function cancel(Request $request, Planning $planning): Response
// Returns: Redirect to index with flash message
```

### AdminStudySessionController

**Location**: `src/Controller/Admin/StudySession/AdminStudySessionController.php`

**Dependencies**:
- `StudySessionService`: Analytics and filtered queries
- `StudySessionRepository`: Data retrieval

**Routes**:
- `GET /admin/study-sessions` → `admin_study_session_index`: List all study sessions (with optional filters)
- `GET /admin/study-sessions/{id}` → `admin_study_session_show`: Show session details
- `GET /admin/study-sessions/analytics` → `admin_study_session_analytics`: Display analytics dashboard

**Methods**:

```php
public function index(Request $request): Response
// Query params: userId, burnoutRisk, dateFrom, dateTo
// Returns: Rendered template with filtered study sessions

public function show(StudySession $studySession): Response
// Returns: Rendered template with session details, user, and planning

public function analytics(Request $request): Response
// Query params: dateFrom, dateTo, groupBy
// Returns: Rendered template with aggregate statistics and charts
```

### CourseService

**Location**: `src/Service/StudySession/CourseService.php`

**Methods**:

```php
public function createCourse(Course $course): Course
// Persists new course, sets createdAt timestamp
// Returns: The persisted course

public function updateCourse(Course $course): Course
// Flushes changes to existing course
// Returns: The updated course

public function deleteCourse(Course $course): void
// Removes course from database
// Note: May need to handle related plannings (cascade or prevent)

public function togglePublish(Course $course): Course
// Toggles isPublished field and persists
// Returns: The updated course

public function findByFilters(?string $difficulty, ?string $category, ?bool $isPublished): array
// Returns: Array of courses matching filters
```

### PlanningService

**Location**: `src/Service/StudySession/PlanningService.php`

**Methods**:

```php
public function updateStatus(Planning $planning, string $newStatus): Planning
// Validates status value, updates planning, persists
// Returns: The updated planning

public function cancelPlanning(Planning $planning): Planning
// Sets status to CANCELLED, persists
// Returns: The updated planning

public function findByFilters(?string $status, ?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo): array
// Returns: Array of planning sessions matching filters
```

### StudySessionService

**Location**: `src/Service/StudySession/StudySessionService.php`

**Methods**:

```php
public function findByFilters(?int $userId, ?string $burnoutRisk, ?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo): array
// Returns: Array of study sessions matching filters

public function getAnalytics(?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo, ?string $groupBy): array
// Calculates aggregate statistics:
// - Total sessions count
// - Average duration
// - Total XP earned
// - Burnout risk distribution (LOW/MODERATE/HIGH counts)
// - Sessions by course (if groupBy='course')
// - Sessions by user (if groupBy='user')
// - Sessions by date (if groupBy='date')
// Returns: Array with statistics
```

## Data Models

### Course Entity
Already exists at `src/Entity/StudySession/Course.php`

**Key Fields**:
- `id`: int (primary key)
- `courseName`: string (required, 3-255 chars)
- `description`: string (optional, max 255 chars)
- `difficulty`: string (required, enum: BEGINNER/INTERMEDIATE/ADVANCED)
- `estimatedDuration`: int (required, positive)
- `progress`: int (0-100)
- `status`: string (required, enum: NOT_STARTED/IN_PROGRESS/COMPLETED)
- `category`: string (required, 3-255 chars)
- `maxStudents`: int (optional, positive)
- `isPublished`: bool (default false)
- `createdAt`: DateTimeImmutable

**Relationships**:
- `plannings`: OneToMany with Planning

### Planning Entity
Already exists at `src/Entity/StudySession/Planning.php`

**Key Fields**:
- `id`: int (primary key)
- `title`: string (required, 3-255 chars)
- `scheduledDate`: DateTimeImmutable (required, >= today)
- `scheduledTime`: DateTimeImmutable (required)
- `plannedDuration`: int (required, positive)
- `status`: string (required, enum: SCHEDULED/COMPLETED/MISSED/CANCELLED)
- `reminder`: bool (default false)
- `createdAt`: DateTimeImmutable

**Relationships**:
- `course`: ManyToOne with Course (required)
- `studySessions`: OneToMany with StudySession

### StudySession Entity
Already exists at `src/Entity/StudySession/StudySession.php`

**Key Fields**:
- `id`: int (primary key)
- `startedAt`: DateTimeImmutable (required)
- `endedAt`: DateTimeImmutable (optional)
- `duration`: int (required)
- `energyUsed`: int (optional)
- `xpEarned`: int (optional)
- `burnoutRisk`: string (required, enum: LOW/MODERATE/HIGH)

**Relationships**:
- `user`: ManyToOne with User (required)
- `planning`: ManyToOne with Planning (required)

## Data Flow Examples

### Creating a Course
1. Admin navigates to `/admin/courses/new`
2. Controller renders form using CourseFormType
3. Admin submits form
4. Controller validates form data
5. If valid: Controller calls `CourseService::createCourse()`
6. Service persists course with EntityManager
7. Controller adds success flash message
8. Controller redirects to `/admin/courses`

### Cancelling a Planning Session
1. Admin clicks "Cancel" button on planning detail page
2. Browser submits POST to `/admin/planning/{id}/cancel` with CSRF token
3. Controller validates CSRF token
4. Controller calls `PlanningService::cancelPlanning()`
5. Service sets status to CANCELLED and persists
6. Controller adds success flash message
7. Controller redirects to `/admin/planning`

### Viewing Analytics
1. Admin navigates to `/admin/study-sessions/analytics?dateFrom=2024-01-01&dateTo=2024-12-31&groupBy=course`
2. Controller extracts query parameters
3. Controller calls `StudySessionService::getAnalytics()`
4. Service queries repository for aggregate data
5. Service calculates statistics and groups by course
6. Controller passes statistics array to template
7. Template renders charts and tables


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Course CRUD Operations Persist Correctly

*For any* valid course data, when an administrator creates or updates a course through the admin interface, the course should be persisted with all field values matching the submitted data.

**Validates: Requirements 1.2, 1.3**

### Property 2: CSRF Protection for Destructive Operations

*For any* destructive operation (delete, cancel), when the CSRF token is invalid, the operation should be rejected and no data should be modified.

**Validates: Requirements 1.5, 5.2, 5.3**

### Property 3: Publication Toggle Inverts State

*For any* course, toggling the publication status twice should return the course to its original publication state (idempotence property).

**Validates: Requirements 1.6**

### Property 4: Successful Operations Display Flash Messages

*For any* successful CRUD operation (create, update, delete, toggle, cancel), the system should add exactly one success flash message to the session.

**Validates: Requirements 1.7, 2.7, 8.1, 8.2, 8.3, 8.4**

### Property 5: Failed Operations Display Error Messages

*For any* operation that fails validation or encounters an error, the system should add at least one error flash message describing the failure.

**Validates: Requirements 1.8, 4.4, 8.5**

### Property 6: Planning Status Updates Validate Against Allowed Values

*For any* planning status update, when the new status is not in the set {SCHEDULED, COMPLETED, MISSED, CANCELLED}, the update should be rejected with a validation error.

**Validates: Requirements 2.3**

### Property 7: Planning Cancellation Sets Correct Status

*For any* planning session, when an administrator cancels it, the status field should be set to CANCELLED and persisted.

**Validates: Requirements 2.4**

### Property 8: Filtering Returns Only Matching Records

*For any* entity collection (courses, planning sessions, study sessions) and any filter criteria (status, date range, user, burnout risk), all returned records should satisfy the filter criteria and no matching records should be excluded.

**Validates: Requirements 2.5, 2.6, 3.3, 3.4, 3.5**

### Property 9: Analytics Calculations Are Accurate

*For any* set of study sessions, the calculated aggregate statistics (total count, average duration, burnout risk distribution) should match the actual values computed from the session data.

**Validates: Requirements 3.6**

### Property 10: Analytics Grouping Partitions Data Correctly

*For any* set of study sessions and grouping dimension (course, user, date), every session should appear in exactly one group, and the union of all groups should equal the original set.

**Validates: Requirements 3.7**

### Property 11: Authorization Denies Unauthorized Access

*For any* admin route, when accessed by a user without ROLE_ADMIN, the request should be denied and redirected to the login page.

**Validates: Requirements 5.1, 5.4**

### Property 12: Form Validation Enforces Entity Constraints

*For any* form submission with invalid data (missing required fields, out-of-range values, invalid enum values), the form should fail validation and be redisplayed with error messages.

**Validates: Requirements 7.1, 7.2, 7.3**

### Property 13: Flash Messages Use Correct Categories

*For any* flash message added by the system, the category should be one of {success, error, warning, info} and should match the operation outcome (success for successful operations, error for failures).

**Validates: Requirements 8.7**

## Error Handling

### Controller-Level Error Handling

All controllers should implement try-catch blocks around service calls to handle exceptions gracefully:

```php
try {
    $this->courseService->createCourse($course);
    $this->addFlash('success', 'Course created successfully!');
} catch (\Exception $e) {
    $this->addFlash('error', 'Failed to create course: ' . $e->getMessage());
    return $this->redirectToRoute('admin_course_index');
}
```

### Service-Level Error Handling

Services should validate business rules and throw descriptive exceptions:

```php
public function deleteCourse(Course $course): void
{
    if ($course->getPlannings()->count() > 0) {
        throw new \RuntimeException('Cannot delete course with existing planning sessions');
    }
    $this->em->remove($course);
    $this->em->flush();
}
```

### Form Validation Errors

Symfony Form component handles validation errors automatically. Controllers should check `$form->isValid()` and redisplay forms with errors when validation fails.

### CSRF Token Validation

Invalid CSRF tokens should result in:
1. Operation rejection (no data modification)
2. Flash error message
3. Redirect to safe page (typically the list view)

### Database Errors

Database constraint violations (foreign key, unique constraints) should be caught and translated to user-friendly error messages.

### Authorization Errors

Symfony Security component handles authorization automatically via `#[IsGranted('ROLE_ADMIN')]` attribute. Unauthorized access results in redirect to login page.

## Testing Strategy

### Dual Testing Approach

This feature requires both unit tests and property-based tests for comprehensive coverage:

- **Unit tests**: Verify specific examples, edge cases, and integration points
- **Property tests**: Verify universal properties across randomized inputs

### Unit Testing Focus

Unit tests should cover:

1. **Controller Response Structure**
   - List views return correct template with entity collections
   - Detail views return correct template with complete entity data
   - Form views return correct template with form objects

2. **Route Accessibility**
   - All routes are accessible at correct URLs
   - Routes respond to correct HTTP methods
   - Route names follow naming conventions

3. **Edge Cases**
   - Deleting course with no plannings succeeds
   - Deleting course with existing plannings fails gracefully
   - Cancelling already-cancelled planning is idempotent
   - Filtering with no matches returns empty array

4. **Integration Points**
   - Controllers call correct service methods
   - Services call correct repository methods
   - Flash messages are added to session

### Property-Based Testing Configuration

**Library**: Use `facile-it/paratest` or `phpunit/phpunit` with custom data providers for property-based testing in PHP

**Configuration**:
- Minimum 100 iterations per property test
- Each test must reference its design document property
- Tag format: `@Feature admin-study-session-controllers, Property {number}: {property_text}`

**Property Test Implementation**:

Each correctness property must be implemented as a single property-based test:

1. **Property 1**: Generate random valid course data, create/update, verify persistence
2. **Property 2**: Generate random CSRF tokens (valid/invalid), verify rejection of invalid
3. **Property 3**: Generate random courses, toggle twice, verify original state
4. **Property 4**: Perform random successful operations, verify flash message exists
5. **Property 5**: Perform random failing operations, verify error message exists
6. **Property 6**: Generate random status values (valid/invalid), verify validation
7. **Property 7**: Generate random planning sessions, cancel, verify status
8. **Property 8**: Generate random entity collections and filters, verify all results match
9. **Property 9**: Generate random study sessions, calculate statistics, verify accuracy
10. **Property 10**: Generate random study sessions, group by dimension, verify partition
11. **Property 11**: Generate random routes, access without ROLE_ADMIN, verify denial
12. **Property 12**: Generate random invalid form data, verify validation failure
13. **Property 13**: Generate random operations, verify flash message categories

### Test Data Generation

For property-based tests, implement generators for:

- **Course**: Random courseName, description, difficulty, estimatedDuration, category, maxStudents, isPublished
- **Planning**: Random title, scheduledDate, scheduledTime, plannedDuration, status, reminder
- **StudySession**: Random user, planning, startedAt, endedAt, duration, energyUsed, xpEarned, burnoutRisk
- **CSRF Token**: Random valid/invalid token strings
- **Filter Criteria**: Random status values, date ranges, user IDs, burnout risk levels

### Mocking Strategy

For unit tests:
- Mock service layer to isolate controller logic
- Mock repositories to isolate service logic
- Use Symfony's test client for integration tests

For property tests:
- Use real database with transactions (rollback after each test)
- Use Symfony's test container for dependency injection
- Avoid mocking to test real behavior

### Test Organization

```
tests/
  Controller/
    Admin/
      StudySession/
        AdminCourseControllerTest.php (unit tests)
        AdminPlanningControllerTest.php (unit tests)
        AdminStudySessionControllerTest.php (unit tests)
  Service/
    StudySession/
      CourseServiceTest.php (unit tests)
      PlanningServiceTest.php (unit tests)
      StudySessionServiceTest.php (unit tests)
  Property/
    AdminStudySessionControllersPropertyTest.php (property tests for all 13 properties)
```
