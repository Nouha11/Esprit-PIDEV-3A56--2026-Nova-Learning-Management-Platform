# Certificate Full Page Fix - Implementation

## Issues Fixed

1. ✅ Certificate now fills the entire A4 landscape page (no white margins)
2. ✅ Added actual NOVA logo from site assets
3. ✅ Improved border and decoration positioning
4. ✅ Enhanced PDF rendering quality

---

## Changes Made

### 1. Template Updates (`templates/front/game/certificate.html.twig`)

#### Full Page Layout
```css
@page {
    size: A4 landscape;
    margin: 0;
}

body {
    width: 297mm;
    height: 210mm;
    margin: 0;
    padding: 0;
    background: white;
}

.certificate-container {
    width: 297mm;
    height: 210mm;
    padding: 20mm;
}
```

#### Logo Integration
- Replaced emoji icon (🏆) with actual site logo
- Using: `public/assets/images/logo.svg`
- Logo size: 80px x 80px
- Uses `absolute_url(asset())` for proper path resolution in PDF

```html
<div class="certificate-logo">
    <img src="{{ absolute_url(asset('assets/images/logo.svg')) }}" alt="NOVA Logo">
</div>
```

#### Border Improvements
- Added double border effect using ::before and ::after pseudo-elements
- Outer border: 3px solid blue (#667eea)
- Inner border: 1px solid light gray (#e2e8f0)
- Decorative corners positioned with mm units for precision

#### Watermark Enhancement
- Reduced opacity from 0.05 to 0.03 for subtlety
- Added letter-spacing for better visual effect
- Positioned absolutely in center with rotation

---

### 2. PDF Generation Updates (`src/Service/game/CertificateService.php`)

#### Enhanced Options
```php
[
    'page-size' => 'A4',
    'orientation' => 'Landscape',
    'margin-top' => '0mm',
    'margin-right' => '0mm',
    'margin-bottom' => '0mm',
    'margin-left' => '0mm',
    'encoding' => 'UTF-8',
    'enable-local-file-access' => true,
    'dpi' => 300,                      // High quality (was 96)
    'image-dpi' => 300,                // High quality images
    'image-quality' => 100,            // Maximum image quality
    'disable-smart-shrinking' => true, // Prevent auto-shrinking
    'zoom' => 1.0,                     // No zoom
    'viewport-size' => '1280x1024',    // Consistent viewport
]
```

#### Key Improvements
- **DPI increased to 300**: Professional print quality
- **Margins set to '0mm'**: Ensures full page coverage
- **Smart shrinking disabled**: Prevents wkhtmltopdf from auto-adjusting
- **Viewport size specified**: Consistent rendering across systems

---

## Technical Details

### Page Dimensions
- A4 Landscape: 297mm x 210mm (11.69" x 8.27")
- Content padding: 20mm on all sides
- Usable area: 257mm x 170mm

### Logo Path Resolution
- Uses Symfony's `asset()` function for proper path
- `absolute_url()` converts to full URL for PDF rendering
- Supports both SVG and PNG formats

### Border Structure
```
┌─────────────────────────────────────┐
│ Outer border (3px blue)             │
│  ┌───────────────────────────────┐  │
│  │ Inner border (1px gray)       │  │
│  │  ┌─────────────────────────┐  │  │
│  │  │ Content area            │  │  │
│  │  │                         │  │  │
│  │  └─────────────────────────┘  │  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

### Decorative Corners
- Size: 80mm x 80mm
- Position: 20mm from edges
- Style: L-shaped borders (2 sides only)
- Color: #667eea (brand blue)

---

## Testing Checklist

- [x] Certificate fills entire page (no white margins)
- [x] Logo displays correctly
- [x] All text is readable
- [x] Reward details show properly
- [x] Borders and decorations align correctly
- [x] PDF quality is high (300 DPI)
- [x] Student name displays
- [x] Date displays correctly
- [x] Reward type badge shows
- [x] Reward name and description visible
- [x] Reward value displays (if applicable)

---

## File Locations

### Modified Files
1. `templates/front/game/certificate.html.twig` - Certificate template
2. `src/Service/game/CertificateService.php` - PDF generation service

### Logo Files Available
- `public/assets/images/logo.svg` (Used in certificate)
- `public/assets/images/Logo.png`
- `public/assets/images/nova_logo.png`
- `public/assets/images/logo-light.svg`
- `public/assets/images/logo-mobile.svg`

---

## Browser vs PDF Rendering

### Important Notes
- HTML/CSS in browser may look different from PDF
- wkhtmltopdf has specific rendering engine
- Always test by downloading actual PDF
- Use absolute URLs for images in PDF context
- Millimeter units (mm) work best for print layouts

### Why Full Page Now Works
1. **@page rule**: Tells PDF renderer exact page size and margins
2. **Explicit dimensions**: Body and container set to exact A4 landscape size
3. **Zero margins**: All margins explicitly set to 0mm
4. **Disabled shrinking**: Prevents auto-adjustment by renderer
5. **High DPI**: Ensures crisp rendering at full size

---

## Troubleshooting

### If logo doesn't show:
- Check file exists at `public/assets/images/logo.svg`
- Verify `enable-local-file-access` is true
- Try using PNG version instead: `logo.png`

### If still not full page:
- Clear browser cache
- Regenerate certificate (don't use cached version)
- Check wkhtmltopdf version (should be 0.12.5+)

### If quality is poor:
- DPI is set to 300 (high quality)
- Image quality is 100%
- Check source logo file quality

---

**Last Updated**: February 18, 2026
**Status**: Full Page Layout ✅ | Logo Integrated ✅
