# Study Buddy AI Assistant

## Overview
Study Buddy AI is a dedicated learning companion that helps students with their study sessions, courses, and learning strategies. It appears as a floating chat widget on study-related pages.

## Features

### Chat Interface
- **Floating Widget**: Green/teal themed button in bottom-right corner
- **Separate Chat History**: Independent from the game AI assistant (stored in `study_buddy_chat_history`)
- **Context-Aware**: Uses student's actual study data for personalized responses

### Quick Actions
1. **Study Tips**: Get effective study strategies
2. **Summarize Notes**: Learn about note summarization techniques
3. **Generate Quiz**: Understand quiz generation from study materials
4. **Study Schedule**: Get help creating effective study schedules

### AI Capabilities
- Personalized study recommendations based on:
  - Total study sessions and completion rate
  - Average session duration
  - Recent study patterns (mood, energy levels)
  - Enrolled courses
- Study tips and learning strategies
- Time management advice
- Motivation and encouragement
- Course-specific guidance

## Where It Appears
The Study Buddy widget is available on all study and course-related pages:

### Course Pages
- Course Index (`/courses`)
- Course Details (`/courses/{id}`)

### Study Session Pages
- Study Sessions Index (`/study-session`)
- Study Session Details (`/study-session/{id}`)
- Study Session Calendar (`/study-session/calendar`)
- Study Session Analytics (`/analytics`)

### Integration Pages
- YouTube Search (`/study-session/integration/youtube/search`)
- Wikipedia Search (`/study-session/integration/wikipedia/search`)

### Planning & Organization
- Planning Index (`/planning`)
- Planning Details (`/planning/{id}`)
- Tags Management (`/tag`)

## Technical Details

### Architecture
```
User Browser
    ↓
Study Buddy Widget (JavaScript)
    ↓
POST /study-buddy/chat
    ↓
StudyBuddyController
    ↓
HuggingFaceService (Qwen 2.5 7B)
    ↓
AI Response
```

### Components
- **Widget Template**: `templates/components/study_buddy_widget.html.twig`
- **Controller**: `src/Controller/Front/StudySession/StudyBuddyController.php`
- **AI Service**: `src/Service/game/HuggingFaceService.php`
- **Route**: `/study-buddy/chat` (POST)

### API Endpoint
**POST** `/study-buddy/chat`

**Request Body:**
```json
{
  "question": "How can I improve my study habits?"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Here are some effective study habits: 1) Create a consistent schedule..."
}
```

### Color Scheme
- **Primary Gradient**: `#11998e` to `#38ef7d` (green/teal)
- **Toggle Button**: Green gradient with pulse animation
- **User Messages**: `#11998e` (teal background)
- **AI Messages**: White/dark theme adaptive
- **Quick Action Buttons**: Bootstrap themed (success, info, primary, warning)

### Data Storage
- **Storage Key**: `study_buddy_chat_history`
- **Storage Type**: Browser localStorage
- **Maximum Messages**: 50 (older messages automatically removed)
- **Data Structure**:
```json
{
  "messages": [
    {"text": "Hello!", "type": "user"},
    {"text": "Hi! How can I help?", "type": "study"}
  ],
  "timestamp": 1234567890
}
```

### AI Service Configuration
- **Model**: `qwen/qwen2.5-7b-instruct`
- **Temperature**: 0.3 (deterministic responses)
- **Max Tokens**: 300
- **Timeout**: 20 seconds
- **System Prompt**: Study-focused with student data context

## Differences from Game AI Assistant

| Feature | Game AI Assistant | Study Buddy AI |
|---------|------------------|----------------|
| **Theme** | Purple gradient | Green/teal gradient |
| **Icon** | Robot | Book |
| **Name** | AI Assistant | Study Buddy AI |
| **Focus** | Games, rewards, XP | Study sessions, courses, learning |
| **Storage Key** | `ai_chat_history` | `study_buddy_chat_history` |
| **Pages** | Game-related pages | Study-related pages |
| **Quick Actions** | Rewards, progress, games | Study tips, notes, quiz, schedule |

## Context Data Provided to AI

The Study Buddy AI receives the following student data for personalized responses:

### Study Session Statistics
- Total number of study sessions
- Number of completed sessions
- Total study time (in minutes)
- Average session duration
- Completion rate

### Course Information
- List of enrolled courses (up to 5 most recent)
- Course names and descriptions

### Recent Study Patterns
- Last 5 study sessions with:
  - Duration
  - Status (completed, in progress, etc.)
  - Mood rating
  - Energy level

