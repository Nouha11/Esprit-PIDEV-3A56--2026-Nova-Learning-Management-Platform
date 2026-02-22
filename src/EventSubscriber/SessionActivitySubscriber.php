<?php

namespace App\EventSubscriber;

use App\Service\SessionManagementService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SessionManagementService $sessionManagementService,
        private TokenStorageInterface $tokenStorage
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        
        if (!$token || !$token->getUser()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if ($session->has('session_token')) {
            $sessionToken = $session->get('session_token');
            
            try {
                $this->sessionManagementService->updateSessionActivity($sessionToken);
            } catch (\Exception $e) {
                // Silently fail - don't break the request
            }
        }
    }
}
