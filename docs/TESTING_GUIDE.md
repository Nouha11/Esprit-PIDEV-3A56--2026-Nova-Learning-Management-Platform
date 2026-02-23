# Energy & Pomodoro System - Testing Guide

## Quick Start Testing

### Prerequisites
- Database migration completed: `last_energy_update` field added to `student_profile` table
- Cache cleared (if needed)
- Logged in as a student user
- Enrolled in at least one course

## Test Scenarios

### 1. Basic Pomodoro Flow (5 minutes)

**Steps**:
1. Navigate to any course detail page: `http://127.0.0.1:8000/courses/{id}`
2. Click the green "Start Course Session" button
3. Verify you see:
   - Pomodoro timer (25:00)
   - Energy bar (should show current energy)
   - Course progress bar
4. Click "Start" on Pomodoro timer
5. Wait for timer to complete OR click "Reset" then manually test completion

**Expected Results**:
- Timer starts counting down
- On completion:
  - Energy decreases by 5 points
  - Progress increases by 5%
  - Break suggestion appears
  - Timer resets to 25:00

### 2. Energy Depletion Test (2 minutes)

**Steps**:
1. Manually set your energy to 5 in database:
   ```sql
   UPDATE student_profile SET energy = 5 WHERE id = YOUR_STUDENT_ID;
   ```
2. Start a course session
3. Complete one Pomodoro (or use browser console to trigger):
   ```javascript
   window.pomodoroTimer.complete()
   ```

**Expected Results**:
- Energy drops to 0
- Modal popup appears: "Energy Depleted!"
- Course content overlay appears (blocked)
- Pomodoro start button is disabled
- "Go to Games" button is visible

### 3. Energy Blocking Test (1 minute)

**Steps**:
1. Set energy to 0 in database:
   ```sql
   UPDATE student_profile SET energy = 0 WHERE id = YOUR_STUDENT_ID;
   ```
2. Try to access course session: `http://127.0.0.1:8000/course/{id}/session`

**Expected Results**:
- Redirected to course detail page
- Error flash message: "Your energy is depleted! Play mini games to restore it or wait 30 minutes for auto-refill."
- Cannot access course session

### 4. Auto-Refill Test (2 minutes)

**Steps**:
1. Set energy to 50 and last update to 1 hour ago:
   ```sql
   UPDATE student_profile 
   SET energy = 50, 
       last_energy_update = DATE_SUB(NOW(), INTERVAL 1 HOUR) 
   WHERE id = YOUR_STUDENT_ID;
   ```
2. Refresh any page or check energy

**Expected Results**:
- Energy should be 52 (2 points refilled: 60 minutes / 30 = 2)
- `last_energy_update` updated to current time

### 5. Course Progress Test (3 minutes)

**Steps**:
1. Start a course session with fresh progress (0%)
2. Complete 4 Pomodoros (use console if needed):
   ```javascript
   for(let i = 0; i < 4; i++) {
       await window.pomodoroTimer.complete();
   }
   ```

**Expected Results**:
- Progress bar shows 20% (4 × 5%)
- Course status changes to "IN_PROGRESS"
- Energy decreases by 20 points (4 × 5)

### 6. Mini Game Energy Restoration (3 minutes)

**Steps**:
1. Set energy to 0
2. Click "Play Mini Games" link
3. Complete any mini game
4. Return to course

**Expected Results**:
- Energy restored (amount depends on mini game)
- Can access course session again
- Energy bar reflects new value

## Browser Console Testing

### Quick Energy Check
```javascript
// Check current energy
fetch('/course/3/session/energy-check')
    .then(r => r.json())
    .then(d => console.log('Energy:', d.energy));
```

### Manual Energy Depletion
```javascript
// Deplete 5 energy
fetch('/course/3/session/deplete-energy', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({amount: 5})
})
.then(r => r.json())
.then(d => console.log('New energy:', d.energy));
```

