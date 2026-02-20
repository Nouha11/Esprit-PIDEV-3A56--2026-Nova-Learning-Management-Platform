<?php

namespace App\MessageHandler\StudySession;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Custom message handler for sending emails asynchronously
 * Implements Requirements 23.1 (async email sending) and 23.5 (failure logging)
 * 
 * Note: Symfony has a built-in handler for SendEmailMessage, but this custom handler
 * provides additional logging and error handling specific to our application.
 */
#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Handle email sending asynchronously
     * Logs success and failures for monitoring
     *
     * @param SendEmailMessage $message
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function __invoke(SendEmailMessage $message): void
    {
        $email = $message->getMessage();
        
        try {
            $this->logger->info('Processing email message', [
                'subject' => $email->getSubject(),
                'to' => implode(', ', array_map(fn($addr) => $addr->getAddress(), $email->getTo())),
                'from' => implode(', ', array_map(fn($addr) => $addr->getAddress(), $email->getFrom()))
            ]);

            // Send the email
            $this->mailer->send($email);
            
            $this->logger->info('Email sent successfully', [
                'subject' => $email->getSubject(),
                'to' => implode(', ', array_map(fn($addr) => $addr->getAddress(), $email->getTo()))
            ]);
            
        } catch (\Exception $e) {
            // Log the error - the message will be retried by Messenger
            $this->logger->error('Failed to send email', [
                'subject' => $email->getSubject(),
                'to' => implode(', ', array_map(fn($addr) => $addr->getAddress(), $email->getTo())),
                'error' => $e->getMessage(),
                'error_class' => get_class($e)
            ]);
            
            // Re-throw the exception so Messenger can retry
            // After max retries (3), the MessengerFailureListener will log the final failure
            throw $e;
        }
    }
}
