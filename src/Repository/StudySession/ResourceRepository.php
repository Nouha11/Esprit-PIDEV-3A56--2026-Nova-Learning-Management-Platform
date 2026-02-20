<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\Resource;
use App\Entity\StudySession\StudySession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resource>
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resource::class);
    }

    /**
     * Find resources by study session
     *
     * @param StudySession $studySession
     * @return Resource[]
     */
    public function findByStudySession(StudySession $studySession): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.studySession = :studySession')
            ->setParameter('studySession', $studySession)
            ->orderBy('r.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
