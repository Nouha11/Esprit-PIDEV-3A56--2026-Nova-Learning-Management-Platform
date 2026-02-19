# Dark Mode Compatibility for Rewards

## Overview
Updated all reward-related pages to be fully compatible with both light and dark themes using Bootstrap's `data-bs-theme` attribute.

---

## Files Updated

### 1. templates/front/game/my_rewards.html.twig
**My Rewards Page** - Main rewards collection page

### 2. templates/front/game/reward_show.html.twig
**Reward Details Page** - Individual reward view

### 3. templates/front/game/show.html.twig
**Game Details Page** - Game page with reward listings

---

## Dark Mode Styling Strategy

### Color Adjustments

#### Achievement Cards (Green Theme)
**Light Mode:**
- Background: White → Light Green gradient
- Border: #28a745 (green)
- Text: #28a745 (green)

**Dark Mode:**
- Background: Dark → Dark Green gradient (#1a1a1a → #1a3a1f)
- Border: #20c997 (teal/cyan green)
- Text: #20c997 (brighter for contrast)
- Shadow: Enhanced glow effect

#### Badge Cards (Gold Theme)
**Light Mode:**
- Background: White → Light Yellow gradient
- Border: #ffc107 (gold)
- Text: #ff9800 (orange)

**Dark Mode:**
- Background: Dark → Dark Yellow gradient (#1a1a1a → #2a2416)
- Border: #ff9800 (orange)
- Text: #ffc107 (brighter gold)
- Shine: Reduced opacity (0.1 vs 0.3)

#### Bonus Cards (Blue Theme)
**Light Mode:**
- Background: White → Light Blue gradient
- Border: #17a2b8 (blue)
- Icon: #17a2b8 → #138496 gradient

**Dark Mode:**
- Background: Dark → Dark Blue gradient (#1a1a1a → #162a2e)
- Border: #20c9e3 (cyan)
- Icon: #20c9e3 → #17a2b8 gradient (brighter)

---

## CSS Selectors Used

All dark mode styles use the attribute selector:
```css
[data-bs-theme="dark"] .element {
    /* dark mode styles */
}
```

This matches Bootstrap 5.3+ dark mode implementation.

---

## Specific Adjustments

### Cards
```css
/* Hover shadows */
Light: 0 8px 25px rgba(0,0,0,0.15)
Dark:  0 8px 25px rgba(255,255,255,0.1)

/* Card backgrounds */
Light: #ffffff
Dark:  rgba(255,255,255,0.05)

/* Card borders */
Light: Original color
Dark:  rgba(255,255,255,0.1)
```

### Text Colors
```css
/* Muted text */
Light: Bootstrap default
Dark:  rgba(255,255,255,0.6) or rgba(255,255,255,0.7)

/* Section titles */
Light: #2d3748
Dark:  #e2e8f0

/* Lead text */
Light: Bootstrap default
Dark:  rgba(255,255,255,0.9)
```

### Alerts
```css
/* Alert backgrounds */
Light: Bootstrap defaults
Dark:  rgba(color, 0.2) with rgba(color, 0.3) border

/* Alert text colors */
Dark mode uses brighter versions:
- Info: #20c9e3
- Warning: #ffc107
- Success: #20c997
- Danger: #ff6b6b
- Secondary: #adb5bd
```

### Badges
```css
/* Badge colors in dark mode */
bg-success: #20c997
bg-warning: #ff9800 (with black text)
bg-info: #20c9e3 (with black text)
bg-primary: #667eea
```

### Borders & Separators
```css
/* Section headers */
Light: 3px solid #e2e8f0
Dark:  3px solid rgba(255,255,255,0.1)

/* Card borders */
Light: Original theme colors
Dark:  50% opacity of theme colors
```

### Icons & Images
```css
/* Icon shadows */
Light: rgba(0,0,0,0.1)
Dark:  rgba(255,255,255,0.1)

/* Icon backgrounds */
Achievement: Enhanced glow in dark mode
Badge: Enhanced glow in dark mode
Bonus: Brighter gradient in dark mode
```

---

## Animation Adjustments

### Badge Shine Effect
**Light Mode:**
```css
background: linear-gradient(45deg, 
    transparent, 
    rgba(255, 255, 255, 0.3), 
    transparent
);
```

**Dark Mode:**
```css
background: linear-gradient(45deg, 
    transparent, 
    rgba(255, 255, 255, 0.1), 
    transparent
);
```
Reduced opacity to prevent overwhelming effect in dark mode.

### Pulse Animation
No changes needed - works well in both modes.

---

## Page-Specific Changes

### My Rewards Page (my_rewards.html.twig)

**Elements Updated:**
- Achievement cards with ribbon
- Badge cards with shine
- Bonus cards with icon wrapper
- Section headers
- Card footers
- Text colors
- Hover effects

**Key Features:**
- Gradient backgrounds adjusted for dark mode
- Border colors use brighter variants
- Shadow effects use white instead of black
- Text maintains readability

### Reward Details Page (reward_show.html.twig)

**Elements Updated:**
- Main reward card
- Alert boxes (info, warning, success, secondary)
- Card headers with gradients
- Button hover effects
- Border colors
- Badge colors
- Card footers

**Key Features:**
- Gradient header maintains visibility
- Alert boxes have semi-transparent backgrounds
- All text remains readable
- Buttons maintain contrast

### Game Details Page (show.html.twig)

**Elements Updated:**
- Reward item cards
- Card headers (primary, info)
- Alert boxes
- Badge colors
- Text colors (info, success, warning, primary)
- Sticky sidebar card

**Key Features:**
- Reward items have subtle background
- Card headers use semi-transparent theme colors
- Badges use brighter colors for visibility
- Hover effects work smoothly

---

## Testing Checklist

### Light Mode
- [x] Achievement cards display correctly
- [x] Badge cards display correctly
- [x] Bonus cards display correctly
- [x] Text is readable
- [x] Hover effects work
- [x] Animations smooth
- [x] Colors match design

### Dark Mode
- [x] Achievement cards visible with good contrast
- [x] Badge cards visible with good contrast
- [x] Bonus cards visible with good contrast
- [x] Text is readable (not too dim)
- [x] Hover effects visible
- [x] Animations not overwhelming
- [x] Colors adjusted appropriately
- [x] Borders visible but subtle
- [x] Shadows use white glow
- [x] Gradients maintain depth

### Both Modes
- [x] Smooth transition when switching themes
- [x] No layout shifts
- [x] Icons remain visible
- [x] Badges readable
- [x] Buttons maintain contrast
- [x] Links distinguishable
- [x] Alerts clearly visible

---

## Browser Compatibility

Works with:
- Chrome/Edge (Chromium)
- Firefox
- Safari
- Any browser supporting CSS attribute selectors

Requires:
- Bootstrap 5.3+ for `data-bs-theme` support
- Modern CSS support (gradients, rgba, transforms)

---

## Color Palette Reference

### Light Mode Colors
| Element | Color | Usage |
|---------|-------|-------|
| Achievement | #28a745 | Border, text |
| Badge | #ffc107 | Border, background |
| Bonus | #17a2b8 | Border, icon |
| Text | #2d3748 | Headings |
| Muted | Bootstrap | Secondary text |

### Dark Mode Colors
| Element | Color | Usage |
|---------|-------|-------|
| Achievement | #20c997 | Border, text (brighter) |
| Badge | #ff9800 | Border, text (brighter) |
| Bonus | #20c9e3 | Border, icon (brighter) |
| Text | #e2e8f0 | Headings |
| Muted | rgba(255,255,255,0.6) | Secondary text |

### Background Gradients

**Light Mode:**
- Achievement: #ffffff → #f0fff4
- Badge: #ffffff → #fffbf0
- Bonus: #ffffff → #f0f9ff

**Dark Mode:**
- Achievement: #1a1a1a → #1a3a1f
- Badge: #1a1a1a → #2a2416
- Bonus: #1a1a1a → #162a2e

---

## Accessibility Notes

### Contrast Ratios
All text colors in dark mode maintain WCAG AA compliance:
- Headings: High contrast (white on dark)
- Body text: Medium-high contrast
- Muted text: Minimum AA compliance
- Links: Distinguishable from text

### Visual Indicators
- Hover states clearly visible in both modes
- Focus states inherit from Bootstrap
- Active states maintain visibility
- Disabled states appropriately dimmed

---

## Future Enhancements

Potential improvements:
- Add theme-specific images/icons
- Implement custom color schemes
- Add user preference storage
- Create theme preview toggle
- Add high contrast mode option
- Implement reduced motion preferences

---

## Maintenance Notes

When adding new reward types or cards:
1. Define light mode styles first
2. Add dark mode overrides using `[data-bs-theme="dark"]`
3. Test both modes thoroughly
4. Ensure text contrast meets WCAG standards
5. Adjust shadow colors (black → white)
6. Use brighter colors for dark mode
7. Test hover and active states

---

**Last Updated**: February 18, 2026
**Status**: Fully Compatible ✅
**Bootstrap Version**: 5.3+
