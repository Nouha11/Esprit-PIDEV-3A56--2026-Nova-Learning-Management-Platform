# Flash Messages Implementation Summary

## Overview

Flash messages have been integrated throughout the course session flow according to the specifications. All messages are tied to **course completion** and **course session events**, NOT to study session planning.

## Important Distinction

- **Study Session Planning** = Calendar/reminder entry (NO rewards, NO flash messages for XP/tokens)
- **Course Session** = Actual learning experience (triggers rewards and flash messages)
- **Course Completion** = When student finishes course (triggers XP, tokens, badges)

## Implemented Flash Messages

### 1. Course Session Start (CourseSessionController::view)

**Trigger:** When student clicks "Start Course" for the first time

**Message:**
```
✅ SUCCESS: "Course started! Good luck with your session."
```

**Location:** `src/Controller/Front/StudySession/CourseSessionController.php` line ~60

---

### 2. Low Energy Warning (CourseSessionController::view)

**Trigger:** When energy drops to 20% or below (but not 0)

**Message:**
```
⚠️ WARNING: "Your energy is running low! Consider playing a mini game to restore it."
```

**Location:** `src/Controller/Front/StudySession/CourseSessionController.php` line ~50

---

### 3. Not Enrolled Error (CourseSessionController::view)

**Trigger:** Student tries to access a course they're not enrolled in

**Message:**
```
❌ ERROR: "You are not enrolled in this course."
```

**Location:** `src/Controller/Front/StudySession/CourseSessionController.php` line ~42

---

### 4. Course Completion Rewards (CourseSessionController::completeCourse)

**Trigger:** When student completes a course

**Message:**
```
✅ SUCCESS: "Your session has been saved! You earned X XP and Y tokens!"
```

**Location:** `src/Controller/Front/StudySession/CourseSessionController.php` line ~120

**Note:** This is where XP, tokens, and badges are awarded - NOT during session planning!

---

### 5. Energy Restoration (GameController::complete)

**Trigger:** After student completes an energy-restore mini game

**Message:**
```
✅ SUCCESS: "Energy restored! Well done for completing the mini game! (+X energy)"
```

**Location:** `src/Controller/Front/Game/GameController.php` line ~520

**Logic:**
- Only triggers for games with `category = 'MINI_GAME'` AND `energyPoints > 0`
- Restores energy using `EnergyMonitorService::restoreEnergy()`
- Shows actual energy restored amount

---

### 6. Enrollment Request Success (EnrollmentController::request)

**Trigger:** Student successfully requests enrollment

**Message:**
```
✅ SUCCESS: "Enrollment request sent successfully. You will be notified when the tutor responds."
```

**Location:** `src/Controller/Front/StudySession/EnrollmentController.php`

---

### 7. Enrollment Approved (EnrollmentController::approve)

**Trigger:** Tutor approves enrollment request

**Message:**
```
✅ SUCCESS: "Enrollment request approved successfully"
```

**Location:** `src/Controller/Front/StudySession/EnrollmentController.php`

---

## Flash Messages NOT Implemented (As Per Spec)

These were mentioned in your requirements but are **placeholders for future implementation**:

### Future Implementations:

1. **Badge Unlocked:**
   ```
   "Badge unlocked: [reward name]!"
   ```
   - Requires badge system integration
   - Should be added to `CourseSessionController::completeCourse`

2. **Unfinished Session Warning:**
   ```
   "You have an unfinished session for [Course Name]. Pick up where you left off!"
   ```
   - Requires session state tracking
   - Should be added to dashboard or course index

3. **Pomodoro Timer Warning:**
   ```
   "Your Pomodoro session is about to end in 5 minutes."
   ```
   - Requires JavaScript timer integration
   - Should be implemented in frontend

4. **Pending Sessions Info:**
   ```
   "You have [X] pending sessions planned for today."
   ```
   - Should be added to dashboard controller
   - Tied to study session planning (calendar entries)

5. **New Resources Info:**
   ```
   "This course has new PDF resources available."
   ```
   - Requires resource tracking system
   - Placeholder exists in CourseSessionController

6. **No Active Courses Info:**
   ```
   "You haven't started any courses yet. Begin your learning journey!"
   ```
   - Should be added to dashboard when student has no enrollments

7. **Session Save Error:**
   ```
   "Session could not be saved. Please try again."
   ```
   - Should be added to error handling in course completion

---

## Key Controllers Modified

1. **CourseSessionController** (`src/Controller/Front/StudySession/CourseSessionController.php`)
   - Added course start message
   - Added low energy warning
   - Added enrollment error
   - Added course completion rewards

2. **GameController** (`src/Controller/Front/Game/GameController.php`)
   - Added energy restoration message
   - Integrated with EnergyMonitorService

3. **EnrollmentController** (`src/Controller/Front/StudySession/EnrollmentController.php`)
   - Added enrollment request messages
   - Added approval/rejection messages

---

## Services Used

1. **EnergyMonitorService** - Manages energy levels
   - `getCurrentEnergy(User): int`
   - `restoreEnergy(User, int): void`
   - `isEnergyDepleted(User): bool`

2. **EnrollmentService** - Manages course enrollment
   - `isEnrolled(User, Course): bool`
   - `requestEnrollment(User, Course, ?string): EnrollmentRequest`
   - `approveEnrollment(EnrollmentRequest, User): void`

---

## Testing the Flash Messages

### Test Course Session Flow:

1. **Login as student**
2. **Request enrollment** → See "Enrollment request sent" message
3. **Login as tutor** → Approve request → See "Enrollment approved" message
4. **Login as student** → Click "Start Course" → See "Course started!" message
5. **Let energy drop to 20%** → See "Energy running low" warning
6. **Play mini game** → See "Energy restored!" message
7. **Complete course** → See "You earned X XP and Y tokens!" message

### Test Error Cases:

1. **Try to access non-enrolled course** → See "You are not enrolled" error
2. **Try to request enrollment twice** → See error message

---

## Global Flash Message Display

The application already has a global Twig partial for displaying flash messages. The messages are automatically styled and displayed as pop-up notifications.

**No additional template work needed** - just use `$this->addFlash()` in controllers!

---

## Important Notes

✅ **DO:** Tie flash messages to course completion and course session events
✅ **DO:** Award XP/tokens/badges when course is completed
✅ **DO:** Show energy restoration messages after mini games

❌ **DON'T:** Add flash messages to study session planning
❌ **DON'T:** Award rewards when creating calendar entries
❌ **DON'T:** Trigger learning mechanics during session planning

---

## Next Steps

To complete the flash message system:

1. Implement badge system and add "Badge unlocked" messages
2. Add session state tracking for "unfinished session" warnings
3. Integrate Pomodoro timer warnings in JavaScript
4. Add dashboard messages for pending sessions and no active courses
5. Implement resource tracking for "new resources" notifications
