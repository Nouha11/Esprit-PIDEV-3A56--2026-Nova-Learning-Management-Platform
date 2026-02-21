# Quiz Pagination and Image Upload System

## Overview
This document describes the implementation of KnpPaginatorBundle for quiz pagination and VichUploaderBundle for question image uploads in the quiz system.

## Features Implemented

### 1. KnpPaginatorBundle - Quiz Pagination

#### What It Does
- Paginates quiz lists on both admin and front-end interfaces
- Maintains filter and sort parameters across pages
- Provides clean, Bootstrap-styled pagination controls
- Improves performance by loading quizzes in batches

#### Configuration
**File:** `config/packages/knp_paginator.yaml`
```yaml
knp_paginator:
    page_range: 5
    default_options:
        page_name: page
        sort_field_name: sort
        sort_direction_name: direction
    template:
        pagination: '@App/pagination/custom_pagination.html.twig'
```

#### Implementation Details

**Repository Changes:**
- Modified `QuizRepository::findWithFiltersAndSort()` to return QueryBuilder instead of array
- This allows the paginator to efficiently paginate results

**Controller Changes:**
- Admin: 12 quizzes per page (3x4 grid)
- Front-end: 9 quizzes per page (3x3 grid)
- Both controllers inject `PaginatorInterface` and use `paginate()` method

**Template Changes:**
- Replaced `{% for quiz in quizzes %}` with `{% for quiz in pagination %}`
- Added `{{ knp_pagination_render(pagination) }}` for pagination controls
- Custom pagination template at `templates/pagination/custom_pagination.html.twig`

#### Usage
```php
// In Controller
$pagination = $paginator->paginate(
    $queryBuilder,
    $request->query->getInt('page', 1),
    12 // items per page
);

return $this->render('template.html.twig', [
    'pagination' => $pagination
]);
```

```twig
{# In Template #}
{% for item in pagination %}
    {# Display item #}
{% endfor %}

{{ knp_pagination_render(pagination) }}
```

### 2. VichUploaderBundle - Question Images

#### What It Does
- Allows admins to upload images for quiz questions
- Automatically handles file naming, storage, and deletion
- Validates image types and sizes
- Displays images during quiz gameplay

#### Configuration
**File:** `config/packages/vich_uploader.yaml`
```yaml
question_images:
    uri_prefix: /uploads/questions
    upload_destination: '%kernel.project_dir%/public/uploads/questions'
    namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
    inject_on_load: false
    delete_on_update: true
    delete_on_remove: true
```

#### Entity Changes
**File:** `src/Entity/Quiz/Question.php`

Added fields:
- `$imageFile` - Handles the uploaded file (not persisted)
- `$imageName` - Stores the filename in database
- `$updatedAt` - Tracks when image was last updated

Validation:
- Max file size: 2MB
- Allowed types: JPEG, PNG, GIF, WebP

#### Form Changes
**File:** `src/Form/Quiz/QuestionType.php`

Added field:
```php
->add('imageFile', VichImageType::class, [
    'label' => 'Question Image (Optional)',
    'required' => false,
    'allow_delete' => true,
    'delete_label' => 'Remove image',
    'help' => 'Upload an image to make the question more engaging (max 2MB)',
])
```

#### Template Changes
**File:** `templates/front/quiz/game/play.html.twig`

Displays image if available:
```twig
{% if question.imageName %}
    <div class="mb-4">
        <img src="{{ asset('uploads/questions/' ~ question.imageName) }}" 
             alt="Question image" 
             class="img-fluid rounded-3 shadow-sm"
             style="max-height: 300px; object-fit: contain;">
    </div>
{% endif %}
```

## Database Changes

### Migration: Version20260221190455
Added to `question` table:
- `image_name` VARCHAR(255) NULL
- `updated_at` DATETIME NULL

## File Structure

```
public/uploads/questions/     # Question image storage
templates/pagination/         # Custom pagination templates
  └── custom_pagination.html.twig
```

## Benefits

### Pagination Benefits
1. **Performance**: Loads only needed quizzes per page
2. **User Experience**: Easier navigation through large quiz lists
3. **Scalability**: Handles hundreds of quizzes efficiently
4. **Filter Preservation**: Maintains search/sort across pages

### Image Upload Benefits
1. **Engagement**: Visual questions are more engaging
2. **Flexibility**: Support for diagrams, charts, photos
3. **Automatic Management**: Files are automatically renamed and cleaned up
4. **Validation**: Ensures only valid images are uploaded

## Usage Examples

### Adding an Image to a Question
1. Go to Admin → Quiz Manager
2. Select a quiz and click "Manage Questions"
3. Create or edit a question
4. Use the "Question Image" field to upload an image
5. Save the question

### Viewing Paginated Quizzes
1. Navigate to Quiz Arcade (front-end) or Quiz Manager (admin)
2. Apply filters or sorting if desired
3. Use pagination controls at the bottom to navigate pages
4. Filters and sorting are preserved across pages

## Technical Notes

### Pagination
- Uses Doctrine QueryBuilder for efficient queries
- Supports complex filtering with HAVING clauses
- Custom template provides Bootstrap 5 styling
- Shows "Showing X to Y of Z results" info

### Image Upload
- Files stored in `public/uploads/questions/`
- Filenames are automatically made unique and safe
- Old images are deleted when updated or removed
- Images are optional - questions work without them

## Testing

### Test Pagination
1. Create 20+ quizzes
2. Navigate to quiz list
3. Verify pagination appears
4. Test page navigation
5. Apply filters and verify they persist across pages

### Test Image Upload
1. Create/edit a question
2. Upload an image (test various formats)
3. Save and verify image appears in gameplay
4. Update image and verify old one is deleted
5. Delete image and verify it's removed from storage

## Future Enhancements

Potential improvements:
- Image cropping/resizing in admin interface
- Multiple images per question
- Image galleries for questions
- Lazy loading for pagination
- AJAX-based pagination (no page reload)
- Image optimization/compression

## Troubleshooting

### Pagination Not Working
- Verify KnpPaginatorBundle is installed: `composer show knplabs/knp-paginator-bundle`
- Check configuration in `config/packages/knp_paginator.yaml`
- Ensure repository returns QueryBuilder, not array

### Images Not Uploading
- Check directory permissions: `public/uploads/questions/` must be writable
- Verify VichUploaderBundle is installed: `composer show vich/uploader-bundle`
- Check file size and type restrictions
- Review server PHP upload limits in `php.ini`

### Images Not Displaying
- Verify file exists in `public/uploads/questions/`
- Check `imageName` field in database
- Ensure correct path in template: `asset('uploads/questions/' ~ question.imageName)`
- Clear Symfony cache: `php bin/console cache:clear`

## Related Files

### Controllers
- `src/Controller/Admin/Quiz/QuizController.php`
- `src/Controller/Front/Quiz/QuizGameController.php`

### Repositories
- `src/Repository/QuizRepository.php`

### Entities
- `src/Entity/Quiz/Question.php`

### Forms
- `src/Form/Quiz/QuestionType.php`

### Templates
- `templates/admin/quiz/index.html.twig`
- `templates/front/quiz/game/index.html.twig`
- `templates/front/quiz/game/play.html.twig`
- `templates/pagination/custom_pagination.html.twig`

### Configuration
- `config/packages/knp_paginator.yaml`
- `config/packages/vich_uploader.yaml`

## Conclusion

Both KnpPaginatorBundle and VichUploaderBundle are now fully integrated into the quiz system, providing professional pagination and image upload capabilities with minimal code and maximum functionality.
