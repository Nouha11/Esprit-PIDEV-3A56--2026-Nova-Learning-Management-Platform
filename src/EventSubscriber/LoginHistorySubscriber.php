<?php

namespace App\EventSubscriber;

use App\Entity\users\User;
use App\Service\LoginHistoryService;
use App\Service\SessionManagementService;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoginHistoryService $loginHistoryService,
        private SessionManagementService $sessionManagementService,
        private NotificationService $notificationService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if ($user instanceof User) {
            // Check if 2FA was used
            $is2faUsed = $user->isTotpAuthenticationEnabled();
            
            // Log the login attempt
            $this->loginHistoryService->logLoginAttempt(
                $user,
                'success',
                null,
                $is2faUsed
            );

            // Create or update session
            $sessionResult = $this->sessionManagementService->createSession($user);
            $session = $sessionResult['session'];
            $isNewDevice = $sessionResult['is_new_device'];

            // Send notification if it's a new device
            if ($isNewDevice) {
                $this->notificationService->notifyNewDeviceLogin(
                    user: $user,
                    browser: $session->getBrowser() ?? 'Unknown Browser',
                    platform: $session->getPlatform() ?? 'Unknown Platform',
                    device: $session->getDevice() ?? 'Unknown Device',
                    location: $session->getLocation(),
                    ipAddress: $session->getIpAddress(),
                    loginTime: $session->getCreatedAt()
                );
            }
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $exception = $event->getException();
        $passport = $event->getPassport();
        
        // Try to get the user from the passport
        $user = null;
        if ($passport && method_exists($passport, 'getUser')) {
            $user = $passport->getUser();
        }

        // If we have a user, log the failed attempt
        if ($user instanceof User) {
            $failureReason = $exception->getMessage();
            
            $this->loginHistoryService->logLoginAttempt(
                $user,
                'failed',
                $failureReason
            );
        }
    }
}
