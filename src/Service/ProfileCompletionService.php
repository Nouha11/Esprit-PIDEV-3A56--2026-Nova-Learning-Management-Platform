<?php

namespace App\Service;

use App\Entity\users\StudentProfile;
use App\Entity\users\TutorProfile;

class ProfileCompletionService
{
    /**
     * Calculate profile completion percentage for Student
     */
    public function calculateStudentCompletion(StudentProfile $student): array
    {
        $fields = [
            'firstName' => $student->getFirstName(),
            'lastName' => $student->getLastName(),
            'bio' => $student->getBio(),
            'university' => $student->getUniversity(),
            'major' => $student->getMajor(),
            'academicLevel' => $student->getAcademicLevel(),
            'profilePicture' => $student->getProfilePicture(),
            'interests' => $student->getInterests(),
        ];

        $completed = 0;
        $total = count($fields);
        $missing = [];

        foreach ($fields as $fieldName => $value) {
            if ($this->isFieldComplete($value)) {
                $completed++;
            } else {
                $missing[] = $this->getFieldLabel($fieldName);
            }
        }

        $percentage = ($completed / $total) * 100;

        return [
            'percentage' => round($percentage),
            'completed' => $completed,
            'total' => $total,
            'missing' => $missing,
            'isComplete' => $percentage === 100.0,
        ];
    }

    /**
     * Calculate profile completion percentage for Tutor
     */
    public function calculateTutorCompletion(TutorProfile $tutor): array
    {
        $fields = [
            'firstName' => $tutor->getFirstName(),
            'lastName' => $tutor->getLastName(),
            'bio' => $tutor->getBio(),
            'expertise' => $tutor->getExpertise(),
            'qualifications' => $tutor->getQualifications(),
            'yearsOfExperience' => $tutor->getYearsOfExperience(),
            'hourlyRate' => $tutor->getHourlyRate(),
            'profilePicture' => $tutor->getProfilePicture(),
        ];

        $completed = 0;
        $total = count($fields);
        $missing = [];

        foreach ($fields as $fieldName => $value) {
            if ($this->isFieldComplete($value)) {
                $completed++;
            } else {
                $missing[] = $this->getFieldLabel($fieldName);
            }
        }

        $percentage = ($completed / $total) * 100;

        return [
            'percentage' => round($percentage),
            'completed' => $completed,
            'total' => $total,
            'missing' => $missing,
            'isComplete' => $percentage === 100.0,
        ];
    }

    /**
     * Check if a field is considered complete
     */
    private function isFieldComplete($value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * Get human-readable field label
     */
    private function getFieldLabel(string $fieldName): string
    {
        $labels = [
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'bio' => 'Bio',
            'university' => 'University',
            'major' => 'Major',
            'academicLevel' => 'Academic Level',
            'profilePicture' => 'Profile Picture',
            'interests' => 'Interests',
            'expertise' => 'Expertise',
            'qualifications' => 'Qualifications',
            'yearsOfExperience' => 'Years of Experience',
            'hourlyRate' => 'Hourly Rate',
        ];

        return $labels[$fieldName] ?? $fieldName;
    }
}
