<?php

namespace App\Controller;

use App\Entity\users\User; // <-- ADDED THIS IMPORT
use App\Service\UsernameChangeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/settings/username')]
class UsernameChangeController extends AbstractController
{
    public function __construct(
        private UsernameChangeService $usernameChangeService
    ) {}

    #[Route('', name: 'app_username_change', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function change(Request $request): Response
    {
        // ADDED: PHPDoc to make VS Code Intelephense happy
        /** @var User $user */
        $user = $this->getUser();
        
        // ADDED: Type verification to make PHPStan happy
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $currentUsername = $user->getUsername();
        $suggestions = [];

        if ($request->isMethod('POST')) {
            $newUsername = trim((string)$request->request->get('username'));
            
            // Validate username
            $validation = $this->usernameChangeService->validateUsername($newUsername, $user);
            
            if ($validation['valid']) {
                // Change username
                $success = $this->usernameChangeService->changeUsername($user, $newUsername);
                
                if ($success) {
                    $this->addFlash('success', 'Username changed successfully!');
                    return $this->redirectToRoute('app_username_change');
                } else {
                    $this->addFlash('error', 'Failed to change username. Please try again.');
                }
            } else {
                // Show errors and suggestions
                foreach ($validation['errors'] as $error) {
                    $this->addFlash('error', $error);
                }
                
                // Generate suggestions if username is taken
                if (in_array('This username is already taken', $validation['errors'])) {
                    $suggestions = $this->usernameChangeService->suggestAlternatives($newUsername);
                }
            }
        }

        $rules = $this->usernameChangeService->getValidationRules();

        return $this->render('settings/username_change.html.twig', [
            'currentUsername' => $currentUsername,
            'suggestions' => $suggestions,
            'rules' => $rules,
        ]);
    }

    #[Route('/check', name: 'app_username_check', methods: ['POST'])]
    public function checkAvailability(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $username = trim((string)$request->request->get('username', ''));

        if (empty($username)) {
            return $this->json([
                'available' => false,
                'message' => 'Username cannot be empty',
            ]);
        }

        // Only pass the user if it's actually an instance of your User class
        $validUser = $user instanceof User ? $user : null;

        // Validate username (pass null for user if not authenticated - for signup)
        $validation = $this->usernameChangeService->validateUsername($username, $validUser);

        if (!$validation['valid']) {
            return $this->json([
                'available' => false,
                'message' => implode(', ', $validation['errors']),
                'errors' => $validation['errors'],
            ]);
        }

        return $this->json([
            'available' => true,
            'message' => 'Username is available!',
        ]);
    }

    #[Route('/suggestions', name: 'app_username_suggestions', methods: ['POST'])]
    public function getSuggestions(Request $request): JsonResponse
    {
        $username = trim((string)$request->request->get('username', ''));

        if (empty($username)) {
            return $this->json([
                'suggestions' => [],
            ]);
        }

        $suggestions = $this->usernameChangeService->suggestAlternatives($username);

        return $this->json([
            'suggestions' => $suggestions,
        ]);
    }
}