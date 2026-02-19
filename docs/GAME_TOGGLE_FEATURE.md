# Game Active Status Toggle Feature

## Overview
This feature allows admins to activate/deactivate games instantly using Ajax without page reload. When a game is deactivated, it's immediately hidden from the student game list.

## Features

### Admin Side
- **Toggle Button**: Play/Pause icon button in the admin game list
- **Ajax Request**: No page reload required
- **Visual Feedback**: 
  - Loading spinner during request
  - Status badge updates instantly
  - Row fade animation
  - Toast notification with success/error message
- **CSRF Protection**: Secure token validation
- **Real-time Updates**: Status changes reflect immediately

### Student Side
- **Automatic Filtering**: Only active games appear in the game list
- **Access Control**: Inactive games return 404 if accessed directly
- **Seamless Experience**: Students never see deactivated games

## How It Works

### 1. Admin Toggles Game Status

**Location**: `/admin/games`

**Action**: Click the toggle button (play/pause icon)

**Process**:
1. Button shows loading spinner
2. Ajax POST request sent to `/admin/games/{id}/toggle-active`
3. Server toggles `isActive` field in database
4. JSON response returned with new status
5. UI updates without page reload:
   - Status badge changes (Active/Inactive)
   - Button icon changes (pause/play)
   - Row fades in/out
   - Toast notification appears

### 2. Student Game List Updates

**Location**: `/games`

**Query**: Only fetches games where `isActive = true`

**Result**: Deactivated games disappear from the list immediately

## Technical Implementation

### Controller Route

**File**: `src/Controller/Admin/Game/GameAdminController.php`

```php
#[Route('/{id}/toggle-active', name: 'admin_game_toggle_active', methods: ['POST'])]
public function toggleActive(Request $request, Game $game, EntityManagerInterface $entityManager): Response
{
    // Verify CSRF token
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('toggle_active_' . $game->getId(), $token)) {
        return $this->json(['success' => false, 'message' => 'Invalid security token.'], 400);
    }

    // Toggle status
    $newStatus = !$game->isActive();
    $game->setIsActive($newStatus);
    $entityManager->flush();

    // Return JSON response
    return $this->json([
        'success' => true,
        'message' => sprintf('Game "%s" has been %s successfully!', $game->getName(), $newStatus ? 'activated' : 'deactivated'),
        'isActive' => $newStatus,
        'gameId' => $game->getId(),
        'gameName' => $game->getName()
    ]);
}
```

### JavaScript Function

**File**: `templates/admin/game/index.html.twig`

```javascript
function toggleGameStatus(gameId, csrfToken) {
    const toggleBtn = document.querySelector('.btn-toggle-' + gameId);
    const statusBadge = document.querySelector('.status-badge-' + gameId);
    const gameRow = document.getElementById('game-row-' + gameId);
    
    // Show loading state
    toggleBtn.disabled = true;
    toggleBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    // Send Ajax request
    fetch('/admin/games/' + gameId + '/toggle-active', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_token=' + encodeURIComponent(csrfToken)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI based on new status
            updateGameUI(gameId, data.isActive);
            showFlashMessage('success', data.message);
        } else {
            showFlashMessage('danger', data.message);
        }
    })
    .catch(error => {
        showFlashMessage('danger', 'An error occurred while toggling game status.');
    })
    .finally(() => {
        toggleBtn.disabled = false;
    });
}
```

### Student Game Filter

**File**: `src/Controller/Front/Game/GameController.php`

```php
#[Route('', name: 'front_game_index', methods: ['GET'])]
public function index(Request $request): Response
{
    $queryBuilder = $this->gameRepository->createQueryBuilder('g')
        ->where('g.isActive = :active')
        ->setParameter('active', true)
        ->orderBy('g.createdAt', 'DESC');

    $pagination = $this->paginator->paginate($queryBuilder, $request->query->getInt('page', 1), 6);

    return $this->render('front/game/index.html.twig', ['games' => $pagination]);
}
```

## UI Components

### Toggle Button States

**Active Game** (can be deactivated):
```html
<button class="btn btn-sm btn-secondary" title="Deactivate">
    <i class="bi bi-pause-circle"></i>
</button>
```

**Inactive Game** (can be activated):
```html
<button class="btn btn-sm btn-success" title="Activate">
    <i class="bi bi-play-circle"></i>
</button>
```

### Status Badge

**Active**:
```html
<span class="badge bg-success">Active</span>
```

