<?php

namespace App\Service;

use App\Entity\users\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UsernameChangeService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Validate new username
     */
    public function validateUsername(string $username, ?User $currentUser = null): array
    {
        $errors = [];

        // Check length
        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }

        if (strlen($username) > 100) {
            $errors[] = 'Username cannot be longer than 100 characters';
        }

        // Check format (alphanumeric and underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }

        // Check if username is already taken (excluding current user if provided)
        $existingUser = $this->userRepository->findOneBy(['username' => $username]);
        if ($existingUser && ($currentUser === null || $existingUser->getId() !== $currentUser->getId())) {
            $errors[] = 'This username is already taken';
        }

        // Check for reserved usernames
        $reservedUsernames = [
            'admin', 'administrator', 'root', 'system', 'moderator', 'mod',
            'support', 'help', 'staff', 'official', 'nova', 'test', 'demo',
            'guest', 'user', 'null', 'undefined', 'anonymous'
        ];

        if (in_array(strtolower($username), $reservedUsernames)) {
            $errors[] = 'This username is reserved and cannot be used';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Change username
     */
    public function changeUsername(User $user, string $newUsername): bool
    {
        $validation = $this->validateUsername($newUsername, $user);

        if (!$validation['valid']) {
            return false;
        }

        $oldUsername = $user->getUsername();
        $user->setUsername($newUsername);
        
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get username change history count
     */
    public function getChangeCount(User $user): int
    {
        // This could be tracked in a separate table if needed
        // For now, we'll return 0 as a placeholder
        return 0;
    }

    /**
     * Check if username is available
     */
    public function isUsernameAvailable(string $username, ?User $excludeUser = null): bool
    {
        $existingUser = $this->userRepository->findOneBy(['username' => $username]);
        
        if (!$existingUser) {
            return true;
        }

        if ($excludeUser && $existingUser->getId() === $excludeUser->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Suggest alternative usernames
     */
    public function suggestAlternatives(string $username): array
    {
        $suggestions = [];
        $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $username);

        // Try with numbers
        for ($i = 1; $i <= 5; $i++) {
            $suggestion = $baseUsername . $i;
            if ($this->isUsernameAvailable($suggestion)) {
                $suggestions[] = $suggestion;
            }
        }

        // Try with underscores and numbers
        for ($i = 1; $i <= 3; $i++) {
            $suggestion = $baseUsername . '_' . $i;
            if ($this->isUsernameAvailable($suggestion)) {
                $suggestions[] = $suggestion;
            }
        }

        // Try with random numbers
        for ($i = 0; $i < 3; $i++) {
            $suggestion = $baseUsername . rand(10, 99);
            if ($this->isUsernameAvailable($suggestion)) {
                $suggestions[] = $suggestion;
            }
        }

        return array_unique(array_slice($suggestions, 0, 5));
    }

    /**
     * Get username validation rules
     */
    public function getValidationRules(): array
    {
        return [
            'minLength' => 3,
            'maxLength' => 100,
            'pattern' => '^[a-zA-Z0-9_]+$',
            'patternDescription' => 'Only letters, numbers, and underscores allowed',
        ];
    }
}
