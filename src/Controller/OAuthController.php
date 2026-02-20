<?php

namespace App\Controller;

use App\Entity\users\User;
use App\Entity\users\StudentProfile;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OAuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry): Response
    {
        // Redirect to Google OAuth
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'email', 'profile'
            ], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(ClientRegistry $clientRegistry, Request $request): Response
    {
        try {
            $client = $clientRegistry->getClient('google');
            $accessToken = $client->getAccessToken();
            
            // Get user info from Google
            $googleUser = $client->fetchUserFromToken($accessToken);
            
            $email = $googleUser->getEmail();
            $firstName = $googleUser->getFirstName();
            $lastName = $googleUser->getLastName();
            
            // Check if user exists
            $user = $this->userRepository->findOneBy(['email' => $email]);
            
            $locale = $request->getSession()->get('_locale', 'en');
            
            if (!$user) {
                // Create new user
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($this->generateUsername($email));
                $user->setPassword(bin2hex(random_bytes(32))); // Random password
                $user->setRole('ROLE_STUDENT');
                $user->setIsActive(true);
                $user->setIsVerified(true); // Auto-verify OAuth users
                
                // Create student profile
                $studentProfile = new StudentProfile();
                $studentProfile->setFirstName($firstName ?? 'User');
                $studentProfile->setLastName($lastName ?? '');
                $studentProfile->setEmail($email);
                
                $user->setStudentProfile($studentProfile);
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $this->addFlash('success', $locale === 'fr' 
                    ? 'Compte créé avec succès via Google !' 
                    : 'Account created successfully via Google!');
            } else {
                $this->addFlash('success', $locale === 'fr' 
                    ? 'Connexion réussie via Google !' 
                    : 'Successfully logged in via Google!');
            }
            
            // Manually authenticate the user
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            
            // Fire the login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($event);
            
            // Save session
            $request->getSession()->set('_security_main', serialize($token));
            
            return $this->redirectToRoute('app_home');
            
        } catch (IdentityProviderException $e) {
            $this->addFlash('error', 'OAuth authentication failed: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred during authentication: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/connect/linkedin', name: 'connect_linkedin_start')]
    public function connectLinkedIn(ClientRegistry $clientRegistry): Response
    {
        // Redirect to LinkedIn OAuth
        return $clientRegistry
            ->getClient('linkedin')
            ->redirect([
                'openid', 'profile', 'email'
            ], []);
    }

    #[Route('/connect/linkedin/check', name: 'connect_linkedin_check')]
    public function connectLinkedInCheck(ClientRegistry $clientRegistry, Request $request): Response
    {
        try {
            $client = $clientRegistry->getClient('linkedin');
            $accessToken = $client->getAccessToken();
            
            // Get user info from LinkedIn
            $linkedinUser = $client->fetchUserFromToken($accessToken);
            
            $email = $linkedinUser->getEmail();
            $firstName = $linkedinUser->getFirstName();
            $lastName = $linkedinUser->getLastName();
            
            // Check if user exists
            $user = $this->userRepository->findOneBy(['email' => $email]);
            
            $locale = $request->getSession()->get('_locale', 'en');
            
            if (!$user) {
                // Create new user
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($this->generateUsername($email));
                $user->setPassword(bin2hex(random_bytes(32))); // Random password
                $user->setRole('ROLE_STUDENT');
                $user->setIsActive(true);
                $user->setIsVerified(true); // Auto-verify OAuth users
                
                // Create student profile
                $studentProfile = new StudentProfile();
                $studentProfile->setFirstName($firstName ?? 'User');
                $studentProfile->setLastName($lastName ?? '');
                $studentProfile->setEmail($email);
                
                $user->setStudentProfile($studentProfile);
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $this->addFlash('success', $locale === 'fr' 
                    ? 'Compte créé avec succès via LinkedIn !' 
                    : 'Account created successfully via LinkedIn!');
            } else {
                $this->addFlash('success', $locale === 'fr' 
                    ? 'Connexion réussie via LinkedIn !' 
                    : 'Successfully logged in via LinkedIn!');
            }
            
            // Manually authenticate the user
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            
            // Fire the login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($event);
            
            // Save session
            $request->getSession()->set('_security_main', serialize($token));
            
            return $this->redirectToRoute('app_home');
            
        } catch (IdentityProviderException $e) {
            $this->addFlash('error', 'OAuth authentication failed: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred during authentication: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }

    private function generateUsername(string $email): string
    {
        $baseUsername = explode('@', $email)[0];
        $username = $baseUsername;
        $counter = 1;
        
        // Ensure unique username
        while ($this->userRepository->findOneBy(['username' => $username])) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
}
