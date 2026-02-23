# Profile Completion & Avatar Upload - Quick Start Guide

## 🚀 5-Minute Integration

### Step 1: Add to Student Dashboard

Edit `templates/front/users/student/dashboard.html.twig`:

```twig
<div class="row">
    <div class="col-lg-3 col-md-4">
        {# Profile Completion Widget #}
        {% include 'components/profile_completion.html.twig' with {
            'completion': completion,
            'editRoute': path('app_student_profile_edit')
        } %}
    </div>
    <div class="col-lg-9 col-md-8">
        {# Your existing dashboard content #}
    </div>
</div>
```

### Step 2: Add to Student Profile Edit

Edit `templates/front/users/student/edit.html.twig`:

```twig
<form method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-4">
            {# Avatar Upload #}
            {% include 'components/avatar_upload.html.twig' with {
                'profile': student
            } %}
        </div>
        <div class="col-md-8">
            {# Your existing form fields #}
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="firstName" value="{{ student.firstName }}" class="form-control">
            </div>
            {# ... other fields ... #}
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary">Save Profile</button>
</form>
```

### Step 3: Add to Tutor Dashboard

Edit `templates/front/users/tutor/dashboard.html.twig`:

```twig
<div class="row">
    <div class="col-lg-3 col-md-4">
        {# Profile Completion Widget #}
        {% include 'components/profile_completion.html.twig' with {
            'completion': completion,
            'editRoute': path('app_tutor_profile_edit')
        } %}
    </div>
    <div class="col-lg-9 col-md-8">
        {# Your existing dashboard content #}
    </div>
</div>
```

### Step 4: Add to Tutor Profile Edit

Edit `templates/front/users/tutor/edit.html.twig`:

```twig
<form method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-4">
            {# Avatar Upload #}
            {% include 'components/avatar_upload.html.twig' with {
                'profile': tutor
            } %}
        </div>
        <div class="col-md-8">
            {# Your existing form fields #}
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary">Save Profile</button>
</form>
```

### Step 5: Display Avatar in Navigation

Add to your navigation/header template:

```twig
{% set user = app.user %}
{% if user %}
    {% if user.role == 'ROLE_STUDENT' %}
        {% set profile = user.studentProfile %}
    {% elseif user.role == 'ROLE_TUTOR' %}
        {% set profile = user.tutorProfile %}
    {% endif %}
    
    {% if profile and profile.profilePicture %}
        <img src="{{ vich_uploader_asset(profile, 'avatarFile') }}" 
             alt="{{ profile.firstName }}" 
             class="rounded-circle"
             style="width: 40px; height: 40px; object-fit: cover;">
    {% else %}
        <i class="bi bi-person-circle" style="font-size: 40px;"></i>
    {% endif %}
{% endif %}
```

## ✅ That's It!

Your profile completion and avatar upload system is now fully integrated!

## 🧪 Test It

1. **Login** as a student or tutor
2. **Go to dashboard** - See completion percentage
3. **Click "Complete Profile"** - Opens edit page
4. **Upload an avatar** - Click "Upload Photo", select image
5. **Fill missing fields** - Complete your profile
6. **Save** - See completion percentage increase!

## 📊 What Users Will See

### Incomplete Profile (< 100%)
```
┌─────────────────────────────────┐
│ 🛡️ Profile Completion    [62%] │
├─────────────────────────────────┤
│ ████████░░░░░░░░░░░░░░░░░░░░░░ │
│                                 │
│ ⚠️ Complete your profile to     │
│    unlock all features          │
│                                 │
│ Missing fields:                 │
│ • Bio  • Profile Picture        │
│ • Interests                     │
│                                 │
│ [Complete Profile]              │
└─────────────────────────────────┘
```

### Complete Profile (100%)
```
┌─────────────────────────────────┐
│ 🛡️ Profile Completion   [100%] │
├─────────────────────────────────┤
│ ████████████████████████████████│
│                                 │
│ ✅ Your profile is complete!    │
│    Great job!                   │
└─────────────────────────────────┘
```

## 🎨 Customization

### Change Colors

Edit `templates/components/profile_completion.html.twig`:

```twig
{# Change badge colors #}
{% if isComplete %}
    bg-success  {# Green #}
{% elseif percentage >= 75 %}
    bg-info     {# Blue #}
{% elseif percentage >= 50 %}
    bg-warning  {# Yellow #}
{% else %}
    bg-danger   {# Red #}
{% endif %}
```

### Change File Size Limit

Edit entity (`StudentProfile.php` or `TutorProfile.php`):

```php
#[Assert\File(
    maxSize: '5M',  // Change from 2M to 5M
    mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
)]
```

### Add More Fields to Track

Edit `src/Service/ProfileCompletionService.php`:

```php
$fields = [
    'firstName' => $student->getFirstName(),
    'lastName' => $student->getLastName(),
    // Add your new field
    'phoneNumber' => $student->getPhoneNumber(),
];
```

## 🐛 Common Issues

### Avatar not uploading?
✅ Add `enctype="multipart/form-data"` to your form tag

### Completion not showing?
✅ Make sure `completion` variable is passed from controller

### Avatar not displaying?
✅ Use `vich_uploader_asset(profile, 'avatarFile')` not direct path

## 📚 Full Documentation

For complete documentation, see:
- `docs/PROFILE_COMPLETION_AVATAR_UPLOAD.md`
- `PROFILE_SYSTEM_SUMMARY.md`

## 🎯 Next Steps

1. ✅ Integrate into dashboards (done above)
2. ✅ Add to profile edit pages (done above)
3. ⭐ Add avatar to navigation/header
4. ⭐ Add completion widget to sidebar
5. ⭐ Award XP for profile completion (optional)

---

**You're all set!** The system is ready to use. 🎉
