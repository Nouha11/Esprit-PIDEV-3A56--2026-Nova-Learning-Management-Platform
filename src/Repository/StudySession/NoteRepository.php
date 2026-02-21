<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\Note;
use App\Entity\StudySession\StudySession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Find notes by study session (ordered by createdAt DESC)
     *
     * @param StudySession $studySession
     * @return Note[]
     */
    public function findByStudySession(StudySession $studySession): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.studySession = :studySession')
            ->setParameter('studySession', $studySession)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search notes by keyword
     *
     * @param string $keyword
     * @return Note[]
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.content LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
