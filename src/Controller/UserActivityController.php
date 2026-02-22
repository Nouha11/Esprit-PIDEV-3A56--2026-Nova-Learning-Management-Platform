<?php

namespace App\Controller;

use App\Service\UserActivityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/activities')]
#[IsGranted('ROLE_USER')]
class UserActivityController extends AbstractController
{
    public function __construct(
        private UserActivityService $activityService
    ) {}

    #[Route('', name: 'app_user_activities', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $activities = $this->activityService->getRecentActivities($user, 50);
        $stats = $this->activityService->getActivityStats($user);

        return $this->render('user_activity/index.html.twig', [
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }
}
