# CAPTCHA System Documentation

## Overview

NOVA Platform includes a personalized, educational-themed CAPTCHA system to prevent automated bot submissions while maintaining a user-friendly experience. The system offers two types of CAPTCHA challenges:

1. **Question-based CAPTCHA**: Educational questions that are easy for humans but difficult for bots
2. **Visual CAPTCHA**: SVG-based image CAPTCHA with distorted text

## Features

- **Educational Theme**: Questions related to math, logic, and general knowledge
- **SVG-based Visual CAPTCHA**: No GD extension required, lightweight and scalable
- **Session-based**: Secure storage of CAPTCHA answers
- **Refresh Capability**: Users can request a new CAPTCHA if needed
- **Customizable**: Easy to add new questions or modify difficulty
- **Accessible**: Clear instructions and user-friendly interface

## Installation

The CAPTCHA system is already integrated into the platform. No additional packages are required.

## Components

### 1. CaptchaService (`src/Service/CaptchaService.php`)

Main service handling CAPTCHA generation and verification.

**Methods:**
- `generateCaptcha()`: Generate a question-based CAPTCHA
- `generateVisualCaptcha()`: Generate an SVG-based visual CAPTCHA
- `verifyCaptcha(string $answer)`: Verify user's answer
- `getCurrentQuestion()`: Get the current CAPTCHA question
- `hasCaptcha()`: Check if a CAPTCHA is active
- `clearCaptcha()`: Clear the current CAPTCHA from session

### 2. CaptchaController (`src/Controller/CaptchaController.php`)

Handles CAPTCHA image generation and refresh requests.

**Routes:**
- `/captcha/generate` - Generate new CAPTCHA image
- `/captcha/refresh` - Refresh CAPTCHA (AJAX)

### 3. CAPTCHA Component (`templates/components/captcha.html.twig`)

Reusable Twig component for displaying CAPTCHA in forms.

## Usage

### Question-based CAPTCHA

```php
// In your controller
use App\Service\CaptchaService;

public function yourAction(Request $request, CaptchaService $captchaService)
{
    if ($request->isMethod('POST')) {
        $userAnswer = $request->request->get('captcha_answer');
        
        if (!$captchaService->verifyCaptcha($userAnswer)) {
            $this->addFlash('error', 'Incorrect CAPTCHA answer. Please try again.');
            return $this->redirectToRoute('your_route');
        }
        
        // Process form...
    }
    
    // Generate CAPTCHA for display
    $captcha = $captchaService->generateCaptcha();
    
    return $this->render('your_template.html.twig', [
        'captchaQuestion' => $captcha['question'],
    ]);
}
```

```twig
{# In your template #}
{% include 'components/captcha.html.twig' with {
    'captchaType': 'question',
    'captchaQuestion': captchaQuestion
} %}
```

### Visual CAPTCHA

```php
// In your controller
public function yourAction(Request $request, CaptchaService $captchaService)
{
    if ($request->isMethod('POST')) {
        $userAnswer = $request->request->get('captcha_answer');
        
        if (!$captchaService->verifyCaptcha($userAnswer)) {
            $this->addFlash('error', 'Incorrect CAPTCHA code. Please try again.');
            return $this->redirectToRoute('your_route');
        }
        
        // Process form...
    }
    
    // Visual CAPTCHA is generated automatically via route
    return $this->render('your_template.html.twig');
}
```

```twig
{# In your template #}
{% include 'components/captcha.html.twig' with {
    'captchaType': 'visual'
} %}
```

## CAPTCHA Questions

The system includes various types of educational questions:

### Math Challenges
- Basic arithmetic (addition, subtraction, multiplication, division)
- Example: "What is 5 + 3?" → Answer: "8"

### Educational Questions
- General knowledge
- Example: "How many days are in a week?" → Answer: "7"

### Pattern Recognition
- Number sequences
- Example: "Complete the sequence: 2, 4, 6, 8, __" → Answer: "10"

### Logic Questions
- Simple reasoning
- Example: "If today is Monday, what day is tomorrow?" → Answer: "tuesday"

## Adding Custom Questions

To add new CAPTCHA questions, edit `src/Service/CaptchaService.php`:

```php
$challenges = [
    // Add your custom challenge
    [
        'type' => 'custom',
        'question' => 'Your question here?',
        'answer' => 'expected answer',
    ],
    // ... existing challenges
];
```

## Visual CAPTCHA Customization

The visual CAPTCHA can be customized by modifying the `createSvgCaptcha()` method:

```php
private function createSvgCaptcha(string $code): string
{
    $width = 200;        // Image width
    $height = 60;        // Image height
    $fontSize = 24;      // Font size
    
    // Customize colors, noise, distortion, etc.
}
```

## Security Considerations

1. **Session-based Storage**: CAPTCHA answers are stored in the session, not in the HTML
2. **One-time Use**: CAPTCHA is cleared after successful verification
3. **Case-insensitive**: Answers are compared in lowercase to improve UX
4. **No Reuse**: Each CAPTCHA can only be used once
5. **Timeout**: Session-based timeout prevents indefinite CAPTCHA validity

## Integration Points

The CAPTCHA system is now fully integrated into the following forms:

### 1. Login Page (`/login`)
- **Type**: Question-based CAPTCHA
- **Verification**: Via `LoginCaptchaSubscriber` event subscriber
- **Behavior**: 
  - CAPTCHA generated on page load
  - Verified before authentication
  - New CAPTCHA generated on failed login attempts
  - Prevents brute force attacks

