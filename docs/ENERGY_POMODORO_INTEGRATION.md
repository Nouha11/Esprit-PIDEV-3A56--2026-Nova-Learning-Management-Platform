# Energy & Pomodoro Integration System

## Overview
Complete integration of the Pomodoro timer with the energy system and course progress tracking. This system ties study time to energy depletion, course progress updates, and provides automatic energy refill mechanisms.

## Features Implemented

### 1. Pomodoro Timer Integration with Energy System
- **Energy Depletion**: Each completed Pomodoro (25 minutes) depletes 5 energy points
- **Automatic Blocking**: When energy reaches 0, the Pomodoro timer is disabled and course content is blocked
- **Real-time Updates**: Energy bar updates immediately after each Pomodoro completion

### 2. Course Progress Tracking
- **Progress Calculation**: Each Pomodoro completion = 5% course progress (max 100%)
- **Status Updates**: Course status automatically changes:
  - `NOT_STARTED` → `IN_PROGRESS` (when progress > 0%)
  - `IN_PROGRESS` → `COMPLETED` (when progress = 100%)
- **Visual Feedback**: Progress bar updates in real-time

### 3. Energy Blocking System
- **Access Control**: Students cannot start course sessions if energy is 0
- **Redirect**: Users with 0 energy are redirected to course detail page with error message
- **Visual Indicators**: 
  - Overlay blocks course content when energy is depleted
  - Modal popup alerts users when energy reaches 0
  - Start button is disabled when energy is 0

### 4. Auto-Refill System
- **Passive Regeneration**: 1 energy point every 30 minutes automatically
- **Timestamp Tracking**: `lastEnergyUpdate` field tracks last energy change
- **Automatic Application**: Auto-refill is calculated and applied whenever energy is checked

### 5. Mini Game Energy Restoration
- **Manual Restoration**: Students can play mini games to restore energy
- **Quick Access**: Links to mini games provided in energy-depleted states
- **Immediate Effect**: Energy updates immediately after mini game completion

## Technical Implementation

### Database Changes

#### StudentProfile Entity
```php
// New field added
#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
private ?\DateTimeInterface $lastEnergyUpdate = null;
```

**Migration**: `Version20260222210256.php`
```sql
ALTER TABLE student_profile ADD last_energy_update DATETIME DEFAULT NULL;
```

### Backend Changes

#### CourseSessionController
**New Routes**:
1. `course_session_deplete_energy` - POST endpoint to deplete energy
2. `course_session_update_progress` - POST endpoint to update course progress

**Energy Blocking Logic**:
```php
// Check energy before allowing access
$currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);

if ($currentEnergy <= 0) {
    $this->addFlash('error', 'Your energy is depleted! Play mini games to restore it or wait 30 minutes for auto-refill.');
    return $this->redirectToRoute('course_show', ['id' => $courseId]);
}
```

#### EnergyMonitorService
**Auto-Refill Logic**:
```php
private function applyAutoRefill($studentProfile): void
{
    $lastUpdate = $studentProfile->getLastEnergyUpdate();
    $now = new \DateTime();
    $minutesPassed = ($now->getTimestamp() - $lastUpdate->getTimestamp()) / 60;
    
    // 1 point per 30 minutes
    $energyToRefill = floor($minutesPassed / 30);
    
    if ($energyToRefill > 0) {
        $newEnergy = min(100, $currentEnergy + $energyToRefill);
        $studentProfile->setEnergy($newEnergy);
        $studentProfile->setLastEnergyUpdate($now);
    }
}
```

### Frontend Changes

#### pomodoro-timer.js
**New Methods**:
- `depleteEnergy(amount)` - Depletes energy after Pomodoro completion
- `updateCourseProgress()` - Updates course progress based on Pomodoro count
- `handleEnergyDepletion()` - Handles UI when energy reaches 0

**Integration Flow**:
```javascript
async complete() {
    // 1. Increment pomodoro count
    this.pomodoroCount++;
    
    // 2. Deplete energy (5 points)
    await this.depleteEnergy(5);
    
    // 3. Update course progress
    await this.updateCourseProgress();
    
    // 4. Update server
    await this.updateServer();
}
```

#### view.html.twig
**Configuration Passed to JavaScript**:
```javascript
window.pomodoroData = {
    courseId: {{ course.id }},
    depleteEnergyUrl: '{{ path('course_session_deplete_energy', {courseId: course.id}) }}',
    updateProgressUrl: '{{ path('course_session_update_progress', {courseId: course.id}) }}'
};
```

