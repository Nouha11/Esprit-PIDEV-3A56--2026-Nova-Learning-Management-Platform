# User Profile Completion & Avatar Upload System

## Overview

The NOVA platform now includes a comprehensive User Profile Completion & Avatar Upload system that helps users complete their profiles and upload custom avatars. The system tracks profile completion percentage and provides visual feedback to encourage users to fill out all profile fields.

## Features

### Profile Completion Tracking
- **Automatic Calculation**: Tracks completion percentage based on filled fields
- **Visual Progress Bar**: Color-coded progress indicator (red < 50%, yellow < 75%, blue < 100%, green = 100%)
- **Missing Fields Display**: Shows which fields still need to be completed
- **Real-time Updates**: Completion percentage updates after each profile edit

### Avatar Upload
- **VichUploader Integration**: Secure file upload handling
- **Image Preview**: Live preview before uploading
- **File Validation**: Max 2MB, supports JPEG, PNG, GIF, WebP
- **Smart Naming**: Automatic unique filename generation
- **Auto-Delete**: Old avatars are automatically deleted when new ones are uploaded

### User Experience
- **Reusable Components**: Twig components for easy integration
- **Responsive Design**: Works on all devices
- **Accessibility**: Screen reader friendly, keyboard navigation
- **Visual Feedback**: Clear indicators for completion status

## Components

### 1. ProfileCompletionService

**Location**: `src/Service/ProfileCompletionService.php`

Calculates profile completion percentage for both students and tutors.

**Methods**:
- `calculateStudentCompletion(StudentProfile $student): array`
- `calculateTutorCompletion(TutorProfile $tutor): array`

**Return Format**:
```php
[
    'percentage' => 75,           // Completion percentage (0-100)
    'completed' => 6,             // Number of completed fields
    'total' => 8,                 // Total number of fields
    'missing' => ['Bio', 'Major'], // Array of missing field names
    'isComplete' => false         // Whether profile is 100% complete
]
```

### 2. Entity Updates

**StudentProfile** (`src/Entity/users/StudentProfile.php`):
- Added `avatarFile` property (File object for upload)
- Added `updatedAt` property (triggers VichUploader)
- Added VichUploader annotations
- Tracks: firstName, lastName, bio, university, major, academicLevel, profilePicture, interests

**TutorProfile** (`src/Entity/users/TutorProfile.php`):
- Added `avatarFile` property (File object for upload)
- Added `updatedAt` property (triggers VichUploader)
- Added VichUploader annotations
- Tracks: firstName, lastName, bio, expertise, qualifications, yearsOfExperience, hourlyRate, profilePicture

### 3. Controller Updates

**StudentController** (`src/Controller/Front/users/StudentController.php`):
- Injects `ProfileCompletionService`
- Passes completion data to all views
- Handles avatar file uploads in `editProfile()`

**TutorController** (`src/Controller/Front/users/TutorController.php`):
- Injects `ProfileCompletionService`
- Passes completion data to all views
- Handles avatar file uploads in `editProfile()`

### 4. Twig Components

**Profile Completion Widget** (`templates/components/profile_completion.html.twig`):
- Displays completion percentage
- Shows progress bar with color coding
- Lists missing fields
- Provides "Complete Profile" button

**Avatar Upload Component** (`templates/components/avatar_upload.html.twig`):
- Shows current avatar or placeholder
- File upload button
- Live preview functionality
- File size and type information

## Installation & Setup

### 1. VichUploader Configuration

Already configured in `config/packages/vich_uploader.yaml`:

```yaml
vich_uploader:
    db_driver: orm
    mappings:
        user_avatars:
            uri_prefix: /uploads/avatars
            upload_destination: '%kernel.project_dir%/public/uploads/avatars'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true
```

### 2. Database Migration

The `updated_at` field has been added to both `student_profile` and `tutor_profile` tables.

### 3. Upload Directory

Created: `public/uploads/avatars/`

Ensure this directory has write permissions.

## Usage

### In Controllers

```php
use App\Service\ProfileCompletionService;

public function __construct(
    private ProfileCompletionService $profileCompletionService
) {}

public function profile(): Response
{
    $student = $this->getUser()->getStudentProfile();
    $completion = $this->profileCompletionService->calculateStudentCompletion($student);
    
    return $this->render('template.html.twig', [
        'student' => $student,
        'completion' => $completion,
    ]);
}
```

### In Templates

**Profile Completion Widget**:
```twig
{% include 'components/profile_completion.html.twig' with {
    'completion': completion,
    'editRoute': path('app_student_profile_edit')
} %}
```

**Avatar Upload in Forms**:
```twig
<form method="post" enctype="multipart/form-data">
    {% include 'components/avatar_upload.html.twig' with {
        'profile': student
    } %}
    
    {# Other form fields #}
    
    <button type="submit">Save Profile</button>
</form>
```

**Display Avatar**:
```twig
{% if student.profilePicture %}
    <img src="{{ vich_uploader_asset(student, 'avatarFile') }}" 
         alt="Profile Picture" 
         class="rounded-circle"
         style="width: 50px; height: 50px; object-fit: cover;">
{% else %}
    <i class="bi bi-person-circle" style="font-size: 50px;"></i>
{% endif %}
```

## Profile Completion Criteria

### Student Profile (8 fields)
1. ✅ First Name (required)
2. ✅ Last Name (required)
3. ✅ Bio
4. ✅ University (required)
5. ✅ Major
6. ✅ Academic Level
7. ✅ Profile Picture
8. ✅ Interests

### Tutor Profile (8 fields)
1. ✅ First Name (required)
2. ✅ Last Name (required)
3. ✅ Bio
4. ✅ Expertise (required)
5. ✅ Qualifications
6. ✅ Years of Experience (required)
7. ✅ Hourly Rate
8. ✅ Profile Picture

