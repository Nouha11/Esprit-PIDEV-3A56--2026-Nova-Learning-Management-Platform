<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\Course;
use App\Repository\StudySession\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;

class CourseService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CourseRepository $courseRepository
    ) {
    }

    /**
     * Create a new course
     */
    public function createCourse(Course $course): Course
    {
        if (!$course->getCreatedAt()) {
            $course->setCreatedAt(new \DateTimeImmutable());
        }
        
        $this->em->persist($course);
        $this->em->flush();
        
        return $course;
    }

    /**
     * Update an existing course
     */
    public function updateCourse(Course $course): Course
    {
        $this->em->flush();
        return $course;
    }

    /**
     * Delete a course
     * Throws exception if course has existing planning sessions
     */
    public function deleteCourse(Course $course): void
    {
        if ($course->getPlannings()->count() > 0) {
            throw new \RuntimeException('Cannot delete course with existing planning sessions');
        }
        
        $this->em->remove($course);
        $this->em->flush();
    }

    /**
     * Toggle course publication status
     */
    public function togglePublish(Course $course): Course
    {
        $course->setIsPublished(!$course->isPublished());
        $this->em->flush();
        
        return $course;
    }

    /**
     * Find courses by filters
     */
    public function findByFilters(?string $difficulty, ?string $category, ?bool $isPublished): array
    {
        return $this->courseRepository->findByFilters($difficulty, $category, $isPublished);
    }
}
