<?php

namespace App\Repository\Gamification;

use App\Entity\Gamification\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
    * Find all active games
    */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('g')
        ->where('g.isActive = :active')
        ->setParameter('active', true)
        ->orderBy('g.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }
    
    /**
    * Find games by type
    */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('g')
        ->where('g.type = :type')
        ->andWhere('g.isActive = :active')
        ->setParameter('type', $type)
        ->setParameter('active', true)
        ->getQuery()
        ->getResult();
    }
    /**
    * Find games by difficulty
    */
    public function findByDifficulty(string $difficulty): array
    {
        return $this->createQueryBuilder('g')
        ->where('g.difficulty = :difficulty')
        ->andWhere('g.isActive = :active')
        ->setParameter('difficulty', $difficulty)
        ->setParameter('active', true)
        ->getQuery()
        ->getResult();
    }
    /**
    * Find free games (tokenCost = 0)
    */
    public function findFreeGames(): array
    {
        return $this->createQueryBuilder('g')
        ->where('g.tokenCost = 0')
        ->andWhere('g.isActive = :active')
        ->setParameter('active', true)
        ->getQuery()
        ->getResult();
    }

}
