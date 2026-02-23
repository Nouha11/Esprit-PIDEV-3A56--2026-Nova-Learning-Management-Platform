<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\TutorProfileRepository;
use App\Repository\StudySession\CourseRepository;
use App\Repository\StudySession\PlanningRepository;
use App\Repository\StudySession\StudySessionRepository;
use App\Repository\StudySession\EnrollmentRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        UserRepository $userRepository,
        StudentProfileRepository $studentRepository,
        TutorProfileRepository $tutorRepository
    ): Response {
        // Get statistics
        $totalUsers = count($userRepository->findAll());
        $totalStudents = count($studentRepository->findAll());
        $totalTutors = count($tutorRepository->findAll());
        
        // Count by role using DQL to avoid issues
        $adminCount = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'ROLE_ADMIN')
            ->getQuery()
            ->getSingleScalarResult();
            
        $studentCount = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'ROLE_STUDENT')
            ->getQuery()
            ->getSingleScalarResult();
            
        $tutorCount = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'ROLE_TUTOR')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Get recent users with proper entity hydration
        $recentUsers = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalStudents' => $totalStudents,
            'totalTutors' => $totalTutors,
            'adminCount' => $adminCount,
            'studentCount' => $studentCount,
            'tutorCount' => $tutorCount,
            'recentUsers' => $recentUsers,
        ]);
    }

    #[Route('/api/learning-stats', name: 'admin_api_learning_stats', methods: ['GET'])]
    public function learningStats(
        CourseRepository $courseRepository,
        PlanningRepository $planningRepository,
        StudySessionRepository $studySessionRepository,
        EnrollmentRequestRepository $enrollmentRequestRepository
    ): JsonResponse {
        try {
            $totalCourses = $courseRepository->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $totalPlanningSessions = $planningRepository->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $totalStudySessions = $studySessionRepository->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->getQuery()
                ->getSingleScalarResult();

            // Count approved enrollments
            $totalEnrollments = $enrollmentRequestRepository->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.status = :status')
                ->setParameter('status', 'APPROVED')
                ->getQuery()
                ->getSingleScalarResult();

            return new JsonResponse([
                'success' => true,
                'totalCourses' => (int)$totalCourses,
                'totalPlanningSessions' => (int)$totalPlanningSessions,
                'totalStudySessions' => (int)$totalStudySessions,
                'totalEnrollments' => (int)$totalEnrollments,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'totalCourses' => 0,
                'totalPlanningSessions' => 0,
                'totalStudySessions' => 0,
                'totalEnrollments' => 0,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