**Inactive**:
```html
<span class="badge bg-secondary">Inactive</span>
```

### Flash Notification

**Success**:
```html
<div class="alert alert-success alert-dismissible fade show">
    Game "Puzzle Master" has been deactivated successfully!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

**Error**:
```html
<div class="alert alert-danger alert-dismissible fade show">
    Invalid security token.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

## Animations

### Row Fade Effect

**Deactivation**:
```css
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0.5; }
}
```

**Activation**:
```css
@keyframes fadeIn {
    from { opacity: 0.5; }
    to { opacity: 1; }
}
```

### Notification Slide-In

```css
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
```

## Security

### CSRF Protection

Each toggle button has a unique CSRF token:
```twig
{{ csrf_token('toggle_active_' ~ game.id) }}
```

Server validates the token before processing:
```php
if (!$this->isCsrfTokenValid('toggle_active_' . $game->getId(), $token)) {
    return $this->json(['success' => false, 'message' => 'Invalid security token.'], 400);
}
```

### Access Control

- Only admins can access the toggle route (`#[IsGranted('ROLE_ADMIN')]`)
- Students cannot see or access inactive games
- Direct access to inactive games returns 404

## Testing

### Test Deactivation

1. Go to `/admin/games`
2. Find an active game
3. Click the pause icon button
4. Verify:
   - ✅ Button shows loading spinner
   - ✅ Status badge changes to "Inactive"
   - ✅ Button icon changes to play
   - ✅ Row fades slightly
   - ✅ Success notification appears
   - ✅ Notification auto-dismisses after 5 seconds

5. Open `/games` in another tab (as student)
6. Verify:
   - ✅ Deactivated game is not visible

### Test Activation

1. Go to `/admin/games`
2. Find an inactive game
3. Click the play icon button
4. Verify:
   - ✅ Button shows loading spinner
   - ✅ Status badge changes to "Active"
   - ✅ Button icon changes to pause
   - ✅ Row fades back to full opacity
   - ✅ Success notification appears

5. Refresh `/games` (as student)
6. Verify:
   - ✅ Activated game is now visible

### Test Error Handling

1. Modify CSRF token in browser console
2. Click toggle button
3. Verify:
   - ✅ Error notification appears
   - ✅ Button returns to original state
   - ✅ Status doesn't change

### Test Direct Access

1. Deactivate a game
2. Try to access `/games/{id}` directly (as student)
3. Verify:
   - ✅ 404 error page appears

## Use Cases

### Maintenance Mode
Temporarily deactivate a game for bug fixes without deleting it.

### Seasonal Games
Activate/deactivate games based on events or seasons.

### Content Rotation
Rotate available games to keep content fresh.

### Testing
Deactivate games during testing without affecting production data.

### Gradual Rollout
Activate new games gradually for specific user groups.

## Troubleshooting

### Toggle Button Not Working

**Symptom**: Clicking button does nothing

**Solutions**:
- Check browser console for JavaScript errors
- Verify CSRF token is present
- Check network tab for failed requests
- Ensure user has ROLE_ADMIN

### Status Not Updating

**Symptom**: Button works but status doesn't change

**Solutions**:
- Check database connection
- Verify entity manager is flushing changes
- Check for validation errors
- Review server logs

### Games Still Visible to Students

**Symptom**: Deactivated games appear in student list

**Solutions**:
- Clear Symfony cache: `php bin/console cache:clear`
- Verify query has `where('g.isActive = :active')`
- Check database value is actually false
- Refresh student page (Ctrl+F5)

### Flash Messages Not Appearing

**Symptom**: No notification after toggle

**Solutions**:
- Check `#ajax-flash-messages` div exists
- Verify JavaScript `showFlashMessage()` function
- Check CSS positioning
- Review browser console for errors

## Future Enhancements

- [ ] Bulk activate/deactivate multiple games
- [ ] Schedule activation/deactivation times
- [ ] Activity log for status changes
- [ ] Email notifications to admins
- [ ] Reason field for deactivation
- [ ] Reactivation reminders
- [ ] Student notification when favorite game is reactivated

## Related Files

- `src/Controller/Admin/Game/GameAdminController.php` - Toggle route
- `src/Controller/Front/Game/GameController.php` - Student filtering
- `templates/admin/game/index.html.twig` - Admin UI
- `templates/front/game/index.html.twig` - Student UI
- `src/Entity/Gamification/Game.php` - Game entity
