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
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof \App\Entity\users\User) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }
        
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
            
            // Check if response mentions external platforms (fallback protection)
            $externalPlatforms = ['coursera', 'edx', 'udemy', 'khan academy', 'linkedin learning', 'udacity', 'pluralsight'];
            $responseLower = strtolower($response);
            $mentionsExternal = false;
            
            foreach ($externalPlatforms as $platform) {
                if (strpos($responseLower, $platform) !== false) {
                    $mentionsExternal = true;
                    break;
                }
            }
            
            // If AI mentioned external platforms, provide fallback response with actual courses
            if ($mentionsExternal || (stripos($question, 'course') !== false && stripos($question, 'best') !== false)) {
                if (!empty($studyData['availableCourses'])) {
                    $courseNames = array_slice(array_map(fn($c) => $c['name'], $studyData['availableCourses']), 0, 3);
                    $response = "Hey {$studentName}! 📚 Check out '" . implode("', '", $courseNames) . "' on our NOVA platform. These are great courses to get you started! ✨";
                }
            }
            
            // Ensure response starts with student's name
            if (!preg_match('/^(Hey|Hi|Hello)\s+' . preg_quote($studentName, '/') . '/i', $response)) {
                // If response doesn't start with greeting + name, prepend it
                $response = "Hey {$studentName}! " . $response;
            }
            
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

        // Get ALL available courses in the platform (not just user's courses)
        $allCourses = $this->courseRepository->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(20) // Get top 20 courses
            ->getQuery()
            ->getResult();

        // Get user's enrolled courses
        $enrolledCourses = $this->courseRepository->createQueryBuilder('c')
            ->where('c.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $sessionCount = count($recentSessions);
        $courseCount = count($enrolledCourses);

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
            'enrolledCourses' => array_map(fn($c) => [
                'name' => $c->getCourseName(),
                'description' => $c->getDescription()
            ], $enrolledCourses),
            'availableCourses' => array_map(fn($c) => [
                'name' => $c->getCourseName(),
                'description' => $c->getDescription(),
                'category' => $c->getCategory() ?? 'General'
            ], $allCourses),
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
        $enrolledCoursesInfo = '';
        if (!empty($studyData['enrolledCourses'])) {
            $enrolledCoursesInfo = "\n\n=== STUDENT'S ENROLLED COURSES ===\n";
            foreach ($studyData['enrolledCourses'] as $course) {
                $enrolledCoursesInfo .= "✓ {$course['name']}: {$course['description']}\n";
            }
        }

        $availableCoursesInfo = '';
        $coursesList = '';
        if (!empty($studyData['availableCourses'])) {
            $availableCoursesInfo = "\n\n=== AVAILABLE COURSES ON NOVA PLATFORM (USE THESE ONLY!) ===\n";
            foreach ($studyData['availableCourses'] as $idx => $course) {
                $category = $course['category'] ?? 'General';
                $availableCoursesInfo .= ($idx + 1) . ". {$course['name']} [{$category}]: {$course['description']}\n";
                $coursesList .= "- {$course['name']}\n";
            }
            $availableCoursesInfo .= "\n⚠️ CRITICAL: When asked about courses, you MUST recommend ONLY from the list above.\n";
            $availableCoursesInfo .= "⚠️ NEVER mention: Coursera, edX, Udemy, Khan Academy, or any external platform.\n";
            $availableCoursesInfo .= "⚠️ If asked for 'best courses', mention 2-3 courses from the NOVA platform list above.\n";
        }

        $sessionsInfo = '';
        if (!empty($studyData['recentSessions'])) {
            $sessionsInfo = "\n\n=== RECENT STUDY SESSIONS ===\n";
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
You are Study Buddy AI for the NOVA learning platform. You help students with courses available ON THIS PLATFORM ONLY.

=== STUDENT INFO ===
Name: {$studentName}
- Total Study Sessions: {$studyData['sessionCount']}
- Completed Sessions: {$studyData['completedSessions']}
- Total Study Time: {$studyData['totalStudyTime']} minutes
- Average Session Duration: {$studyData['avgSessionDuration']} minutes
- Active Courses: {$studyData['courseCount']}
{$enrolledCoursesInfo}{$availableCoursesInfo}{$sessionsInfo}

=== CRITICAL RULES FOR COURSE RECOMMENDATIONS ===
1. You can ONLY recommend courses from the "AVAILABLE COURSES ON NOVA PLATFORM" list above
2. NEVER mention external platforms (Coursera, edX, Udemy, Khan Academy, LinkedIn Learning, etc.)
3. When asked about "best courses" or course recommendations, pick 2-3 from the NOVA list
4. Use the actual course names from the list above
5. If no courses match the request, say "We don't have that specific course yet, but check out [course from list]"

=== YOUR ROLE ===
- Recommend courses from NOVA platform only
- Provide study tips and learning strategies
- Offer motivation and encouragement
- Answer questions about study sessions
- Address the student by their name ({$studentName}) when appropriate

=== RESPONSE FORMAT ===
- ALWAYS start with "Hey {$studentName}!" or "Hi {$studentName}!" 
- Respond in 2-3 SHORT sentences (maximum 60 words)
- Use emojis naturally (💡 📚 ⏰ 📝 ✨ 🎯 💪 🌟)
- NO numbered lists, NO bullet points, NO markdown
- Just plain conversational sentences

=== EXAMPLE RESPONSES ===
Q: "What are the best courses?"
A: "Hey {$studentName}! 📚 I recommend checking out 'php' for programming or 'first course' to get started. Both are popular on NOVA and great for building skills ✨"

Q: "Recommend a course"
A: "Hi {$studentName}! 💡 Try 'test' or 'php rocks' - they're both excellent choices on our platform. Pick one that matches your interests! 🎯"

Q: "Give me study tips"
A: "Hey {$studentName}! 💡 Try the Pomodoro technique with 25-minute focused blocks. Take short breaks to maintain your energy levels ⏰"

Q: "I'm feeling overwhelmed"
A: "Hey {$studentName}! 💪 You're doing great with {$studyData['completedSessions']} completed sessions. Break your work into smaller chunks and celebrate small wins 🌟"

Remember: ONLY recommend courses from the NOVA platform list above. Never mention external platforms.

Respond naturally in 2-3 sentences.
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