#### detail.html.twig
**Start Course Button**:
```html
<a href="{{ path('course_session_view', {courseId: course.id}) }}" class="btn btn-success btn-lg">
    <i class="bi bi-play-circle"></i> Start Course Session
</a>
```

## User Experience Flow

### Starting a Course Session
1. Student clicks "Start Course Session" button on course detail page
2. System checks energy level:
   - **Energy > 0**: Access granted, session starts
   - **Energy = 0**: Access denied, redirected with error message
3. Energy bar and Pomodoro timer are displayed

### During Study Session
1. Student starts Pomodoro timer (25 minutes)
2. Timer counts down
3. On completion:
   - Energy depletes by 5 points
   - Course progress increases by 5%
   - Break suggestion is shown
   - Energy bar updates visually

### Energy Depletion
1. When energy reaches 0:
   - Pomodoro timer is paused/disabled
   - Modal popup appears
   - Course content is blocked with overlay
   - Options presented:
     - Play mini games (immediate restoration)
     - Wait 30 minutes (auto-refill)

### Energy Restoration
**Option 1: Mini Games**
- Click "Play Mini Games" button
- Complete a mini game
- Energy restored immediately
- Return to course session

**Option 2: Auto-Refill**
- Wait 30 minutes
- 1 energy point restored automatically
- Continue waiting or play mini games for faster restoration

## Configuration

### Energy Settings
- **Initial Energy**: 100 points
- **Max Energy**: 100 points
- **Depletion Rate**: 5 points per Pomodoro (25 minutes)
- **Auto-Refill Rate**: 1 point per 30 minutes
- **Warning Threshold**: 20 points (shows warning message)

### Progress Settings
- **Progress per Pomodoro**: 5%
- **Max Progress**: 100%
- **Completion Threshold**: 100% (marks course as COMPLETED)

## API Endpoints

### POST /course/{courseId}/session/deplete-energy
**Request Body**:
```json
{
    "amount": 5
}
```

**Response**:
```json
{
    "success": true,
    "energy": 45,
    "depleted": false
}
```

### POST /course/{courseId}/session/update-progress
**Request Body**:
```json
{
    "pomodoroCount": 10
}
```

**Response**:
```json
{
    "success": true,
    "progress": 50,
    "status": "IN_PROGRESS"
}
```

### GET /course/{courseId}/session/energy-check
**Response**:
```json
{
    "energy": 75,
    "depleted": false
}
```

## Testing

### Test Scenarios

1. **Normal Study Flow**
   - Start course with full energy (100)
   - Complete 5 Pomodoros
   - Verify energy = 75, progress = 25%

2. **Energy Depletion**
   - Set energy to 5
   - Complete 1 Pomodoro
   - Verify energy = 0, modal appears, content blocked

3. **Auto-Refill**
   - Set energy to 50
   - Set lastEnergyUpdate to 1 hour ago
   - Refresh page
   - Verify energy = 52 (2 points refilled)

4. **Course Completion**
   - Complete 20 Pomodoros
   - Verify progress = 100%, status = COMPLETED

5. **Access Blocking**
   - Set energy to 0
   - Try to access course session
   - Verify redirect to course detail with error

## Future Enhancements

1. **Configurable Rates**: Allow admins to configure energy depletion and refill rates
2. **Energy Boosts**: Special items or achievements that increase energy capacity
3. **Study Streaks**: Bonus energy for consecutive study days
4. **Energy Analytics**: Track energy patterns and provide insights
5. **Notifications**: Alert users when energy is fully refilled
6. **Difficulty Scaling**: Harder courses deplete more energy

## Troubleshooting

### Energy Not Depleting
- Check browser console for JavaScript errors
- Verify `depleteEnergyUrl` is correctly set in template
- Check network tab for failed API calls

### Auto-Refill Not Working
- Verify `lastEnergyUpdate` field exists in database
- Check `EnergyMonitorService::applyAutoRefill()` is being called
- Verify timestamp calculations are correct

### Progress Not Updating
- Check `updateProgressUrl` is correctly set
- Verify course entity has `progress` field
- Check database for updated progress values

## Related Documentation
- [Energy System](ENERGY_SYSTEM.md)
- [Pomodoro Timer](../public/js/pomodoro-timer.js)
- [Course Session Controller](../src/Controller/Front/StudySession/CourseSessionController.php)
- [Energy Monitor Service](../src/Service/StudySession/EnergyMonitorService.php)