## File Upload Specifications

### Accepted Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)

### File Size Limit
- Maximum: 2MB per file

### Storage
- Location: `public/uploads/avatars/`
- Naming: Automatic unique names via `SmartUniqueNamer`
- Example: `profile-6999b65b252cb.png`

### Security
- File type validation
- Size validation
- Automatic sanitization
- Old files deleted on update

## Visual Indicators

### Completion Percentage Colors
- 🔴 **Red (0-49%)**: Critical - Profile needs attention
- 🟡 **Yellow (50-74%)**: Warning - Almost there
- 🔵 **Blue (75-99%)**: Info - Nearly complete
- 🟢 **Green (100%)**: Success - Profile complete!

### Progress Bar
- Smooth animation
- Color-coded based on percentage
- Rounded corners for modern look

## Integration Examples

### Student Dashboard
```twig
<div class="row">
    <div class="col-md-4">
        {# Profile Completion Widget #}
        {% include 'components/profile_completion.html.twig' with {
            'completion': completion,
            'editRoute': path('app_student_profile_edit')
        } %}
    </div>
    <div class="col-md-8">
        {# Dashboard content #}
    </div>
</div>
```

### Profile Edit Page
```twig
<form method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-4">
            {# Avatar Upload #}
            {% include 'components/avatar_upload.html.twig' with {
                'profile': student
            } %}
            
            {# Completion Status #}
            {% include 'components/profile_completion.html.twig' with {
                'completion': completion,
                'editRoute': path('app_student_profile_edit')
            } %}
        </div>
        <div class="col-md-8">
            {# Profile form fields #}
        </div>
    </div>
</form>
```

## Customization

### Adding New Fields to Track

Edit `ProfileCompletionService.php`:

```php
public function calculateStudentCompletion(StudentProfile $student): array
{
    $fields = [
        'firstName' => $student->getFirstName(),
        'lastName' => $student->getLastName(),
        // Add your new field here
        'phoneNumber' => $student->getPhoneNumber(),
    ];
    
    // Rest of the method...
}
```

Don't forget to add the field label in `getFieldLabel()` method.

### Changing File Size Limit

Edit entity annotation:

```php
#[Assert\File(
    maxSize: '5M',  // Change from 2M to 5M
    mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
)]
private ?File $avatarFile = null;
```

### Custom Avatar Placeholder

Replace the default placeholder in `avatar_upload.html.twig`:

```twig
<img src="{{ asset('path/to/your/default-avatar.png') }}" 
     alt="Default Avatar">
```

## Troubleshooting

### Avatar Not Uploading

**Problem**: File upload fails silently

**Solutions**:
1. Check form has `enctype="multipart/form-data"`
2. Verify upload directory exists and is writable
3. Check file size doesn't exceed PHP limits
4. Ensure VichUploader bundle is installed

### Completion Percentage Not Updating

**Problem**: Percentage stays the same after editing

**Solutions**:
1. Clear Symfony cache: `php bin/console cache:clear`
2. Verify ProfileCompletionService is injected
3. Check completion data is passed to template
4. Ensure all fields are being tracked in service

### Avatar Not Displaying

**Problem**: Broken image or placeholder shows

**Solutions**:
1. Check file was actually uploaded to `public/uploads/avatars/`
2. Verify `profilePicture` field is set in database
3. Use `vich_uploader_asset()` function, not direct path
4. Clear browser cache

### Permission Denied

**Problem**: Cannot write to upload directory

**Solutions**:
```bash
# Windows (run as administrator)
icacls public\uploads\avatars /grant Users:F

# Linux/Mac
chmod 777 public/uploads/avatars
```

## Best Practices

1. **Always use enctype**: Include `enctype="multipart/form-data"` in forms with file uploads
2. **Validate on both sides**: Client-side and server-side validation
3. **Show progress**: Use the completion widget to encourage profile completion
4. **Provide feedback**: Show success/error messages after uploads
5. **Optimize images**: Consider adding image optimization for uploaded avatars
6. **Backup strategy**: Implement backup for uploaded files
7. **Clean old files**: VichUploader handles this automatically

## Security Considerations

1. **File Type Validation**: Only allowed image types can be uploaded
2. **Size Limits**: Prevents large file uploads
3. **Unique Naming**: Prevents file name conflicts and directory traversal
4. **Auto-Delete**: Old files are removed to prevent storage bloat
5. **No Direct Access**: Files are served through Symfony, not direct URLs

## Performance Tips

1. **Image Optimization**: Consider using image optimization libraries
2. **CDN Integration**: Serve avatars from CDN for better performance
3. **Lazy Loading**: Use lazy loading for avatar images
4. **Caching**: Cache completion calculations if needed
5. **Thumbnails**: Generate thumbnails for different sizes

## Future Enhancements

Potential improvements for the system:

1. **Image Cropping**: Allow users to crop avatars before upload
2. **Multiple Sizes**: Generate thumbnail, medium, and large versions
3. **Social Import**: Import avatars from social media profiles
4. **Gravatar Support**: Fallback to Gravatar if no avatar uploaded
5. **Profile Badges**: Award badges for profile completion milestones
6. **Completion Rewards**: Give XP/tokens for completing profile
7. **Profile Visibility**: Control who can see profile information
8. **Profile Analytics**: Track which fields users skip most often

## Support

For issues or questions:
- Check this documentation
- Review the source code in `src/Service/ProfileCompletionService.php`
- Test with different file types and sizes
- Check Symfony logs for errors

---

**Status**: ✅ COMPLETE - Profile completion tracking and avatar upload system is fully functional!
