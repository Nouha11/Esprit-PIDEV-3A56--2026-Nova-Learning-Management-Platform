# Design Document: Course Session View

## Overview

The Course Session View feature creates an immersive learning environment for students to actively engage with course content. This design implements a dedicated view that integrates time management (Pomodoro timer), progress tracking, energy management, and resource access into a cohesive learning experience.

The system distinguishes between two separate concepts:
- **Study Session Planning**: Creating calendar entries for future study time (existing feature)
- **Course Session Execution**: The actual learning experience with active engagement (this feature)

This design focuses on the course session execution path, ensuring clear separation from the planning workflow.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Student Dashboard                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ View Details │  │ Plan Session │  │ Start Course │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              Course Session View Controller                  │
│  - Verify enrollment                                         │
│  - Load course data                                          │
│  - Initialize session state                                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  Course Session View Page                    │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Pomodoro Timer Component                │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Course Progress Bar                     │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Energy Bar (Reactive)                   │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Course Content Area                     │   │
│  │              (Placeholder for now)                   │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              PDF Resources Section                   │   │
│  │  • Resource 1.pdf                                    │   │
│  │  • Resource 2.pdf                                    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                    (Energy reaches 0)
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              Energy Depletion Modal                          │
│  "Your energy is depleted and you need to play a           │
│   mini game to restore it"                                  │
│  ┌──────────────────────────────┐                          │
│  │  Go to Games (/games)        │                          │
│  └──────────────────────────────┘                          │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

- **Backend**: Symfony 6.x (PHP)
- **Frontend**: Twig templates with JavaScript
- **Database**: Doctrine ORM with existing entities
- **Existing Components**: PomodoroService, PomodoroTimer.js

## Components and Interfaces

### 1. CourseSessionController

**Responsibility**: Handle course session view requests and manage session lifecycle

**Routes**:
```php
#[Route('/course/{courseId}/session', name: 'course_session_view')]
public function view(int $courseId): Response

#[Route('/course/{courseId}/session/energy-check', name: 'course_session_energy_check')]
public function checkEnergy(int $courseId): JsonResponse
```

**Methods**:
```php
// Display the course session view
public function view(int $courseId): Response
  Input: courseId (int)
  Output: Response (rendered Twig template)
  Logic:
    1. Get current user
    2. Verify user is enrolled in course (check User->courses collection)
    3. If not enrolled, redirect with error message
    4. Load course entity
    5. Load student profile (for energy level)
    6. Load course resources (PDFs)
    7. Initialize Pomodoro timer state
    8. Render course_session/view.html.twig with data

// Check energy level via AJAX
public function checkEnergy(int $courseId): JsonResponse
  Input: courseId (int)
  Output: JsonResponse with energy level
  Logic:
    1. Get current user
    2. Get student profile
    3. Return JSON: {energy: <current_energy>, depleted: <boolean>}
```

### 2. EnrollmentService (New)

**Responsibility**: Manage student enrollment verification

**Interface**:
```php
class EnrollmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    // Check if student is enrolled in course
    public function isEnrolled(User $user, Course $course): bool
      Input: user (User), course (Course)
      Output: boolean
      Logic:
        1. Check if course exists in user->getCourses() collection
        2. Return true if found, false otherwise

    // Get enrolled courses for user
    public function getEnrolledCourses(User $user): Collection
      Input: user (User)
      Output: Collection<Course>
      Logic:
        1. Return user->getCourses()
}
```

### 3. CourseResourceService (New)

**Responsibility**: Manage course-specific PDF resources

**Interface**:
```php
class CourseResourceService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $uploadsDirectory
    ) {}

    // Get all PDF resources for a course
    public function getCourseResources(Course $course): array
      Input: course (Course)
      Output: array of Resource entities
      Logic:
        1. Query Resource repository
        2. Filter by course relationship
        3. Return array of resources

    // Get download URL for resource
    public function getResourceUrl(Resource $resource): string
      Input: resource (Resource)
      Output: string (URL path)
      Logic:
        1. Return path to stored file: /uploads/resources/{storedFilename}
}
```

### 4. EnergyMonitorService (New)

**Responsibility**: Monitor and manage student energy levels during course sessions

**Interface**:
```php
class EnergyMonitorService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    // Get current energy level
    public function getCurrentEnergy(User $user): int
      Input: user (User)
      Output: int (energy level 0-100)
      Logic:
        1. Get student profile
        2. Return energy field value (default 100 if null)

    // Check if energy is depleted
    public function isEnergyDepleted(User $user): bool
      Input: user (User)
      Output: boolean
      Logic:
        1. Get current energy
        2. Return true if energy <= 0

    // Deplete energy (for future use)
    public function depleteEnergy(User $user, int $amount): void
      Input: user (User), amount (int)
      Output: void
      Logic:
        1. Get student profile
        2. Reduce energy by amount
        3. Ensure energy doesn't go below 0
        4. Persist changes

    // Restore energy (called after mini-game)
    public function restoreEnergy(User $user, int $amount): void
      Input: user (User), amount (int)
      Output: void
      Logic:
        1. Get student profile
        2. Increase energy by amount
        3. Cap energy at 100
        4. Persist changes
}
```

