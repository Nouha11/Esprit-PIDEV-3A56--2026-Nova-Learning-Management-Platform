<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\EnrollmentRequest;
use App\Entity\StudySession\Course;
use App\Entity\users\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EnrollmentRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnrollmentRequest::class);
    }

    public function findPendingByStudentAndCourse(User $student, Course $course): ?EnrollmentRequest
    {
        return $this->createQueryBuilder('er')
            ->where('er.student = :student')
            ->andWhere('er.course = :course')
            ->andWhere('er.status = :status')
            ->setParameter('student', $student)
            ->setParameter('course', $course)
            ->setParameter('status', 'PENDING')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingByCourse(Course $course): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.course = :course')
            ->andWhere('er.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'PENDING')
            ->orderBy('er.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingByTutor(User $tutor): array
    {
        return $this->createQueryBuilder('er')
            ->join('er.course', 'c')
            ->where('c.createdBy = :tutor')
            ->andWhere('er.status = :status')
            ->setParameter('tutor', $tutor)
            ->setParameter('status', 'PENDING')
            ->orderBy('er.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
