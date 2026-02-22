# Energy System Documentation

## Overview
The energy system tracks student energy levels that decrease during study sessions and can be restored by playing mini games.

## Components

### 1. Energy Bar Widget
**Location**: `templates/components/energy_bar_widget.html.twig`

A reusable, animated energy bar component with:
- **Visual Design**:
  - Gradient fill that changes color based on energy level
  - Animated shine effect
  - Glow overlay for depth
  - Pulse animations for low energy warnings
  
- **Color Coding**:
  - 60-100%: Green gradient (healthy)
  - 30-59%: Yellow gradient (moderate)
  - 10-29%: Orange gradient (low - pulsing warning)
  - 0-9%: Red gradient (critical - pulsing danger)

- **Features**:
  - Dark/light theme compatible
  - Three sizes: small, normal, large
  - Optional label display
  - Automatic alerts for low energy
  - Smooth animations for energy changes
  - Restoration animation effect

### 2. Energy Monitor Service
**Location**: `src/Service/StudySession/EnergyMonitorService.php`

Handles all energy-related operations:
- `getCurrentEnergy(User $user)`: Get current energy level (0-100)
- `isEnergyDepleted(User $user)`: Check if energy is at 0
- `depleteEnergy(User $user, int $amount)`: Reduce energy (for studying)
- `restoreEnergy(User $user, int $amount)`: Increase energy (from mini games)

### 3. Student Profile Entity
**Location**: `src/Entity/users/StudentProfile.php`

Stores energy level:
- Field: `energy` (integer, default: 100)
- Range: 0-100 (automatically clamped)
- Getter: `getEnergy()`
- Setter: `setEnergy(int $energy)`

## Usage

### Including the Energy Bar Widget

```twig
{# Basic usage #}
{% include 'components/energy_bar_widget.html.twig' with {
    'currentEnergy': currentEnergy
} %}

{# With options #}
{% include 'components/energy_bar_widget.html.twig' with {
    'currentEnergy': currentEnergy,
    'showLabel': false,
    'size': 'large'
} %}
```

**Parameters**:
- `currentEnergy` (required): Current energy value (0-100)
- `showLabel` (optional, default: true): Show "Energy Level" label
- `size` (optional, default: 'normal'): Size variant ('small', 'normal', 'large')

### JavaScript API

**Update Energy Bar**:
```javascript
// Update energy bar to new value
window.updateEnergyBar(75);
```

**Listen for Energy Updates**:
```javascript
window.addEventListener('energyUpdated', function(event) {
    console.log('New energy:', event.detail.energy);
});
```

**Game Completion Event**:
```javascript
// Dispatched when a game is completed with energy restoration
window.addEventListener('gameCompleted', function(event) {
    console.log('Energy restored:', event.detail.energyRestored);
    console.log('Current energy:', event.detail.currentEnergy);
});
```

## Integration with Mini Games

### Game Configuration
Mini games that restore energy must have:
- **Category**: `MINI_GAME`
- **Energy Points**: > 0 (amount to restore)

### Automatic Energy Restoration
When a mini game is completed:
1. `GameController::complete()` checks if game is a MINI_GAME with energyPoints
2. Calls `EnergyMonitorService::restoreEnergy()`
3. Returns energy data in JSON response
4. Frontend dispatches `gameCompleted` event
5. Energy bar updates automatically with animation

### Example Mini Games
- **Stretch Break**: Restores 10 energy
- **Eye Rest (20-20-20)**: Restores 15 energy
- **Hydration Break**: Restores 20 energy

## Energy Depletion (Study Sessions)

Energy decreases during study sessions:
```php
// In study session controller
$this->energyMonitorService->depleteEnergy($user, 10);
```

**Recommended Depletion Rates**:
- Short session (15-30 min): -5 energy
- Medium session (30-60 min): -10 energy
- Long session (60+ min): -15 energy

## Alerts and Warnings

The energy bar automatically shows alerts:
- **≤ 20 energy**: Yellow warning - "Low energy! Play a mini game to restore it."
- **0 energy**: Red danger - "Energy depleted! Take a break or play a mini game."

## Pages with Energy Bar

Currently integrated on:
- Course Session View (`/course/{id}/session`)
- Can be added to any page by including the widget

## Styling and Themes

The energy bar is fully compatible with Bootstrap's dark/light themes:
- Automatically adjusts colors and contrast
- Uses CSS custom properties for easy customization
- Smooth transitions between theme changes

## Future Enhancements

Potential improvements:
1. **Energy Decay**: Automatic energy decrease over time during study
2. **Energy Boosts**: Temporary energy multipliers from achievements
3. **Energy History**: Track energy patterns over time
4. **Notifications**: Browser notifications when energy is low
5. **Energy Leaderboard**: Compare energy management with other students
6. **Custom Thresholds**: User-configurable low energy warnings

## Technical Notes

- Energy values are always clamped between 0 and 100
- Database updates are immediate (no caching)
- Energy restoration is additive (current + restored, max 100)
- All animations use CSS for performance
- Widget is responsive and mobile-friendly

## Troubleshooting

**Energy bar not updating after game**:
- Check that game has `category = 'MINI_GAME'`
- Verify `energyPoints > 0` in game settings
- Check browser console for JavaScript errors
- Ensure `window.updateEnergyBar` function exists

**Energy not persisting**:
- Verify StudentProfile entity has energy field
- Check database migration was run
- Ensure EntityManager is flushing changes

**Visual issues**:
- Clear browser cache
- Check for CSS conflicts
- Verify Bootstrap theme is properly loaded