### 5. Frontend Components

#### Energy Bar Component (JavaScript)

```javascript
class EnergyBar {
    constructor(initialEnergy, checkUrl) {
        this.energy = initialEnergy;
        this.checkUrl = checkUrl;
        this.element = document.getElementById('energy-bar');
        this.modal = document.getElementById('energy-depletion-modal');
    }

    // Update energy display
    updateDisplay() {
        // Update progress bar width
        // Update percentage text
        // Change color based on level (green > yellow > red)
    }

    // Poll server for energy updates
    startMonitoring() {
        setInterval(() => {
            this.checkEnergy();
        }, 5000); // Check every 5 seconds
    }

    // Check energy via AJAX
    async checkEnergy() {
        const response = await fetch(this.checkUrl);
        const data = await response.json();
        
        if (data.depleted && !this.modalShown) {
            this.showDepletionModal();
        }
        
        this.energy = data.energy;
        this.updateDisplay();
    }

    // Show energy depletion modal
    showDepletionModal() {
        this.modal.style.display = 'block';
        this.modalShown = true;
    }
}
```

## Data Models

### Entity Modifications

#### StudentProfile Entity (Modification)

Add energy field if not present:

```php
#[ORM\Column(type: 'integer', options: ['default' => 100])]
private ?int $energy = 100;

public function getEnergy(): ?int
{
    return $this->energy ?? 100;
}

public function setEnergy(int $energy): static
{
    $this->energy = max(0, min(100, $energy));
    return $this;
}
```

#### Resource Entity (Modification)

Add course relationship:

```php
#[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'resources')]
#[ORM\JoinColumn(nullable: true)]
private ?Course $course = null;

public function getCourse(): ?Course
{
    return $this->course;
}

public function setCourse(?Course $course): static
{
    $this->course = $course;
    return $this;
}
```

#### Course Entity (Modification)

Add resources relationship:

```php
#[ORM\OneToMany(mappedBy: 'course', targetEntity: Resource::class)]
private Collection $resources;

// In constructor
$this->resources = new ArrayCollection();

public function getResources(): Collection
{
    return $this->resources;
}

public function addResource(Resource $resource): static
{
    if (!$this->resources->contains($resource)) {
        $this->resources->add($resource);
        $resource->setCourse($this);
    }
    return $this;
}

public function removeResource(Resource $resource): static
{
    if ($this->resources->removeElement($resource)) {
        if ($resource->getCourse() === $this) {
            $resource->setCourse(null);
        }
    }
    return $this;
}
```

### Database Schema Changes

**Migration Required**:
1. Add `energy` column to `student_profile` table (INT, default 100)
2. Add `course_id` column to `resource` table (INT, nullable, foreign key to course.id)
3. Add index on `resource.course_id` for query performance

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: Start Course Button Enrollment Visibility

*For any* student and course, the "Start Course" button should be visible on the dashboard if and only if the student is enrolled in that course.

**Validates: Requirements 1.1, 1.2, 1.4**

### Property 2: Course Identifier in Navigation

*For any* course session navigation, the URL should contain the course identifier as a parameter.

**Validates: Requirements 2.2**

### Property 3: Energy Display Accuracy

*For any* student viewing a course session, the displayed energy level should match the student's current energy value in the Student_Profile entity.

**Validates: Requirements 5.1**

### Property 4: Energy Bar Reactivity

*For any* change to a student's energy level in the database, the energy bar display should update to reflect the new value within the polling interval.

**Validates: Requirements 5.3**

### Property 5: Zero Energy Modal Trigger

*For any* student whose energy reaches 0, the energy depletion modal should be triggered immediately.

**Validates: Requirements 6.1**

### Property 6: Course Interaction Blocking

*For any* student with zero energy, attempts to interact with course content should be blocked until energy is restored.

**Validates: Requirements 6.5**

### Property 7: Modal Dismissal Prevention

*For any* energy depletion modal, attempts to dismiss it without acknowledgment should fail and the modal should remain visible.

**Validates: Requirements 6.4**

### Property 8: Energy Restore Games Display

*For any* games page view from the energy depletion modal, all displayed games should have category "MINI_GAME" and energyPoints greater than 0.

**Validates: Requirements 7.1, 7.3**

### Property 9: Energy Restoration After Game

*For any* completed energy restore game, the student's energy should increase by exactly the game's energyPoints value (capped at 100).

**Validates: Requirements 7.4**

### Property 10: Progress Display Accuracy

