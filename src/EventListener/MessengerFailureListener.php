<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;

/**
 * Listens to Messenger events to log email sending failures
 * Implements Requirements 23.1 (async email sending) and 23.5 (failure logging)
 */
class MessengerFailureListener implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
            WorkerMessageRetriedEvent::class => 'onMessageRetried',
        ];
    }

    /**
     * Log when a message is retried after failure
     * This helps track retry attempts before final failure
     *
     * @param WorkerMessageRetriedEvent $event
     * @return void
     */
    public function onMessageRetried(WorkerMessageRetriedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();
        $throwable = $event->getThrowable();

        // Only log email-related retries
        if ($message instanceof SendEmailMessage) {
            $this->logger->warning('Email message retry attempt', [
                'message_class' => get_class($message),
                'retry_count' => $envelope->last(\Symfony\Component\Messenger\Stamp\RedeliveryStamp::class)?->getRetryCount() ?? 0,
                'error' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString()
            ]);
        }
    }

    /**
     * Log when a message fails after all retries are exhausted
     * This implements the requirement to log failures after all retries (Requirement 23.5)
     *
     * @param WorkerMessageFailedEvent $event
     * @return void
     */
    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();
        $throwable = $event->getThrowable();

        // Check if this is the final failure (will be sent to failure transport)
        if ($event->willRetry()) {
            return; // Not the final failure, will be retried
        }

        // Log the final failure with full context
        if ($message instanceof SendEmailMessage) {
            $email = $message->getMessage();
            
            $this->logger->error('Email message failed after all retries exhausted', [
                'message_class' => get_class($message),
                'email_subject' => $email->getSubject(),
                'email_to' => implode(', ', array_map(fn($addr) => $addr->getAddress(), $email->getTo())),
                'email_from' => implode(', ', array_map(fn($addr) => $addr->getAddress(), $email->getFrom())),
                'retry_count' => $envelope->last(\Symfony\Component\Messenger\Stamp\RedeliveryStamp::class)?->getRetryCount() ?? 0,
                'error' => $throwable->getMessage(),
                'error_class' => get_class($throwable),
                'trace' => $throwable->getTraceAsString()
            ]);

            // TODO: In production, you might want to:
            // - Send an alert to administrators
            // - Store failed emails in a database for manual retry
            // - Trigger a notification to a monitoring service
        } else {
            // Log other message types that fail
            $this->logger->error('Message failed after all retries exhausted', [
                'message_class' => get_class($message),
                'retry_count' => $envelope->last(\Symfony\Component\Messenger\Stamp\RedeliveryStamp::class)?->getRetryCount() ?? 0,
                'error' => $throwable->getMessage(),
                'error_class' => get_class($throwable)
            ]);
        }
    }
}
