# CAPTCHA User Guide

## What is CAPTCHA?

CAPTCHA (Completely Automated Public Turing test to tell Computers and Humans Apart) is a security feature that helps prevent automated bots from accessing your platform. NOVA uses educational, user-friendly questions that are easy for humans but difficult for bots.

## Where You'll See CAPTCHA

### 1. Login Page (`/login`)

When logging in, you'll see a security question above the login button:

```
┌─────────────────────────────────────────┐
│ 🛡️ Security Question                    │
├─────────────────────────────────────────┤
│ ℹ️ What is 5 + 3?                       │
│                                         │
│ [Enter your answer____________]         │
│                                         │
│ ℹ️ Answer the question above to verify │
│   you're human                          │
└─────────────────────────────────────────┘
```

### 2. Student Registration (`/signup/student`)

When creating a student account, you'll see the CAPTCHA before the submit button:

```
┌─────────────────────────────────────────┐
│ Personal Information                    │
│ [First Name] [Last Name]                │
│ [University]                            │
│ [Major] [Academic Level]                │
├─────────────────────────────────────────┤
│ 🛡️ Security Question                    │
├─────────────────────────────────────────┤
│ ℹ️ How many days are in a week?        │
│                                         │
│ [Enter your answer____________]         │
└─────────────────────────────────────────┘
│ [Create Student Account]                │
└─────────────────────────────────────────┘
```

### 3. Tutor Registration (`/signup/tutor`)

When creating a tutor account, you'll see the CAPTCHA before the submit button:

```
┌─────────────────────────────────────────┐
│ Professional Information                │
│ [Expertise]                             │
│ [Qualifications]                        │
│ [Years of Experience] [Hourly Rate]     │
├─────────────────────────────────────────┤
│ 🛡️ Security Question                    │
├─────────────────────────────────────────┤
│ ℹ️ Complete the sequence: 2,4,6,8,__   │
│                                         │
│ [Enter your answer____________]         │
└─────────────────────────────────────────┘
│ [Create Tutor Account]                  │
└─────────────────────────────────────────┘
```

## Types of Questions

### Math Questions
- "What is 5 + 3?" → Answer: 8
- "What is 12 - 7?" → Answer: 5
- "What is 4 × 3?" → Answer: 12
- "What is 15 ÷ 3?" → Answer: 5

### General Knowledge
- "How many days are in a week?" → Answer: 7
- "How many months are in a year?" → Answer: 12
- "What color is the sky on a clear day?" → Answer: blue
- "How many letters are in the word 'NOVA'?" → Answer: 4

### Pattern Recognition
- "Complete the sequence: 2, 4, 6, 8, __" → Answer: 10
- "Complete the sequence: 5, 10, 15, 20, __" → Answer: 25

### Logic Questions
- "If today is Monday, what day is tomorrow?" → Answer: tuesday
- "What comes after 'one, two, three'?" → Answer: four

## How to Answer

1. **Read the question carefully**
2. **Type your answer** in the text field
3. **Case doesn't matter** - "Blue", "blue", "BLUE" are all correct
4. **Numbers or words** - Both work for numeric answers (7 or seven)
5. **Click submit** - Your answer will be verified

## What Happens If You Get It Wrong?

- ❌ You'll see an error message: "Invalid CAPTCHA answer. Please try again."
- 🔄 A new question will be generated automatically
- ✅ Simply answer the new question and resubmit

## Tips

- ✅ **Take your time** - There's no rush
- ✅ **Read carefully** - Make sure you understand the question
- ✅ **Simple answers** - Just type the number or word
- ✅ **No special formatting** - Just plain text
- ✅ **Case insensitive** - Uppercase or lowercase both work

## Why Do We Use CAPTCHA?

1. **Security**: Prevents automated bots from creating fake accounts
2. **Spam Prevention**: Stops spam registrations and login attempts
3. **Brute Force Protection**: Slows down password guessing attacks
4. **User-Friendly**: Educational questions are easy for humans
5. **No Tracking**: Unlike Google reCAPTCHA, we don't track you

## Accessibility

Our CAPTCHA system is designed to be accessible:

- ✅ Clear, readable questions
- ✅ Simple language
- ✅ Keyboard navigation support
- ✅ Screen reader friendly
- ✅ High contrast colors
- ✅ No time limits

## Troubleshooting

### "Invalid CAPTCHA answer" Error

**Problem**: You keep getting the error even with correct answers

**Solutions**:
1. Check for typos in your answer
2. Make sure you're answering the current question (not a previous one)
3. Try refreshing the page to get a new question
4. Clear your browser cache and cookies
5. Try a different browser

### CAPTCHA Not Showing

**Problem**: You don't see the CAPTCHA question

**Solutions**:
1. Refresh the page
2. Clear browser cache
3. Enable JavaScript (required for some features)
4. Check if you're blocking cookies
5. Try a different browser

### Can't Read the Question

**Problem**: The question is unclear or confusing

**Solutions**:
1. Refresh the page to get a different question
2. Take your time to read it carefully
3. Contact support if you consistently have issues

## Privacy

- ✅ No personal data collected by CAPTCHA
- ✅ No tracking cookies
- ✅ No third-party services (like Google reCAPTCHA)
- ✅ Session-based only
- ✅ Cleared after successful verification

## Support

If you have issues with CAPTCHA:

1. **Try refreshing** the page first
2. **Clear your browser cache** and cookies
3. **Try a different browser** (Chrome, Firefox, Edge)
4. **Contact support** if problems persist

---

**Remember**: CAPTCHA is here to protect you and the platform from malicious bots. It only takes a few seconds to answer!