*For any* course session view, the displayed progress percentage should match the course's progress field value.

**Validates: Requirements 4.1, 4.3**

### Property 11: Progress Display Reactivity

*For any* change to a course's progress value, the progress display should update to reflect the new percentage.

**Validates: Requirements 4.4**

### Property 12: PDF Resources Display

*For any* course with associated PDF resources, each resource should be rendered as a downloadable link displaying the original filename.

**Validates: Requirements 9.3**

### Property 13: PDF Download Functionality

*For any* PDF resource link click, the system should initiate a download of the corresponding PDF file.

**Validates: Requirements 9.4**

### Property 14: Multiple Resources Per Course

*For any* course, the system should support associating and displaying multiple PDF resources.

**Validates: Requirements 10.2**

### Property 15: Resource-Course Linking

*For any* resource upload with a specified course, the resource should be linked to that course and accessible through the course session view.

**Validates: Requirements 10.3**

### Property 16: Resource Access Control

*For any* PDF resource, it should only be displayed and accessible when viewing its associated course's session view.

**Validates: Requirements 10.4**

### Property 17: Course Completion Rewards

*For any* course completion event, the student should receive XP, tokens, badge checks, and flash messages for rewards.

**Validates: Requirements 11.1, 11.2, 11.3, 11.4**

### Property 18: Session Planning Isolation

*For any* study session planning action (create or plan), the system should NOT award XP, tokens, badges, trigger reward flash messages, or modify energy levels.

**Validates: Requirements 11.5, 11.6, 11.7**

## Error Handling

### Enrollment Verification Errors

**Scenario**: Student attempts to access course session without enrollment

**Handling**:
```php
if (!$enrollmentService->isEnrolled($user, $course)) {
    $this->addFlash('error', 'You must be enrolled in this course to start a session.');
    return $this->redirectToRoute('course_catalog');
}
```

### Energy Depletion Errors

**Scenario**: Energy check fails due to missing student profile

**Handling**:
```php
$studentProfile = $user->getStudentProfile();
if (!$studentProfile) {
    throw new \RuntimeException('Student profile not found');
}
```

**Fallback**: Default energy to 100 if field is null

### Resource Access Errors

**Scenario**: PDF file not found on disk

**Handling**:
```php
if (!file_exists($filePath)) {
    $this->addFlash('error', 'Resource file not found.');
    return $this->redirectToRoute('course_session_view', ['courseId' => $courseId]);
}
```

### Course Not Found Errors

**Scenario**: Invalid course ID provided

**Handling**:
```php
$course = $courseRepository->find($courseId);
if (!$course) {
    throw $this->createNotFoundException('Course not found');
}
```

## Testing Strategy

### Dual Testing Approach

This feature requires both unit tests and property-based tests for comprehensive coverage:

- **Unit tests**: Verify specific examples, edge cases, and integration points
- **Property tests**: Verify universal properties across all inputs through randomization

### Unit Testing Focus

Unit tests should cover:
- Specific enrollment scenarios (enrolled vs not enrolled)
- Energy depletion modal trigger at exactly 0 energy
- PDF resource download with specific file types
- Course completion reward calculations
- Error conditions (missing profile, invalid course ID, file not found)
- Integration between controller and services

### Property-Based Testing Configuration

- **Library**: Use a PHP property-based testing library (e.g., Eris for PHP)
- **Iterations**: Minimum 100 iterations per property test
- **Tagging**: Each property test must reference its design document property
- **Tag format**: `// Feature: course-session-view, Property {number}: {property_text}`

### Property Test Examples

**Property 1 Test**:
```php
// Feature: course-session-view, Property 1: Start Course Button Enrollment Visibility
public function testStartCourseButtonVisibilityBasedOnEnrollment()
{
    // Generate random users and courses
    // Randomly enroll some users in some courses
    // Render dashboard for each user
    // Assert button appears only for enrolled courses
}
```

**Property 9 Test**:
```php
// Feature: course-session-view, Property 9: Energy Restoration After Game
public function testEnergyRestorationMatchesGamePoints()
{
    // Generate random energy restore games with random energyPoints
    // Generate random students with random initial energy
    // Complete each game
    // Assert energy increase equals game's energyPoints (capped at 100)
}
```

**Property 18 Test**:
```php
// Feature: course-session-view, Property 18: Session Planning Isolation
public function testSessionPlanningDoesNotTriggerLearningMechanics()
{
    // Generate random students with initial XP, tokens, energy
    // Create study session plans
    // Assert XP, tokens, energy remain unchanged
    // Assert no reward flash messages appear
}
```

### Test Coverage Goals

- Controller methods: 100% coverage
- Service methods: 100% coverage
- Edge cases: Zero energy, no resources, no enrollment
- Error paths: All error handlers tested
- Integration: Full workflow from button click to session view
