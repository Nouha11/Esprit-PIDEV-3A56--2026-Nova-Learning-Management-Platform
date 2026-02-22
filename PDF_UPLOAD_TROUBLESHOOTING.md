# PDF Upload Troubleshooting Guide

## Current Issue: "Failed to upload file: NOVA_Git_Workflow_Guide.pdf"

The error message has been improved to show more details. When you try uploading again, you should see a more specific error message like:

```
Failed to upload file: NOVA_Git_Workflow_Guide.pdf - Error: [specific error message]
```

## Common Issues and Solutions

### 1. Directory Permissions

**Problem:** The uploads directory doesn't exist or isn't writable.

**Solution:**
```bash
# Create the directory
mkdir -p public/uploads/resources

# Make it writable (Windows)
icacls public\uploads\resources /grant Everyone:F

# Or on Linux/Mac
chmod 777 public/uploads/resources
```

### 2. PHP Upload Limits

**Problem:** File size exceeds PHP's upload limits.

**Check your PHP settings:**
```bash
php -i | findstr upload_max_filesize
php -i | findstr post_max_size
```

**Solution:** Edit `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20
```

Then restart your web server (Apache/Nginx).

### 3. Database Unique Constraint

**Problem:** The `storedFilename` already exists in the database.

**Solution:** The filename includes `uniqid()` which should prevent this, but if it happens:
```sql
-- Check for duplicate filenames
SELECT storedFilename, COUNT(*) 
FROM resource 
GROUP BY storedFilename 
HAVING COUNT(*) > 1;

-- If found, delete duplicates or update them
```

### 4. File Extension Issue

**Problem:** `guessExtension()` returns null or wrong extension.

**Check:** The file must be a valid PDF with proper MIME type.

**Solution:** Ensure the file is actually a PDF and not renamed from another format.

### 5. Database Migration Not Run

**Problem:** The `study_session_id` column is still NOT NULL.

**Solution:**
```bash
php bin/console doctrine:migrations:migrate
```

This makes `study_session_id` nullable so course resources can be saved.

## Debugging Steps

### Step 1: Check Error Message

Try uploading again and note the complete error message. It should now show:
```
Failed to upload file: [filename] - Error: [specific error]
```

### Step 2: Check Directory

Verify the uploads directory exists and is writable:
```bash
# Check if directory exists
dir public\uploads\resources

# Check permissions (Windows)
icacls public\uploads\resources
```

### Step 3: Check PHP Logs

Look at your PHP error log for more details:
- XAMPP: `C:\xampp\php\logs\php_error_log`
- Or check: `php -i | findstr error_log`

### Step 4: Test File Upload Manually

Create a simple test:
```php
// test-upload.php in public folder
<?php
$uploadDir = __DIR__ . '/uploads/resources/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
echo "Directory exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "\n";
echo "Directory writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "\n";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post max size: " . ini_get('post_max_size') . "\n";
```

Access: `http://localhost/test-upload.php`

### Step 5: Check Database

Verify the migration ran:
```sql
DESCRIBE resource;
```

Check if `study_session_id` is nullable:
```
study_session_id | int | YES | MUL | NULL |
```

If it says `NO` instead of `YES`, run the migration.

## Quick Fix Checklist

- [ ] Run migration: `php bin/console doctrine:migrations:migrate`
- [ ] Create directory: `mkdir -p public/uploads/resources`
- [ ] Set permissions: `icacls public\uploads\resources /grant Everyone:F`
- [ ] Check PHP limits: `upload_max_filesize` and `post_max_size`
- [ ] Clear cache: `php bin/console cache:clear`
- [ ] Restart web server
- [ ] Try upload again and check new error message

## Most Likely Causes

Based on the error, the most likely causes are:

1. **Directory permissions** - The web server can't write to the uploads folder
2. **Migration not run** - The database still requires `study_session_id`
3. **PHP upload limits** - File is too large for PHP settings

## Next Steps

1. Try uploading the file again
2. Note the complete error message (it will now show more details)
3. Follow the troubleshooting steps above based on the error
4. If still stuck, check the PHP error log for more information

## Contact Information

If the issue persists after trying these steps, provide:
- The complete error message
- PHP version: `php -v`
- Directory permissions output
- Database schema for resource table
- PHP upload settings
