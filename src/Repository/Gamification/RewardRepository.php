<?php

namespace App\Repository\Gamification;

use App\Entity\Gamification\Reward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reward>
 */
class RewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reward::class);
    }

    /**
    * Find all active rewards
    */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('r')
        ->where('r.isActive = :active')
        ->setParameter('active', true)
        ->orderBy('r.type', 'ASC')
        ->getQuery()
        ->getResult();
    }
    
    /**
    * Find rewards by type
    */
    public function findByType(string $type): array
    {
    return $this->createQueryBuilder('r')
        ->where('r.type = :type')
        ->andWhere('r.isActive = :active')
        ->setParameter('type', $type)
        ->setParameter('active', true)
        ->getQuery()
        ->getResult();
    }

}
