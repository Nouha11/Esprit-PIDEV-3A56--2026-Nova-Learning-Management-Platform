<?php

namespace App\Service\StudySession;

use App\Entity\users\User;
use App\Entity\StudySession\Course;
use App\Entity\StudySession\EnrollmentRequest;
use App\Repository\StudySession\EnrollmentRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;

class EnrollmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EnrollmentRequestRepository $enrollmentRequestRepository
    ) {}

    /**
     * Check if student is enrolled in course
     */
    public function isEnrolled(User $user, Course $course): bool
    {
        return $user->getCourses()->contains($course);
    }

    /**
     * Get enrolled courses for user
     */
    public function getEnrolledCourses(User $user): Collection
    {
        return $user->getCourses();
    }

    /**
     * Request enrollment in a course
     */
    public function requestEnrollment(User $student, Course $course, ?string $message = null): EnrollmentRequest
    {
        // Check if already enrolled
        if ($this->isEnrolled($student, $course)) {
            throw new \RuntimeException('Student is already enrolled in this course');
        }

        // Check if there's already a pending request
        $existingRequest = $this->enrollmentRequestRepository->findPendingByStudentAndCourse($student, $course);
        if ($existingRequest) {
            throw new \RuntimeException('An enrollment request is already pending for this course');
        }

        $request = new EnrollmentRequest();
        $request->setStudent($student);
        $request->setCourse($course);
        $request->setMessage($message);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        return $request;
    }

    /**
     * Approve enrollment request
     */
    public function approveEnrollment(EnrollmentRequest $request, User $approver): void
    {
        if ($request->getStatus() !== 'PENDING') {
            throw new \RuntimeException('Only pending requests can be approved');
        }

        $student = $request->getStudent();
        $course = $request->getCourse();

        // Add student to course
        $student->addCourse($course);

        // Update request status
        $request->setStatus('APPROVED');
        $request->setRespondedAt(new \DateTimeImmutable());
        $request->setRespondedBy($approver);

        $this->entityManager->flush();
    }

    /**
     * Reject enrollment request
     */
    public function rejectEnrollment(EnrollmentRequest $request, User $rejector): void
    {
        if ($request->getStatus() !== 'PENDING') {
            throw new \RuntimeException('Only pending requests can be rejected');
        }

        $request->setStatus('REJECTED');
        $request->setRespondedAt(new \DateTimeImmutable());
        $request->setRespondedBy($rejector);

        $this->entityManager->flush();
    }

    /**
     * Get pending enrollment request for student and course
     */
    public function getPendingRequest(User $student, Course $course): ?EnrollmentRequest
    {
        return $this->enrollmentRequestRepository->findPendingByStudentAndCourse($student, $course);
    }

    /**
     * Get all pending requests for a course
     */
    public function getPendingRequestsForCourse(Course $course): array
    {
        return $this->enrollmentRequestRepository->findPendingByCourse($course);
    }

    /**
     * Get all pending requests for courses created by a tutor
     */
    public function getPendingRequestsForTutor(User $tutor): array
    {
        return $this->enrollmentRequestRepository->findPendingByTutor($tutor);
    }
}

