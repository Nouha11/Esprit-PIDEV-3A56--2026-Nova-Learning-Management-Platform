<?php

namespace App\Service;

use App\Entity\users\LoginHistory;
use App\Entity\users\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginHistoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    /**
     * Log a login attempt
     */
    public function logLoginAttempt(
        User $user,
        string $status,
        ?string $failureReason = null,
        bool $is2faUsed = false
    ): LoginHistory {
        $request = $this->requestStack->getCurrentRequest();
        
        $loginHistory = new LoginHistory();
        $loginHistory->setUser($user);
        $loginHistory->setStatus($status);
        $loginHistory->setFailureReason($failureReason);
        $loginHistory->setIs2faUsed($is2faUsed);

        if ($request) {
            // Get IP address
            $ipAddress = $request->getClientIp();
            $loginHistory->setIpAddress($ipAddress);

            // Get user agent
            $userAgent = $request->headers->get('User-Agent');
            $loginHistory->setUserAgent($userAgent);

            // Parse user agent for browser, platform, device
            $this->parseUserAgent($userAgent, $loginHistory);

            // Get location (basic implementation - can be enhanced with GeoIP)
            $location = $this->getLocationFromIp($ipAddress);
            $loginHistory->setLocation($location);
        }

        $this->entityManager->persist($loginHistory);
        $this->entityManager->flush();

        return $loginHistory;
    }

    /**
     * Parse user agent string
     */
    private function parseUserAgent(?string $userAgent, LoginHistory $loginHistory): void
    {
        if (!$userAgent) {
            return;
        }

        // Detect browser
        $browser = 'Unknown';
        if (preg_match('/Edge\/([0-9.]+)/', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Edg\/([0-9.]+)/', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent) && !preg_match('/Edg/', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera\/([0-9.]+)/', $userAgent) || preg_match('/OPR\/([0-9.]+)/', $userAgent)) {
            $browser = 'Opera';
        }
        $loginHistory->setBrowser($browser);

        // Detect platform
        $platform = 'Unknown';
        if (preg_match('/Windows NT 10/', $userAgent)) {
            $platform = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6.3/', $userAgent)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.2/', $userAgent)) {
            $platform = 'Windows 8';
        } elseif (preg_match('/Windows NT 6.1/', $userAgent)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/Mac OS X/', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iOS/', $userAgent) || preg_match('/iPhone|iPad|iPod/', $userAgent)) {
            $platform = 'iOS';
        }
        $loginHistory->setPlatform($platform);

        // Detect device
        $device = 'Desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                $device = 'Tablet';
            } else {
                $device = 'Mobile';
            }
        }
        $loginHistory->setDevice($device);
    }

    /**
     * Get location from IP (basic implementation)
     * For production, consider using a GeoIP service like MaxMind or ip-api.com
     */
    private function getLocationFromIp(?string $ipAddress): ?string
    {
        if (!$ipAddress || $ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return 'Localhost';
        }

        // Basic implementation - returns null
        // In production, integrate with a GeoIP service
        return null;
    }

    /**
     * Get recent login history for a user
     */
    public function getRecentLogins(User $user, int $limit = 10): array
    {
        return $this->entityManager
            ->getRepository(LoginHistory::class)
            ->createQueryBuilder('lh')
            ->where('lh.user = :user')
            ->setParameter('user', $user)
            ->orderBy('lh.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get login statistics for a user
     */
    public function getLoginStatistics(User $user, int $days = 30): array
    {
        $startDate = new \DateTime("-{$days} days");

        $qb = $this->entityManager
            ->getRepository(LoginHistory::class)
            ->createQueryBuilder('lh');

        $total = $qb->select('COUNT(lh.id)')
            ->where('lh.user = :user')
            ->andWhere('lh.createdAt >= :startDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getSingleScalarResult();

        $successful = $qb->select('COUNT(lh.id)')
            ->where('lh.user = :user')
            ->andWhere('lh.createdAt >= :startDate')
            ->andWhere('lh.status = :status')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('status', 'success')
            ->getQuery()
            ->getSingleScalarResult();

        $failed = $qb->select('COUNT(lh.id)')
            ->where('lh.user = :user')
            ->andWhere('lh.createdAt >= :startDate')
            ->andWhere('lh.status = :status')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('status', 'failed')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'successRate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get all login history for admin view
     */
    public function getAllLoginHistory(int $limit = 50, int $offset = 0): array
    {
        return $this->entityManager
            ->getRepository(LoginHistory::class)
            ->createQueryBuilder('lh')
            ->orderBy('lh.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Detect suspicious login activity
     */
    public function detectSuspiciousActivity(User $user): array
    {
        $suspicious = [];
        $recentLogins = $this->getRecentLogins($user, 20);

        // Check for multiple failed attempts
        $failedCount = 0;
        foreach ($recentLogins as $login) {
            if ($login->getStatus() === 'failed') {
                $failedCount++;
            }
        }

        if ($failedCount >= 3) {
            $suspicious[] = "Multiple failed login attempts detected ({$failedCount} in recent history)";
        }

        // Check for logins from different locations
        $locations = [];
        foreach ($recentLogins as $login) {
            if ($login->getLocation()) {
                $locations[] = $login->getLocation();
            }
        }
        $uniqueLocations = array_unique($locations);
        if (count($uniqueLocations) > 3) {
            $suspicious[] = 'Logins from multiple locations detected';
        }

        // Check for logins from different devices
        $devices = [];
        foreach ($recentLogins as $login) {
            if ($login->getDevice()) {
                $devices[] = $login->getDevice();
            }
        }
        $uniqueDevices = array_unique($devices);
        if (count($uniqueDevices) > 3) {
            $suspicious[] = 'Logins from multiple devices detected';
        }

        return $suspicious;
    }
}
