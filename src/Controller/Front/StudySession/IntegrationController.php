<?php

namespace App\Controller\Front\StudySession;

use App\Service\StudySession\YouTubeApiClient;
use App\Service\StudySession\WikipediaApiClient;
use App\Service\StudySession\AIRecommendationService;
use App\Repository\StudySession\StudySessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/study-session/integration')]
#[IsGranted('ROLE_STUDENT')]
class IntegrationController extends AbstractController
{
    public function __construct(
        private YouTubeApiClient $youtubeApiClient,
        private WikipediaApiClient $wikipediaApiClient,
        private AIRecommendationService $aiRecommendationService,
        private StudySessionRepository $studySessionRepository
    ) {}

    /**
     * Search for YouTube videos related to a topic
     */
    #[Route('/youtube/search', name: 'integration_youtube_search', methods: ['GET'])]
    public function youtubeSearch(Request $request): Response
    {
        $query = $request->query->get('q', '');
        
        if (empty(trim($query))) {
            $this->addFlash('warning', 'Please enter a search term.');
            return $this->render('front/study_session/youtube_search.html.twig', [
                'videos' => [],
                'query' => '',
                'error' => null,
            ]);
        }

        try {
            $videos = $this->youtubeApiClient->searchVideos($query);
            
            $error = null;
            if (empty($videos)) {
                $error = 'No videos found or YouTube API is temporarily unavailable. Please try again later.';
            }

            return $this->render('front/study_session/youtube_search.html.twig', [
                'videos' => $videos,
                'query' => $query,
                'error' => $error,
            ]);
        } catch (\Exception $e) {
            return $this->render('front/study_session/youtube_search.html.twig', [
                'videos' => [],
                'query' => $query,
                'error' => 'YouTube API is currently unavailable. Please try again later.',
            ]);
        }
    }

    /**
     * Search for Wikipedia articles related to a topic
     */
    #[Route('/wikipedia/search', name: 'integration_wikipedia_search', methods: ['GET'])]
    public function wikipediaSearch(Request $request): Response
    {
        $query = $request->query->get('q', '');
        
        if (empty(trim($query))) {
            $this->addFlash('warning', 'Please enter a search term.');
            return $this->render('front/study_session/wikipedia_search.html.twig', [
                'articles' => [],
                'query' => '',
                'error' => null,
            ]);
        }

        try {
            $articles = $this->wikipediaApiClient->searchArticles($query);
            
            $error = null;
            if (empty($articles)) {
                $error = 'No articles found or Wikipedia API is temporarily unavailable. Please try again later.';
            }

            return $this->render('front/study_session/wikipedia_search.html.twig', [
                'articles' => $articles,
                'query' => $query,
                'error' => $error,
            ]);
        } catch (\Exception $e) {
            return $this->render('front/study_session/wikipedia_search.html.twig', [
                'articles' => [],
                'query' => $query,
                'error' => 'Wikipedia API is currently unavailable. Please try again later.',
            ]);
        }
    }

    /**
     * Get weather-based study suggestions
     * Note: WeatherApiClient is not yet implemented (Task 9)
     */
    #[Route('/weather/suggestions', name: 'integration_weather_suggestions', methods: ['GET'])]
    public function weatherSuggestions(Request $request): Response
    {
        // Weather API integration is pending (Task 9)
        // For now, return a placeholder response
        return $this->render('front/study_session/weather_suggestions.html.twig', [
            'weather' => null,
            'suggestions' => [],
            'error' => 'Weather integration is not yet available. This feature will be implemented in Task 9.',
        ]);
    }

    /**
     * Get AI-powered study recommendations based on recent sessions
     */
    #[Route('/ai/recommendations', name: 'integration_ai_recommendations', methods: ['GET'])]
    public function aiRecommendations(): Response
    {
        $user = $this->getUser();
        
        try {
            // Get recent sessions (last 10 completed sessions)
            $recentSessions = $this->studySessionRepository->findBy(
                ['user' => $user],
                ['completedAt' => 'DESC'],
                10
            );

            // Filter to only completed sessions
            $completedSessions = array_filter($recentSessions, function($session) {
                return $session->getCompletedAt() !== null;
            });

            if (empty($completedSessions)) {
                $this->addFlash('info', 'Complete some study sessions to receive personalized recommendations.');
                return $this->render('front/study_session/ai_recommendations.html.twig', [
                    'recommendations' => [],
                    'error' => 'No completed sessions found. Complete some study sessions to get personalized recommendations.',
                ]);
            }

            $recommendations = $this->aiRecommendationService->generateStudyRecommendations(
                $user,
                $completedSessions
            );

            return $this->render('front/study_session/ai_recommendations.html.twig', [
                'recommendations' => $recommendations,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            return $this->render('front/study_session/ai_recommendations.html.twig', [
                'recommendations' => [],
                'error' => 'AI service is temporarily unavailable. Please try again later.',
            ]);
        }
    }

    /**
     * Summarize notes using AI
     */
    #[Route('/ai/summarize', name: 'integration_ai_summarize', methods: ['POST'])]
    public function summarizeNotes(Request $request): JsonResponse
    {
        $noteContent = $request->request->get('content', '');
        
        if (empty(trim($noteContent))) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Note content is required.',
            ], 400);
        }

        try {
            $summary = $this->aiRecommendationService->summarizeNotes($noteContent);
            
            return new JsonResponse([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to generate summary. Please try again later.',
            ], 500);
        }
    }

    /**
     * Generate quiz questions from content using AI
     */
    #[Route('/ai/quiz', name: 'integration_ai_quiz', methods: ['POST'])]
    public function generateQuiz(Request $request): JsonResponse
    {
        $content = $request->request->get('content', '');
        $questionCount = (int) $request->request->get('question_count', 5);
        
        // Validate question count (5-10)
        $questionCount = max(5, min(10, $questionCount));
        
        if (empty(trim($content))) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Content is required to generate quiz.',
            ], 400);
        }

        if (strlen($content) < 100) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Content is too short to generate a meaningful quiz. Please provide at least 100 characters.',
            ], 400);
        }

        try {
            $questions = $this->aiRecommendationService->generateQuiz($content, $questionCount);
            
            if (empty($questions)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Failed to generate quiz questions. The content may not be suitable for quiz generation.',
                ], 500);
            }

            // Convert QuizQuestion objects to arrays for JSON response
            $questionsArray = array_map(function($question) {
                return [
                    'question' => $question->question,
                    'options' => $question->options,
                    'correctAnswer' => $question->correctAnswer,
                    'type' => $question->type,
                    'explanation' => $question->explanation,
                ];
            }, $questions);

            return new JsonResponse([
                'success' => true,
                'questions' => $questionsArray,
                'count' => count($questionsArray),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to generate quiz. Please try again later.',
            ], 500);
        }
    }
}
