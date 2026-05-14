<?php

namespace App\Twig;

use Doctrine\DBAL\Connection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Twig extension that resolves profile picture URLs for the shared Java/Symfony database.
 *
 * Java saves to:    user.profile_picture  (full local path like C:\Users\nahno\nova_avatars\13_xxx.png)
 * Symfony saves to: student_profile.profile_picture  (plain filename like hey-699c53.png)
 *
 * Resolution order:
 *  1. student_profile.profile_picture (Symfony upload) — plain filename
 *  2. user.profile_picture (Java upload) — full local path, auto-copied to public/uploads/avatars/
 *  3. null → caller shows initials fallback
 */
class AvatarExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private Connection $connection
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('avatar_url', [$this, 'resolveAvatarUrl']),
        ];
    }

    /**
     * Accepts a StudentProfile, TutorProfile, or a raw string.
     * Returns a web-accessible URL string, or null if no picture available.
     */
    public function resolveAvatarUrl(mixed $profile): ?string
    {
        $raw = null;

        if (is_string($profile)) {
            $raw = $profile;
        } elseif (is_object($profile) && method_exists($profile, 'getProfilePicture')) {
            $raw = $profile->getProfilePicture();

            // If student/tutor profile has no picture, fall back to user.profile_picture (Java)
            if (empty($raw)) {
                $raw = $this->getUserProfilePicture($profile);
            }
        }

        if (empty($raw)) {
            return null;
        }

        return $this->resolveRaw($raw);
    }

    /**
     * Fetch user.profile_picture from DB using the profile's user relationship.
     */
    private function getUserProfilePicture(object $profile): ?string
    {
        try {
            // StudentProfile → user via student_profile_id
            // TutorProfile   → user via tutor_profile_id
            $profileId = $profile->getId();
            if (!$profileId) return null;

            $profileClass = get_class($profile);
            $column = str_contains($profileClass, 'Student') ? 'student_profile_id' : 'tutor_profile_id';

            $pic = $this->connection->fetchOne(
                "SELECT profile_picture FROM user WHERE {$column} = ? AND profile_picture IS NOT NULL AND profile_picture != ''",
                [$profileId]
            );

            return $pic ?: null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Resolves a raw string (path or filename) to a web URL.
     */
    private function resolveRaw(string $raw): ?string
    {
        // Already a web path or remote URL
        if (str_starts_with($raw, '/') || str_starts_with($raw, 'http')) {
            return $raw;
        }

        // Java local path (contains backslash or Windows drive letter like C:)
        if (str_contains($raw, '\\') || preg_match('/^[A-Za-z]:/', $raw)) {
            return $this->resolveJavaPath($raw);
        }

        // Plain Symfony filename
        $publicPath = $this->projectDir . '/public/uploads/avatars/' . $raw;
        if (file_exists($publicPath)) {
            return '/uploads/avatars/' . $raw;
        }

        return null;
    }

    /**
     * Handles Java's absolute local path.
     * Copies the file to public/uploads/avatars/ if it exists on this machine.
     */
    private function resolveJavaPath(string $absolutePath): ?string
    {
        $normalised = str_replace('\\', '/', $absolutePath);
        $filename   = basename($normalised);

        $destDir  = $this->projectDir . '/public/uploads/avatars/';
        $destPath = $destDir . $filename;

        // Already copied
        if (file_exists($destPath)) {
            return '/uploads/avatars/' . $filename;
        }

        // Try to copy from original location on this machine
        $sourcePath = str_replace('/', DIRECTORY_SEPARATOR, $normalised);
        if (file_exists($sourcePath)) {
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($sourcePath, $destPath);
            return '/uploads/avatars/' . $filename;
        }

        return null;
    }
}