### Example Context
```php
[
    'sessionCount' => 15,
    'completedSessions' => 12,
    'totalStudyTime' => 450,
    'avgSessionDuration' => 30,
    'courseCount' => 3,
    'courses' => [
        ['name' => 'Mathematics', 'description' => 'Algebra and Calculus'],
        ['name' => 'Physics', 'description' => 'Mechanics and Thermodynamics']
    ],
    'recentSessions' => [
        ['duration' => 45, 'status' => 'COMPLETED', 'mood' => 'GOOD', 'energyLevel' => 'HIGH']
    ]
]
```

## AI Response Guidelines

The Study Buddy AI follows these principles:

1. **Concise**: Responses under 150 words
2. **Actionable**: Provides specific, practical advice
3. **Data-Driven**: Uses actual student data when available
4. **Encouraging**: Maintains positive, supportive tone
5. **Focused**: Stays on topic of learning and studying
6. **Honest**: Doesn't make up data or statistics

### Response Examples

**Question**: "How can I improve my study habits?"

**Response**: "Based on your 15 study sessions with an average of 30 minutes each, you're building good consistency! To improve further: 1) Try the Pomodoro Technique (25 min focus + 5 min break), 2) Study during your high-energy times, 3) Review notes within 24 hours of learning. Your completion rate of 80% is excellent - keep it up! 📚"

**Question**: "I'm feeling unmotivated to study"

**Response**: "It's normal to feel this way sometimes! Here's what can help: 1) Start with just 10 minutes - often the hardest part is beginning, 2) Study your favorite subject first to build momentum, 3) Reward yourself after completing a session. You've completed 12 sessions already - that shows you have the discipline! What subject are you working on today?"

## Usage Example

### Basic Chat
1. Navigate to any study session or course page
2. Click the green book icon in the bottom-right corner
3. The chat window opens with a welcome message
4. Use quick actions or type your own questions

### Quick Actions
- **Study Tips**: Click to get effective study strategies
- **Summarize Notes**: Learn about note summarization techniques
- **Generate Quiz**: Understand quiz generation from study materials
- **Study Schedule**: Get help creating effective study schedules

### Sample Questions
- "How can I improve my study habits?"
- "What's the best way to prepare for exams?"
- "How should I organize my study schedule?"
- "Give me tips for staying focused while studying"
- "How can I make my study sessions more effective?"

### Chat Features
- **Persistent History**: Your last 50 messages are saved in your browser
- **Clear Chat**: Click the trash icon to clear all chat history
- **Close/Reopen**: Click the X to close, click the book icon to reopen
- **Dark Mode Support**: Automatically adapts to your theme preference

## Future Enhancements

### Planned Features
- **Note Summarization API**: Direct integration for summarizing study notes
- **Quiz Generation**: Create practice quizzes from study materials
- **Study Schedule Optimizer**: AI-powered schedule recommendations
- **Progress Analytics**: Visual charts and insights
- **Study Reminders**: Smart notifications based on patterns
- **Collaborative Study**: Connect with study partners
- **Resource Recommendations**: Suggest relevant learning materials

### Potential Improvements
- Voice input/output support
- Multi-language support
- Integration with calendar apps
- Spaced repetition reminders
- Study goal tracking
- Performance predictions

## Troubleshooting

### Widget Not Appearing
1. Check if you're on a study-related page (courses, sessions, planning)
2. Clear browser cache and reload
3. Check browser console for JavaScript errors
4. Verify you're logged in as a student

### Chat Not Responding
1. Check internet connection
2. Verify Hugging Face API is configured correctly
3. Check API key in `.env` file: `HUGGING_FACE_API_KEY`
4. Review server logs for errors

### Chat History Not Saving
1. Check if localStorage is enabled in browser
2. Try clearing localStorage and starting fresh
3. Check browser privacy settings

### Styling Issues
1. Clear browser cache
2. Check if dark/light theme is properly detected
3. Verify CSS is loading correctly

## Removed Features
The old AI recommendations page (`/study-session/integration/ai/recommendations`) has been replaced by the Study Buddy widget, providing a more integrated and accessible experience.

## Support

For issues or questions:
1. Check this documentation
2. Review the code in `src/Controller/Front/StudySession/StudyBuddyController.php`
3. Check browser console for errors
4. Review server logs in `var/log/`

## Version History

### v1.0.0 (Current)
- Initial release
- Chat interface with quick actions
- Context-aware responses
- Separate chat history from game AI
- Available on all study-related pages
- Dark/light theme support