### 2. Student Registration (`/signup/student`)
- **Type**: Question-based CAPTCHA
- **Verification**: In `SecurityController::signupStudent()`
- **Behavior**:
  - CAPTCHA generated on GET request
  - Verified before account creation
  - New CAPTCHA generated on validation errors
  - Prevents automated bot registrations

### 3. Tutor Registration (`/signup/tutor`)
- **Type**: Question-based CAPTCHA
- **Verification**: In `SecurityController::signupTutor()`
- **Behavior**:
  - CAPTCHA generated on GET request
  - Verified before account creation
  - New CAPTCHA generated on validation errors
  - Prevents automated bot registrations

### How It Works

1. **On Page Load**: Controller generates a CAPTCHA and stores the answer in session
2. **User Submits Form**: Answer is verified against session value
3. **On Success**: Session is cleared and form processing continues
4. **On Failure**: New CAPTCHA is generated and user must try again

### Implementation Details

**SecurityController Integration:**
```php
// Constructor injection
public function __construct(
    private CaptchaService $captchaService
) {}

// In action methods
if (!$request->isMethod('POST')) {
    $this->captchaService->generateCaptcha();
}

// Verify on POST
$captchaAnswer = $request->request->get('captcha_answer');
if (!$this->captchaService->verifyCaptcha($captchaAnswer)) {
    $this->addFlash('error', 'Invalid CAPTCHA answer. Please try again.');
    $this->captchaService->generateCaptcha();
    return $this->render('template.html.twig', [
        'captchaQuestion' => $this->captchaService->getCurrentQuestion(),
    ]);
}
```

**Login Event Subscriber:**
```php
// src/EventSubscriber/LoginCaptchaSubscriber.php
// Subscribes to CheckPassportEvent
// Verifies CAPTCHA before authentication
// Throws CustomUserMessageAuthenticationException on failure
```

### Template Integration

All three forms now include the CAPTCHA component:

```twig
{% include 'components/captcha.html.twig' with {
    'captchaType': 'question',
    'captchaQuestion': captchaQuestion
} %}
```

### Additional Integration Examples

#### Contact Forms
#### Contact Forms
Add CAPTCHA to prevent spam:

```twig
{# templates/contact/form.html.twig #}
{% include 'components/captcha.html.twig' with {
    'captchaType': 'question',
    'captchaQuestion': captchaQuestion
} %}
```

#### Password Reset
Add CAPTCHA to prevent abuse:

```twig
{# templates/security/forgot_password.html.twig #}
{% include 'components/captcha.html.twig' with {
    'captchaType': 'visual'
} %}
```

## Styling

The CAPTCHA component includes built-in styling with:
- Gradient backgrounds
- Border highlights
- Responsive design
- Icon integration
- Hover effects

Custom styling can be added in your template:

```css
.captcha-container {
    /* Your custom styles */
}
```

## Accessibility

The CAPTCHA system is designed with accessibility in mind:

- Clear labels and instructions
- Alternative text for images
- Keyboard navigation support
- Screen reader friendly
- High contrast colors

## Testing

### Manual Testing

1. **Question CAPTCHA**:
   - Navigate to a form with CAPTCHA
   - Answer the question correctly → Should proceed
   - Answer incorrectly → Should show error

2. **Visual CAPTCHA**:
   - Navigate to a form with visual CAPTCHA
   - Enter the code correctly → Should proceed
   - Enter incorrectly → Should show error
   - Click refresh → Should generate new code

### Automated Testing

```php
// Example test
public function testCaptchaVerification()
{
    $captchaService = static::getContainer()->get(CaptchaService::class);
    
    $captcha = $captchaService->generateCaptcha();
    $answer = $captcha['answer'];
    
    $this->assertTrue($captchaService->verifyCaptcha($answer));
    $this->assertFalse($captchaService->verifyCaptcha('wrong answer'));
}
```

## Troubleshooting

### CAPTCHA Not Displaying
- Check that the route `/captcha/generate` is accessible
- Verify session is working correctly
- Check browser console for JavaScript errors

### CAPTCHA Always Fails
- Verify session storage is working
- Check that answers are being stored correctly
- Ensure case-insensitive comparison is working

### Visual CAPTCHA Not Refreshing
- Check JavaScript console for errors
- Verify AJAX endpoint is accessible
- Ensure jQuery or vanilla JS is loaded

## Future Enhancements

Potential improvements for the CAPTCHA system:

1. **Audio CAPTCHA**: For visually impaired users
2. **Difficulty Levels**: Easy, medium, hard questions
3. **Localization**: Questions in multiple languages
4. **Rate Limiting**: Limit CAPTCHA attempts per IP
5. **Analytics**: Track CAPTCHA success rates
6. **Honeypot Fields**: Additional bot detection
7. **reCAPTCHA Integration**: Optional Google reCAPTCHA fallback

## Best Practices

1. **Use Appropriate Type**: Visual for high-security, questions for better UX
2. **Clear Instructions**: Always provide clear guidance
3. **Error Messages**: Show helpful error messages
4. **Refresh Option**: Always provide a way to get a new CAPTCHA
5. **Accessibility**: Ensure all users can complete the CAPTCHA
6. **Testing**: Regularly test CAPTCHA functionality
7. **Monitoring**: Monitor CAPTCHA failure rates

## Support

For issues or questions about the CAPTCHA system:
- Check this documentation
- Review the source code in `src/Service/CaptchaService.php`
- Test with different browsers and devices
- Check session configuration in `config/packages/framework.yaml`
