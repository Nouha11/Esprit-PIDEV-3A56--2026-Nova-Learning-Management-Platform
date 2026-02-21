<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Find tag by name (case-insensitive)
     *
     * @param string $name
     * @return Tag|null
     */
    public function findByName(string $name): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->where('LOWER(t.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get tags with usage counts
     *
     * @return array
     */
    public function getTagsWithUsageCounts(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id', 't.name', 'COUNT(s.id) as usage_count')
            ->leftJoin('t.studySessions', 's')
            ->groupBy('t.id')
            ->orderBy('usage_count', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
