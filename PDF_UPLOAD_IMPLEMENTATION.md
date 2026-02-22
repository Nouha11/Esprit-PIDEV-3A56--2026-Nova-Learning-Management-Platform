# PDF Resource Upload Implementation

## Overview

Tutors and admins can now upload PDF resources when creating or editing courses. These PDFs are displayed to enrolled students during course sessions and can be downloaded for reference.

## Features Implemented

### 1. Course Creation with PDF Upload

**Location:** Create New Course page (`/courses/new`)

**Features:**
- Optional PDF upload field (not mandatory)
- Multiple file upload support
- File validation (PDF only, max 10MB per file)
- Automatic file storage and database tracking

### 2. Course Editing with PDF Upload

**Location:** Edit Course page (`/courses/{id}/edit`)

**Features:**
- Add new PDF resources to existing courses
- View list of existing resources with file sizes
- Multiple file upload support
- Same validation as creation

### 3. Student Access

**Location:** Course Session View (`/course/{courseId}/session`)

**Features:**
- Students see all PDF resources for enrolled courses
- Download links with original filenames
- Resources displayed in dedicated section

## Technical Implementation

### Files Modified

1. **CourseType.php** (`src/Form/StudySession/CourseType.php`)
   - Added `pdfResources` FileType field
   - Multiple file upload support
   - PDF validation (max 10MB, PDF mime type only)

2. **CourseController.php** (`src/Controller/Front/StudySession/CourseController.php`)
   - Updated `new()` action to handle PDF uploads
   - Updated `edit()` action to handle PDF uploads
   - File processing and storage logic
   - Resource entity creation

3. **Resource.php** (`src/Entity/StudySession/Resource.php`)
   - Made `studySession` field nullable
   - Allows resources to be linked to courses without study sessions

4. **new.html.twig** (`templates/front/course/new.html.twig`)
   - Added PDF upload field with instructions
   - Added `enctype="multipart/form-data"` to form

5. **edit.html.twig** (`templates/front/course/edit.html.twig`)
   - Added PDF upload field
   - Display existing resources with file sizes
   - Added `enctype="multipart/form-data"` to form

6. **Migration** (`migrations/Version20260222160000.php`)
   - Makes `study_session_id` nullable in resource table

## File Upload Process

### 1. File Validation
- Only PDF files accepted (`.pdf` extension)
- Maximum file size: 10MB per file
- Multiple files can be uploaded at once

### 2. File Storage
- Files stored in: `public/uploads/resources/`
- Filename format: `{sanitized-name}-{unique-id}.pdf`
- Original filename preserved in database

### 3. Database Storage
- Creates `Resource` entity for each uploaded file
- Links resource to course via `course_id`
- Stores: filename, stored filename, file size, mime type, upload date

## User Flow

### For Tutors/Admins:

1. **Creating a Course:**
   - Navigate to Courses → Create New Course
   - Fill in course details
   - Scroll to "PDF Resources (Optional)" section
   - Click "Choose Files" and select one or more PDFs
   - Click "Create Course"
   - Success message shows number of PDFs uploaded

2. **Editing a Course:**
   - Navigate to course → Edit
   - Scroll to "Add PDF Resources (Optional)" section
   - View existing resources (if any)
   - Click "Choose Files" to add more PDFs
   - Click "Update Course"
   - Success message shows number of new PDFs uploaded

### For Students:

1. **Viewing Resources:**
   - Enroll in a course
   - Click "Start Course"
   - Scroll to "PDF Resources" section
   - See list of available PDFs
   - Click filename to download

2. **No Resources:**
   - If no PDFs uploaded, sees "No resources available" message

## File Structure

```
public/
└── uploads/
    └── resources/
        ├── introduction-to-php-abc123.pdf
        ├── advanced-concepts-def456.pdf
        └── ...
```

## Database Schema

### Resource Table Updates

```sql
ALTER TABLE resource MODIFY study_session_id INT DEFAULT NULL;
```

