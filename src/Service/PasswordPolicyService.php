<?php

namespace App\Service;

class PasswordPolicyService
{
    // Modifié : Utilisation de propriétés typées au lieu de constantes
    // Cela résout l'erreur de logique booléenne stricte de PHPStan et rend le service configurable
    private int $minLength = 8;
    private int $maxLength = 128;
    private bool $requireUppercase = true;
    private bool $requireLowercase = true;
    private bool $requireNumbers = true;
    private bool $requireSpecialChars = true;
    private int $minStrengthScore = 3; // 0-5 scale

    /**
     * Validate password against policy
     */
    public function validatePassword(string $password): array
    {
        $errors = [];
        $checks = $this->checkPassword($password);

        if (!$checks['length']) {
            $errors[] = sprintf('Password must be between %d and %d characters', $this->minLength, $this->maxLength);
        }

        if ($this->requireUppercase && !$checks['uppercase']) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if ($this->requireLowercase && !$checks['lowercase']) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if ($this->requireNumbers && !$checks['numbers']) {
            $errors[] = 'Password must contain at least one number';
        }

        if ($this->requireSpecialChars && !$checks['special']) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)';
        }

        if ($checks['strength']['score'] < $this->minStrengthScore) {
            $errors[] = sprintf('Password is too weak. Minimum strength required: %s', $this->getStrengthLabel($this->minStrengthScore));
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'checks' => $checks,
        ];
    }

    /**
     * Check password against all criteria
     */
    public function checkPassword(string $password): array
    {
        $length = strlen($password);
        
        return [
            'length' => $length >= $this->minLength && $length <= $this->maxLength,
            'uppercase' => preg_match('/[A-Z]/', $password) === 1,
            'lowercase' => preg_match('/[a-z]/', $password) === 1,
            'numbers' => preg_match('/[0-9]/', $password) === 1,
            'special' => preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password) === 1,
            'strength' => $this->calculateStrength($password),
        ];
    }

    /**
     * Calculate password strength (0-5 scale)
     */
    public function calculateStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        $length = strlen($password);

        // Length scoring
        if ($length >= 8) $score++;
        if ($length >= 12) $score++;
        if ($length >= 16) $score++;

        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Add lowercase letters';
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Add uppercase letters';
        }

        if (preg_match('/[0-9]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Add numbers';
        }

        if (preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Add special characters';
        }

        // Penalize common patterns
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score = max(0, $score - 1);
            $feedback[] = 'Avoid repeated characters';
        }

        if (preg_match('/^[0-9]+$/', $password)) {
            $score = max(0, $score - 2);
            $feedback[] = 'Don\'t use only numbers';
        }

        if (preg_match('/^[a-zA-Z]+$/', $password)) {
            $score = max(0, $score - 1);
            $feedback[] = 'Mix letters with numbers and symbols';
        }

        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $score = max(0, $score - 2);
            $feedback[] = 'This is a commonly used password';
        }

        // Normalize score to 0-5
        $score = min(5, max(0, $score));

        return [
            'score' => $score,
            'percentage' => ($score / 5) * 100,
            'label' => $this->getStrengthLabel($score),
            'color' => $this->getStrengthColor($score),
            'feedback' => $feedback,
        ];
    }

    /**
     * Get strength label
     */
    private function getStrengthLabel(int $score): string
    {
        return match($score) {
            0, 1 => 'Very Weak',
            2 => 'Weak',
            3 => 'Fair',
            4 => 'Strong',
            5 => 'Very Strong',
            default => 'Unknown',
        };
    }

    /**
     * Get strength color
     */
    private function getStrengthColor(int $score): string
    {
        return match($score) {
            0, 1 => 'danger',
            2 => 'warning',
            3 => 'info',
            4 => 'primary',
            5 => 'success',
            default => 'secondary',
        };
    }

    /**
     * Check if password is in common passwords list
     */
    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', 'password123', '123456', '12345678', 'qwerty', 'abc123',
            'monkey', '1234567', 'letmein', 'trustno1', 'dragon', 'baseball',
            'iloveyou', 'master', 'sunshine', 'ashley', 'bailey', 'passw0rd',
            'shadow', '123123', '654321', 'superman', 'qazwsx', 'michael',
            'football', 'welcome', 'jesus', 'ninja', 'mustang', 'password1',
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Get password policy requirements
     */
    public function getPolicyRequirements(): array
    {
        return [
            'minLength' => $this->minLength,
            'maxLength' => $this->maxLength,
            'requireUppercase' => $this->requireUppercase,
            'requireLowercase' => $this->requireLowercase,
            'requireNumbers' => $this->requireNumbers,
            'requireSpecialChars' => $this->requireSpecialChars,
            'minStrengthScore' => $this->minStrengthScore,
        ];
    }

    /**
     * Generate password suggestion
     */
    public function generateStrongPassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $all = $uppercase . $lowercase . $numbers . $special;

        $password = '';
        
        // Ensure at least one of each required type
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }
}