### Manual Progress Update
```javascript
// Update progress (10 Pomodoros = 50%)
fetch('/course/3/session/update-progress', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({pomodoroCount: 10})
})
.then(r => r.json())
.then(d => console.log('Progress:', d.progress + '%'));
```

### Trigger Pomodoro Completion
```javascript
// Complete current Pomodoro
if (window.pomodoroTimer) {
    window.pomodoroTimer.complete();
}
```

## Database Queries for Testing

### Check Student Energy
```sql
SELECT id, first_name, energy, last_energy_update 
FROM student_profile 
WHERE id = YOUR_STUDENT_ID;
```

### Check Course Progress
```sql
SELECT id, course_name, progress, status 
FROM course 
WHERE id = YOUR_COURSE_ID;
```

### Reset Energy to Full
```sql
UPDATE student_profile 
SET energy = 100, last_energy_update = NOW() 
WHERE id = YOUR_STUDENT_ID;
```

### Reset Course Progress
```sql
UPDATE course 
SET progress = 0, status = 'NOT_STARTED' 
WHERE id = YOUR_COURSE_ID;
```

### Simulate 2 Hours Passed (for auto-refill)
```sql
UPDATE student_profile 
SET last_energy_update = DATE_SUB(NOW(), INTERVAL 2 HOUR) 
WHERE id = YOUR_STUDENT_ID;
```

## Visual Verification Checklist

### Course Detail Page
- [ ] "Start Course Session" button is visible and prominent
- [ ] Energy bar is displayed (if student)
- [ ] Button is green with play icon

### Course Session Page
- [ ] Pomodoro timer displays correctly (25:00)
- [ ] Energy bar is visible and shows current energy
- [ ] Course progress bar is visible
- [ ] All controls (Start, Pause, Resume, Reset) work
- [ ] Energy bar color changes based on level:
  - Green: 60-100%
  - Yellow: 30-59%
  - Orange: 10-29%
  - Red: 0-9%

### Energy Depletion State
- [ ] Modal appears with "Energy Depleted!" message
- [ ] Course content has dark overlay
- [ ] "Go to Games" button is visible
- [ ] Pomodoro start button is disabled

### After Pomodoro Completion
- [ ] Energy bar decreases by 5 points
- [ ] Progress bar increases by 5%
- [ ] Break suggestion appears
- [ ] Timer resets to 25:00

## Common Issues & Solutions

### Issue: Energy not depleting
**Solution**: Check browser console for errors. Verify `depleteEnergyUrl` is set in template.

### Issue: Auto-refill not working
**Solution**: Verify `last_energy_update` field exists in database. Check that field is being updated.

### Issue: Progress not updating
**Solution**: Check `updateProgressUrl` is set. Verify course has `progress` field.

### Issue: Modal not appearing
**Solution**: Check Bootstrap is loaded. Verify modal HTML exists in template.

### Issue: Can still access course with 0 energy
**Solution**: Clear cache. Check controller blocking logic is in place.

## Performance Testing

### Load Test
1. Open 3 browser tabs with different course sessions
2. Start Pomodoro in all tabs
3. Verify all timers work independently
4. Complete Pomodoros in different tabs
5. Verify energy depletes correctly across all sessions

### Concurrent Updates
1. Open course session in 2 tabs
2. Complete Pomodoro in tab 1
3. Refresh tab 2
4. Verify energy and progress are synchronized

## Success Criteria

All tests pass when:
- ✅ Pomodoro completion depletes energy by 5
- ✅ Course progress increases by 5% per Pomodoro
- ✅ Energy 0 blocks course access
- ✅ Modal appears when energy depletes
- ✅ Auto-refill works (1 point per 30 min)
- ✅ Mini games restore energy
- ✅ Visual feedback is clear and immediate
- ✅ No JavaScript errors in console
- ✅ No PHP errors in logs

## Reporting Issues

If you encounter issues, provide:
1. Browser console errors (F12 → Console)
2. Network tab showing failed requests (F12 → Network)
3. PHP error logs (check Symfony profiler)
4. Steps to reproduce
5. Expected vs actual behavior
