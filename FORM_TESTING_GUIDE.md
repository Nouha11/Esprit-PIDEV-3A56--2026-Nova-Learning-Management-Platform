# Form Testing Guide - Empty Data Fix

## Quick Test Commands

```bash
# 1. Clear cache
php bin/console cache:clear

# 2. Check for syntax errors
php bin/console lint:container

# 3. Verify forms are registered
php bin/console debug:container --tag=form.type
```

## Manual Testing Checklist

### 1. Post/Forum Forms

#### PostType Form
- [ ] Navigate to create new post
- [ ] Leave title empty and submit
- [ ] Should show validation error (not null error)
- [ ] Leave content empty and submit
- [ ] Should show validation error (not null error)

#### CommentType Form
- [ ] Navigate to add comment
- [ ] Leave content empty and submit
- [ ] Should show validation error (not null error)

### 2. Quiz Forms

#### QuizType Form
- [ ] Navigate to create quiz
- [ ] Leave title empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave description empty and submit
- [ ] Should get empty string, not null error

#### QuestionType Form
- [ ] Navigate to create question
- [ ] Leave text empty and submit
- [ ] Should show validation error (not null error)

#### AnswerType Form
- [ ] Navigate to add answer
- [ ] Leave content empty and submit
- [ ] Should get empty string, not null error

### 3. Study Session Forms

#### CourseType Form
- [ ] Navigate to create/edit course
- [ ] Leave courseName empty and submit
- [ ] Should show validation error (not null error)
- [ ] Leave description empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave estimatedDuration empty and submit
- [ ] Should get 0, not null error

#### PlanningType Form
- [ ] Navigate to create/edit planning
- [ ] Leave title empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave plannedDuration empty and submit
- [ ] Should get 0, not null error

### 4. Library Forms

#### BookType Form
- [ ] Navigate to create/edit book
- [ ] Leave title empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave description empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave author empty and submit
- [ ] Should get empty string, not null error

### 5. Admin Forms

#### GameFormType
- [ ] Navigate to admin games
- [ ] Create new game
- [ ] Leave name empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave description empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave tokenCost empty and submit
- [ ] Should get 0, not null error
- [ ] Leave rewardTokens empty and submit
- [ ] Should get 0, not null error
- [ ] Leave rewardXP empty and submit
- [ ] Should get 0, not null error

#### RewardFormType
- [ ] Navigate to admin rewards
- [ ] Create new reward
- [ ] Leave name empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave description empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave value empty and submit
- [ ] Should get 0, not null error
- [ ] Leave requirement empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave icon empty and submit
- [ ] Should get empty string, not null error

#### CourseFormType (Admin)
- [ ] Navigate to admin courses
- [ ] Create new course
- [ ] Leave courseName empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave description empty and submit
- [ ] Should get empty string, not null error
- [ ] Leave estimatedDuration empty and submit
- [ ] Should get 0, not null error
- [ ] Leave category empty and submit
- [ ] Should get empty string, not null error

## Expected Behaviors

### ✅ Correct Behavior (After Fix)
```
User submits form with empty field
↓
Form processes with empty_data value
↓
Validation checks if required
↓
If required: Shows "This field is required"
If optional: Saves with empty string or 0
```

### ❌ Old Behavior (Before Fix)
```
User submits form with empty field
↓
Form passes null to entity
↓
Entity expects string, gets null
↓
ERROR: "Expected argument of type 'string', 'null' given"
```

## Common Test Scenarios

### Scenario 1: Required String Field
```
Field: title (required)
Empty Value: '' (empty string)
Expected: Validation error "This field is required"
NOT: Null type error
```

### Scenario 2: Optional String Field
```
Field: description (optional)
Empty Value: '' (empty string)
Expected: Saves successfully with empty string
NOT: Null type error
```

### Scenario 3: Required Integer Field
```
Field: estimatedDuration (required)
Empty Value: 0
Expected: Validation error if 0 is not allowed
OR: Saves with 0 if allowed
NOT: Null type error
```

### Scenario 4: Optional Integer Field
```
Field: maxStudents (optional)
Empty Value: 0
Expected: Saves successfully with 0
NOT: Null type error
```

## Browser Testing

### Chrome DevTools
1. Open form in browser
2. Open DevTools (F12)
3. Go to Console tab
4. Submit form with empty fields
5. Check for JavaScript errors
6. Check Network tab for response

### Firefox Developer Tools
1. Open form in browser
2. Open Developer Tools (F12)
3. Go to Console tab
4. Submit form with empty fields
5. Check for errors

## Automated Testing (Optional)

### PHPUnit Test Example
```php
public function testFormWithEmptyFields(): void
{
    $formData = [
        'title' => '',
        'description' => '',
    ];

    $form = $this->factory->create(PostType::class);
    $form->submit($formData);

    // Should not throw type error
    $this->assertTrue($form->isSynchronized());
    
    // Should have validation errors for required fields
    $this->assertFalse($form->isValid());
}
```

## Troubleshooting

### If You Still See Null Errors

1. **Clear Cache**
   ```bash
   php bin/console cache:clear
   ```

2. **Check Entity Properties**
   - Ensure entity properties are NOT nullable if they shouldn't be
   ```php
   // Good
   private string $title;
   
   // Bad (if you want to prevent null)
   private ?string $title;
   ```

3. **Check Form Configuration**
   - Verify `empty_data` is set correctly
   ```php
   ->add('title', TextType::class, [
       'empty_data' => '',  // Must be present
   ])
   ```

4. **Check Database Schema**
   - Ensure columns allow empty strings if needed
   ```sql
   -- Good
   title VARCHAR(255) NOT NULL DEFAULT ''
   
   -- May cause issues
   title VARCHAR(255) NOT NULL
   ```

## Success Criteria

✅ All forms can be submitted with empty fields without null type errors
✅ Required field validation still works correctly
✅ Optional fields save with empty string or 0
✅ No JavaScript console errors
✅ No PHP exceptions in logs
✅ Database records save correctly

## Reporting Issues

If you find a form that still has null errors:

1. Note the form name (e.g., PostType)
2. Note the field name (e.g., title)
3. Note the exact error message
4. Check if `empty_data` is configured for that field
5. Report to development team with details

## Summary

All 11 form types have been updated with proper `empty_data` configuration. The "Expected argument of type 'string', 'null' given" error should no longer occur when submitting forms with empty fields.

Test each form to verify the fix is working correctly!
