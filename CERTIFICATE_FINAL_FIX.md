# Certificate Final Fix - Footer & Second Page

## Issues Fixed

1. ✅ Footer now positioned at the bottom of the certificate
2. ✅ Second empty page eliminated
3. ✅ Content properly distributed across the page
4. ✅ All spacing optimized

---

## Solution: Flexbox Layout

### Container Structure
Changed the certificate container to use flexbox with vertical layout:

```css
.certificate-container {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 210mm;
    overflow: hidden;
}
```

This creates three sections:
1. **Header** (flex-shrink: 0) - Fixed at top
2. **Body** (flex: 1) - Expands to fill available space
3. **Footer** (flex-shrink: 0) - Fixed at bottom

---

## CSS Changes Made

### 1. Container
```css
.certificate-container {
    display: flex;                    /* NEW */
    flex-direction: column;           /* NEW */
    justify-content: space-between;   /* NEW */
    overflow: hidden;                 /* Prevents second page */
}
```

### 2. Header
```css
.certificate-header {
    flex-shrink: 0;  /* NEW - Prevents shrinking */
}
```

### 3. Body
```css
.certificate-body {
    flex: 1;                    /* NEW - Expands to fill space */
    display: flex;              /* NEW */
    flex-direction: column;     /* NEW */
    justify-content: center;    /* NEW - Centers content vertically */
}
```

### 4. Footer
```css
.certificate-footer {
    flex-shrink: 0;    /* NEW - Prevents shrinking */
    margin-top: 0;     /* Changed from 10px */
}
```

---

## Additional Spacing Optimizations

### Reduced Margins
- Achievement icon: 4px → 3px
- Achievement text: 6px → 5px
- Reward details: 8px → 6px
- Presented-to: Added margin-top: 0

### Smaller Elements
- Icon: 24px → 22px font size

---

## How It Works

### Before (Problems)
```
┌─────────────────────────┐
│ Header                  │
│ Body (fixed height)     │
│ Footer (fixed position) │
│                         │ ← Extra space
│                         │
└─────────────────────────┘
Page 2 (empty)
```

### After (Fixed)
```
┌─────────────────────────┐
│ Header (flex-shrink: 0) │
├─────────────────────────┤
│                         │
│ Body (flex: 1)          │
│ - Centered content      │
│                         │
├─────────────────────────┤
│ Footer (flex-shrink: 0) │
└─────────────────────────┘
No Page 2!
```

---

## Benefits

### 1. Footer Always at Bottom
- Uses `justify-content: space-between`
- Footer pushed to bottom edge
- Consistent positioning regardless of content

### 2. No Second Page
- `overflow: hidden` on body and container
- Content constrained to 210mm height
- Flexbox prevents overflow

### 3. Responsive Content
- Body expands/contracts as needed
- Content stays centered vertically
- Adapts to different content lengths

### 4. Clean Layout
- Header at top
- Content in middle (centered)
- Footer at bottom
- Professional appearance

---

## Page Dimensions

### A4 Landscape
- Width: 297mm
- Height: 210mm
- Padding: 10mm all sides
- Usable area: 277mm x 190mm

### Layout Distribution
- Header: ~40mm (fixed)
- Body: ~140mm (flexible)
- Footer: ~10mm (fixed)
- Total: 190mm (fits perfectly)

---

## Testing Results

### Before
- ❌ Footer in middle of page
- ❌ Second empty page generated
- ❌ Wasted white space
- ❌ Unprofessional appearance

### After
- ✅ Footer at bottom edge
- ✅ Single page only
- ✅ Efficient space usage
- ✅ Professional layout

---

## Technical Details

### Flexbox Properties Used

**Container:**
- `display: flex` - Enables flexbox
- `flex-direction: column` - Vertical stacking
- `justify-content: space-between` - Pushes items apart

**Header:**
- `flex-shrink: 0` - Maintains size

**Body:**
- `flex: 1` - Expands to fill space
- `display: flex` - Nested flexbox
- `justify-content: center` - Centers content

**Footer:**
- `flex-shrink: 0` - Maintains size

### Browser Compatibility
- ✅ wkhtmltopdf (supports flexbox)
- ✅ Modern browsers
- ✅ PDF rendering engines

---

## Maintenance Notes

### If Footer Too Close to Content
Increase body bottom margin:
```css
.certificate-body {
    margin-bottom: 5mm;
}
```

### If Footer Too Far from Bottom
Reduce container padding:
```css
.certificate-container {
    padding: 8mm;  /* Currently 10mm */
}
```

### If Content Overflows
1. Reduce font sizes further
2. Decrease padding in reward-details
3. Shorten achievement text
4. Remove reward description

---

## Files Modified

1. `templates/front/game/certificate.html.twig`
   - Added flexbox to container
   - Added flex properties to sections
   - Reduced spacing throughout
   - Optimized for single page

---

## Summary

The certificate now uses a modern flexbox layout that:
- Positions the footer at the absolute bottom
- Prevents any second page generation
- Centers content vertically in the middle section
- Maintains professional appearance
- Works reliably across PDF renderers

**Result:** Perfect single-page certificate with footer at bottom!

---

**Last Updated:** February 18, 2026
**Status:** Footer Fixed ✅ | Single Page ✅
