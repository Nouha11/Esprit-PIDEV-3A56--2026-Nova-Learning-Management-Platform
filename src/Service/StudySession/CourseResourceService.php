<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\Course;
use App\Entity\StudySession\Resource;
use Doctrine\ORM\EntityManagerInterface;

class CourseResourceService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $uploadDirectory
    ) {}

    /**
     * Get all PDF resources for a course
     */
    public function getCourseResources(Course $course): array
    {
        return $course->getResources()->toArray();
    }

    /**
     * Get download URL for resource
     */
    public function getResourceUrl(Resource $resource): string
    {
        return '/uploads/resources/' . $resource->getStoredFilename();
    }

    /**
     * Get full file path for resource
     */
    public function getResourcePath(Resource $resource): string
    {
        return $this->uploadDirectory . '/resources/' . $resource->getStoredFilename();
    }
}
