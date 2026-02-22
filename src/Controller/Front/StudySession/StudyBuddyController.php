<?php

namespace App\Controller\Front\StudySession;

use App\Service\game\HuggingFaceService;
use App\Repository\StudySession\StudySessionRepository;
use App\Repository\StudySession\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/study-buddy')]
#[IsGranted('ROLE_STUDENT')]
class StudyBuddyController extends AbstractController
{
    public function __construct(
        private HuggingFaceService $huggingFaceService,
        private StudySessionRepository $studySessionRepository,
        private CourseRepository $courseRepository
    ) {
    }

    /**
     * Handle Study Buddy chat messages
     */
    #[Route('/chat', name: 'study_buddy_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        if (empty($question)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Please provide a question.'
            ]);
        }

        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Student profile not found.'
            ]);
        }

        try {
            // Get student's study data
            $studyData = $this->getStudentStudyData($user);
            
            // Get student's name
            $studentName = $student->getFirstName() ?? $user->getUsername();

            // Build context-aware prompt
            $systemPrompt = $this->buildStudyBuddyPrompt($studyData, $studentName);

            // Get AI response using HuggingFaceService chat method
            $response = $this->huggingFaceService->chat($question, $systemPrompt);
            
            // Clean up response: remove markdown formatting and truncate if too long
            $response = $this->cleanResponse($response);

            return new JsonResponse([
                'success' => true,
                'message' => $response
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Get student study data for context
     */
    private function getStudentStudyData($user): array
    {
        // Get recent study sessions
        $recentSessions = $this->studySessionRepository->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.startedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Get enrolled courses
        $courses = $this->courseRepository->createQueryBuilder('c')
            ->where('c.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $sessionCount = count($recentSessions);
        $courseCount = count($courses);

        $totalStudyTime = 0;
        $completedSessions = 0;
        foreach ($recentSessions as $session) {
            if ($session->getDuration()) {
                $totalStudyTime += $session->getDuration();
            }
            if ($session->getCompletedAt() !== null) {
                $completedSessions++;
            }
        }

        $avgSessionDuration = $sessionCount > 0 ? round($totalStudyTime / $sessionCount) : 0;

        return [
            'sessionCount' => $sessionCount,
            'courseCount' => $courseCount,
            'totalStudyTime' => $totalStudyTime,
            'avgSessionDuration' => $avgSessionDuration,
            'completedSessions' => $completedSessions,
            'courses' => array_map(fn($c) => [
                'name' => $c->getName(),
                'description' => $c->getDescription()
            ], $courses),
            'recentSessions' => array_map(fn($s) => [
                'duration' => $s->getDuration(),
                'completed' => $s->getCompletedAt() !== null,
                'mood' => $s->getMood(),
                'energyLevel' => $s->getEnergyLevel()
            ], array_slice($recentSessions, 0, 5))
        ];
    }

    /**
     * Build Study Buddy system prompt
     */
    private function buildStudyBuddyPrompt(array $studyData, string $studentName): string
    {
        $coursesInfo = '';
        if (!empty($studyData['courses'])) {
            $coursesInfo = "\n\nStudent's Courses:\n";
            foreach ($studyData['courses'] as $course) {
                $coursesInfo .= "- {$course['name']}: {$course['description']}\n";
            }
        }

        $sessionsInfo = '';
        if (!empty($studyData['recentSessions'])) {
            $sessionsInfo = "\n\nRecent Study Sessions:\n";
            foreach ($studyData['recentSessions'] as $idx => $session) {
                $sessionsInfo .= sprintf(
                    "Session %d: %d min, Completed: %s, Mood: %s, Energy: %s\n",
                    $idx + 1,
                    $session['duration'] ?? 0,
                    $session['completed'] ? 'Yes' : 'No',
                    $session['mood'] ?? 'N/A',
                    $session['energyLevel'] ?? 'N/A'
                );
            }
        }

        return <<<PROMPT
You are Study Buddy AI, a friendly and knowledgeable learning companion designed to help students with their studies.

=== STUDENT INFO ===
Name: {$studentName}
- Total Study Sessions: {$studyData['sessionCount']}
- Completed Sessions: {$studyData['completedSessions']}
- Total Study Time: {$studyData['totalStudyTime']} minutes
- Average Session Duration: {$studyData['avgSessionDuration']} minutes
- Active Courses: {$studyData['courseCount']}
{$coursesInfo}{$sessionsInfo}

=== YOUR ROLE ===
- Provide study tips, learning strategies, and academic advice
- Help with note summarization and quiz generation concepts
- Suggest effective study schedules and time management
- Offer motivation and encouragement
- Answer questions about courses and study sessions
- Give personalized recommendations based on their study data
- Address the student by their name ({$studentName}) when appropriate

=== CRITICAL RESPONSE FORMAT ===
YOU MUST RESPOND IN EXACTLY 2-3 SHORT SENTENCES. NO MORE.
- Start with a friendly greeting using their name when appropriate
- Add relevant emojis (💡 📚 ⏰ 📝 ✨ 🎯 💪 🌟) to make responses engaging
- NO numbered lists (#1, #2, etc.)
- NO bullet points
- NO step-by-step guides
- NO markdown formatting (###, **, etc.)
- Just 2-3 plain sentences with practical advice
- Maximum 60 words total

EXAMPLE GOOD RESPONSES:
Q: "Give me study tips"
A: "Hey {$studentName}! 💡 Based on your {$studyData['sessionCount']} study sessions, try the Pomodoro technique with 25-minute focused blocks. Take short breaks between sessions to maintain your energy levels ⏰"

Q: "How do I create a study schedule?"
A: "Hi {$studentName}! 📅 Start by blocking out your most productive hours for difficult subjects. Schedule shorter sessions for easier topics and always include 5-10 minute breaks ✨"

Q: "I'm feeling overwhelmed"
A: "{$studentName}, you're doing great with {$studyData['completedSessions']} completed sessions! 💪 Break your work into smaller chunks and celebrate small wins. Remember, consistent progress beats perfection 🌟"

=== RULES ===
- Keep responses under 60 words
- Use simple, conversational language
- Be encouraging but brief
- Reference their actual data when relevant
- Use emojis naturally (1-2 per response)
- Address them by name occasionally
- NO lists, NO formatting, just plain helpful sentences

Respond naturally and helpfully to the student's question in 2-3 sentences only.
PROMPT;
    }

    /**
     * Clean AI response by removing markdown and limiting length
     */
    private function cleanResponse(string $response): string
    {
        // Remove markdown headers (###, ##, #)
        $response = preg_replace('/^#{1,6}\s+/m', '', $response);
        
        // Remove bold/italic markdown (**, *, __)
        $response = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $response);
        $response = preg_replace('/(\*|_)(.*?)\1/', '$2', $response);
        
        // Remove numbered lists (1., 2., etc.)
        $response = preg_replace('/^\d+\.\s+/m', '', $response);
        
        // Remove bullet points (-, *, •)
        $response = preg_replace('/^[\-\*•]\s+/m', '', $response);
        
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($response), -1, PREG_SPLIT_NO_EMPTY);
        
        // Keep only first 3 sentences
        if (count($sentences) > 3) {
            $sentences = array_slice($sentences, 0, 3);
        }
        
        // Join and clean up extra whitespace
        $result = implode(' ', $sentences);
        $result = preg_replace('/\s+/', ' ', $result);
        
        return trim($result);
    }
}