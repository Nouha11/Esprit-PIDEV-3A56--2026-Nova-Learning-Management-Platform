# How to Enable GD Extension in XAMPP (Windows)

## Quick Fix (Already Applied)

The 2FA system now uses SVG QR codes instead of PNG, which **doesn't require the GD extension**. The system should work immediately without any PHP configuration changes.

## Optional: Enable GD Extension

If you want to enable the GD extension for other purposes (image manipulation, etc.), follow these steps:

### Step 1: Locate php.ini

1. Open XAMPP Control Panel
2. Click "Config" button next to Apache
3. Select "PHP (php.ini)"

### Step 2: Enable GD Extension

1. Find this line in php.ini:
   ```ini
   ;extension=gd
   ```

2. Remove the semicolon (;) to uncomment it:
   ```ini
   extension=gd
   ```

3. Save the file

### Step 3: Restart Apache

1. In XAMPP Control Panel, click "Stop" for Apache
2. Wait a few seconds
3. Click "Start" for Apache

### Step 4: Verify GD is Enabled

Run this command in your terminal:

```bash
php -m | findstr -i gd
```

You should see:
```
gd
```

Or create a PHP info file:

```php
<?php
phpinfo();
```

Look for the "gd" section.

## Alternative: Use SVG QR Codes (Recommended)

SVG QR codes have several advantages:

- **No dependencies**: Don't require GD extension
- **Better quality**: Vector-based, scale perfectly
- **Smaller file size**: More efficient than PNG
- **Browser support**: All modern browsers support SVG

The NOVA 2FA system now uses SVG by default, so no configuration is needed!

## Troubleshooting

### GD Extension Still Not Working

1. Make sure you edited the correct php.ini file:
   - Check `C:\xampp\php\php.ini`
   - Not `C:\xampp\apache\bin\php.ini`

2. Verify the extension file exists:
   - Check `C:\xampp\php\ext\php_gd.dll`

3. Check for errors in Apache error log:
   - `C:\xampp\apache\logs\error.log`

### SVG QR Codes Not Displaying

1. Clear browser cache
2. Check browser console for errors
3. Verify the data URI is being generated correctly

## Current Configuration

The NOVA platform is configured to use:
- **Writer**: SvgWriter (no GD required)
- **Format**: SVG (vector graphics)
- **Size**: 300x300 pixels
- **Error Correction**: High level

This provides the best balance of compatibility, quality, and ease of use.
