<?php

namespace App\Service;

use App\Entity\users\User;
use App\Entity\users\UserSession;
use App\Repository\UserSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionManagementService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserSessionRepository $sessionRepository,
        private RequestStack $requestStack
    ) {}

    /**
     * Create or update session on login
     * Returns array with session and isNewDevice flag
     */
    public function createSession(User $user): array
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            throw new \RuntimeException('No request available');
        }

        // Parse user agent
        $userAgent = $request->headers->get('User-Agent', '');
        $deviceInfo = $this->parseUserAgent($userAgent);

        // Check if session already exists for this device
        $existingSession = $this->findExistingSession($user, $userAgent, $request->getClientIp());

        if ($existingSession) {
            $existingSession->updateActivity();
            $existingSession->setIsActive(true);
            $this->entityManager->flush();
            return [
                'session' => $existingSession,
                'is_new_device' => false,
            ];
        }

        // Create new session
        $session = new UserSession();
        $session->setUser($user);
        $session->setIpAddress($request->getClientIp());
        $session->setUserAgent($userAgent);
        $session->setBrowser($deviceInfo['browser']);
        $session->setPlatform($deviceInfo['platform']);
        $session->setDevice($deviceInfo['device']);
        $session->setLocation($this->getLocationFromIp($request->getClientIp()));

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return [
            'session' => $session,
            'is_new_device' => true,
        ];
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity(string $sessionToken): void
    {
        $session = $this->sessionRepository->findByToken($sessionToken);
        
        if ($session) {
            $session->updateActivity();
            $this->entityManager->flush();
        }
    }

    /**
     * Get all active sessions for a user
     */
    public function getActiveSessions(User $user): array
    {
        $sessions = $this->sessionRepository->findActiveSessions($user);
        $currentSessionToken = $this->getCurrentSessionToken();

        // Mark current session
        foreach ($sessions as $session) {
            if ($session->getSessionToken() === $currentSessionToken) {
                $session->setIsCurrent(true);
            }
        }

        return $sessions;
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(int $sessionId, User $user): bool
    {
        $session = $this->entityManager->getRepository(UserSession::class)->find($sessionId);

        if (!$session || $session->getUser()->getId() !== $user->getId()) {
            return false;
        }

        // Don't allow terminating current session this way
        if ($session->isCurrent()) {
            return false;
        }

        $session->setIsActive(false);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Terminate all other sessions except current
     */
    public function terminateAllOtherSessions(User $user): int
    {
        $currentSessionToken = $this->getCurrentSessionToken();
        $sessions = $this->sessionRepository->findActiveSessions($user);
        $count = 0;

        foreach ($sessions as $session) {
            if ($session->getSessionToken() !== $currentSessionToken) {
                $session->setIsActive(false);
                $count++;
            }
        }

        $this->entityManager->flush();
        return $count;
    }

    /**
     * Get current session token from request
     */
    private function getCurrentSessionToken(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return null;
        }

        // Try to get from session or cookie
        $session = $request->getSession();
        
        if ($session->has('session_token')) {
            return $session->get('session_token');
        }

        // Generate new token if not exists
        $token = bin2hex(random_bytes(32));
        $session->set('session_token', $token);
        
        return $token;
    }

    /**
     * Find existing session for same device
     */
    private function findExistingSession(User $user, string $userAgent, ?string $ip): ?UserSession
    {
        $sessions = $this->sessionRepository->findActiveSessions($user);

        foreach ($sessions as $session) {
            if ($session->getUserAgent() === $userAgent && $session->getIpAddress() === $ip) {
                return $session;
            }
        }

        return null;
    }

    /**
     * Parse user agent string
     */
    private function parseUserAgent(string $userAgent): array
    {
        $browser = 'Unknown';
        $platform = 'Unknown';
        $device = 'Desktop';

        // Detect browser
        if (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            if (strpos($userAgent, 'Edg') !== false) {
                $browser = 'Edge ' . $matches[1];
            } else {
                $browser = 'Chrome ' . $matches[1];
            }
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            if (strpos($userAgent, 'Chrome') === false) {
                $browser = 'Safari ' . $matches[1];
            }
        } elseif (preg_match('/MSIE ([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'IE ' . $matches[1];
        }

        // Detect platform
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $platform = 'Windows ' . $this->getWindowsVersion($matches[1]);
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            $platform = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $platform = 'Android';
            $device = 'Mobile';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $platform = 'iOS';
            $device = strpos($userAgent, 'iPad') !== false ? 'Tablet' : 'Mobile';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device,
        ];
    }

    /**
     * Get Windows version name
     */
    private function getWindowsVersion(string $version): string
    {
        $versions = [
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
        ];

        return $versions[$version] ?? $version;
    }

    /**
     * Get approximate location from IP (placeholder)
     */
    private function getLocationFromIp(?string $ip): ?string
    {
        if (!$ip || $ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }

        // In production, you would use a GeoIP service here
        // For now, return null
        return null;
    }

    /**
     * Cleanup old sessions
     */
    public function cleanupOldSessions(): int
    {
        return $this->sessionRepository->deactivateOldSessions();
    }
}