**Fields:**
- `id` - Primary key
- `study_session_id` - Foreign key (now nullable)
- `course_id` - Foreign key (nullable, for course resources)
- `filename` - Original filename
- `stored_filename` - Unique stored filename
- `file_size` - File size in bytes
- `mime_type` - MIME type (application/pdf)
- `uploaded_at` - Upload timestamp

## Validation Rules

### File Upload Constraints:

```php
'constraints' => [
    new File([
        'maxSize' => '10M',
        'mimeTypes' => ['application/pdf'],
        'mimeTypesMessage' => 'Please upload a valid PDF document',
    ])
]
```

### Form Field Configuration:

```php
->add('pdfResources', FileType::class, [
    'label' => 'PDF Resources (Optional)',
    'mapped' => false,           // Not directly mapped to Course entity
    'required' => false,          // Optional field
    'multiple' => true,           // Allow multiple files
    'attr' => [
        'accept' => '.pdf',       // Browser-level PDF filter
        'class' => 'form-control'
    ]
])
```

## Error Handling

### Upload Failures:

- Individual file upload failures show warning flash message
- Other files continue to upload
- Course creation/update succeeds even if some files fail

### Validation Errors:

- File too large: "Please upload a valid PDF document"
- Wrong file type: "Please upload a valid PDF document"
- Displayed inline with form field

## Security Considerations

1. **File Type Validation:**
   - Server-side MIME type checking
   - Extension validation
   - Only PDF files accepted

2. **File Size Limits:**
   - 10MB maximum per file
   - Prevents server overload

3. **Filename Sanitization:**
   - Special characters removed
   - Unique ID appended
   - Prevents file overwrites

4. **Access Control:**
   - Only enrolled students can view resources
   - Verified in CourseSessionController

## Testing the Feature

### Test as Tutor:

1. **Create Course with PDFs:**
   ```
   - Login as tutor
   - Create New Course
   - Fill in details
   - Upload 2-3 PDF files
   - Submit
   - Verify success message shows PDF count
   ```

2. **Edit Course to Add PDFs:**
   ```
   - Edit existing course
   - View existing resources list
   - Upload additional PDFs
   - Submit
   - Verify new PDFs added
   ```

3. **Test Validation:**
   ```
   - Try uploading non-PDF file → See error
   - Try uploading file > 10MB → See error
   - Upload valid PDF → Success
   ```

### Test as Student:

1. **View Resources:**
   ```
   - Enroll in course with PDFs
   - Click "Start Course"
   - See PDF resources section
   - Click PDF filename
   - Verify download starts
   ```

2. **No Resources:**
   ```
   - Enroll in course without PDFs
   - Click "Start Course"
   - See "No resources available" message
   ```

## Migration Instructions

Run the migration to update the database:

```bash
php bin/console doctrine:migrations:migrate
```

This makes `study_session_id` nullable in the `resource` table.

## Future Enhancements

Potential improvements:

1. **Resource Management:**
   - Delete individual resources
   - Reorder resources
   - Edit resource descriptions

2. **File Types:**
   - Support additional file types (DOCX, PPTX, etc.)
   - Preview PDFs in browser

3. **Organization:**
   - Organize resources into folders
   - Tag resources by topic

4. **Analytics:**
   - Track resource downloads
   - Show most popular resources

5. **Notifications:**
   - Notify students when new resources added
   - Flash message: "This course has new PDF resources available"

## Troubleshooting

### PDFs Not Uploading:

1. Check upload directory exists and is writable:
   ```bash
   mkdir -p public/uploads/resources
   chmod 777 public/uploads/resources
   ```

2. Check PHP upload limits:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 12M
   ```

3. Check server disk space

### PDFs Not Displaying:

1. Verify resource is linked to course:
   ```sql
   SELECT * FROM resource WHERE course_id = {course_id};
   ```

2. Check file exists on disk:
   ```bash
   ls public/uploads/resources/
   ```

3. Verify student is enrolled in course

## Summary

The PDF upload feature is now fully integrated into the course creation and editing workflow. Tutors can easily add learning materials, and students can access them during course sessions. The implementation is secure, validated, and user-friendly.
