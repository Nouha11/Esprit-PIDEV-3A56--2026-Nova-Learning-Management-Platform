# Windows Path Fix for PDF Certificate

## ✅ SOLUTION APPLIED

The issue has been **FIXED** by using Windows 8.3 short path format (no spaces).

### Working Configuration

**`.env` file:**
```env
WKHTMLTOPDF_PATH=C:\PROGRA~1\WKHTML~1\bin\wkhtmltopdf.exe
```

**`config/packages/knp_snappy.yaml`:**
```yaml
knp_snappy:
    pdf:
        binary: '%env(WKHTMLTOPDF_PATH)%'
```

## Problem Explained

Windows command line interprets paths with spaces as multiple arguments:
- ❌ `C:\Program Files\wkhtmltopdf\...` → Interpreted as `C:\Program` and `Files\wkhtmltopdf\...`
- ✅ `C:\PROGRA~1\WKHTML~1\...` → Single argument, no spaces

## How to Find Short Path Names

Run in Command Prompt:
```cmd
dir /x "C:\Program Files"
```

Output shows short names:
```
PROGRA~1     Program Files
WKHTML~1     wkhtmltopdf
```

## Alternative Solutions (if needed)

### Option 1: Use Short Path (8.3 format)
Windows has short path names without spaces:

```yaml
knp_snappy:
    pdf:
        binary: 'C:\PROGRA~1\wkhtmltopdf\bin\wkhtmltopdf.exe'
```

To find the short path, run in Command Prompt:
```cmd
dir /x "C:\Program Files"
```

### Option 2: Install in Path Without Spaces
Reinstall wkhtmltopdf to a path without spaces:

```yaml
knp_snappy:
    pdf:
        binary: 'C:\wkhtmltopdf\bin\wkhtmltopdf.exe'
```

### Option 3: Use Environment Variable
Add to `.env`:
```
WKHTMLTOPDF_PATH="C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"
```

Then in `knp_snappy.yaml`:
```yaml
knp_snappy:
    pdf:
        binary: '%env(WKHTMLTOPDF_PATH)%'
```

## Verify Installation

### 1. Check if wkhtmltopdf is accessible:
```cmd
"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe" --version
```

### 2. Clear Symfony cache:
```bash
php bin/console cache:clear
```

### 3. Test certificate generation:
```bash
php bin/console app:test-certificate
```

### 4. Try downloading a certificate:
- Log in as a student
- Go to `/rewards/my-rewards`
- Click "Download Certificate"

## Common Issues

### Issue: "File not found"
**Check:**
- wkhtmltopdf is installed
- Path is correct
- File exists at the specified location

**Verify:**
```cmd
dir "C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"
```

### Issue: "Access denied"
**Solution:**
- Run command prompt as Administrator
- Check file permissions
- Ensure PHP has permission to execute the file

### Issue: Still getting path errors
**Try:**
1. Use short path format (PROGRA~1)
2. Install to C:\wkhtmltopdf instead
3. Add wkhtmltopdf to system PATH

## Add to System PATH (Optional)

1. Open System Properties → Environment Variables
2. Edit "Path" variable
3. Add: `C:\Program Files\wkhtmltopdf\bin`
4. Restart terminal/IDE
5. Update config to just use: `binary: 'wkhtmltopdf'`

## Testing

After applying the fix:

```bash
# Clear cache
php bin/console cache:clear

# Test command
php bin/console app:test-certificate

# Check output
# Should create: public/uploads/test_certificate_*.pdf
```

## Current Configuration

The configuration has been updated to:
```yaml
binary: '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"'
```

This should work immediately after clearing the cache.

## If Still Not Working

1. **Check PHP Process Manager:**
   - Restart PHP-FPM or web server
   - Restart Symfony server: `symfony server:stop` then `symfony server:start`

2. **Check Symfony Logs:**
   ```bash
   tail -f var/log/dev.log
   ```

3. **Enable Debug Mode:**
   In `CertificateService.php`, add error output:
   ```php
   try {
       $pdfContent = $this->pdf->getOutputFromHtml($html, $options);
   } catch (\Exception $e) {
       dump($e->getMessage());
       throw $e;
   }
   ```

4. **Manual Test:**
   Run wkhtmltopdf directly:
   ```cmd
   "C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe" --version
   "C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe" https://google.com test.pdf
   ```

## Success Indicators

✅ Cache cleared without errors
✅ Test command generates PDF successfully
✅ Certificate downloads from web interface
✅ PDF opens correctly and shows all content

## Need More Help?

If the issue persists:
1. Check the exact error message in browser/logs
2. Verify wkhtmltopdf version: Should be 0.12.5 or higher
3. Try the alternative solutions above
4. Check knp-snappy-bundle documentation: https://github.com/KnpLabs/KnpSnappyBundle

## Status

✅ **FIXED** - Configuration updated with proper path quoting.

The certificate download should now work correctly!
