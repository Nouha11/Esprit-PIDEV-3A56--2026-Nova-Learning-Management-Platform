<?php
// src/Repository/StudySession/CourseRepository.php

namespace App\Repository\StudySession;

use App\Entity\StudySession\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * Find courses by difficulty, category, and/or publication status filters
     *
     * @param string|null $difficulty
     * @param string|null $category
     * @param bool|null $isPublished
     * @return Course[]
     */
    public function findByFilters(?string $difficulty = null, ?string $category = null, ?bool $isPublished = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($difficulty) {
            $qb->andWhere('c.difficulty = :difficulty')
               ->setParameter('difficulty', $difficulty);
        }

        if ($category) {
            $qb->andWhere('c.category = :category')
               ->setParameter('category', $category);
        }

        if ($isPublished !== null) {
            $qb->andWhere('c.isPublished = :isPublished')
               ->setParameter('isPublished', $isPublished);
        }

        // Order by creation date, newest first
        $qb->orderBy('c.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all courses ordered by creation date
     *
     * @return Course[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find courses by category
     *
     * @param string $category
     * @return Course[]
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.category = :category')
            ->setParameter('category', $category)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find courses by difficulty level
     *
     * @param string $difficulty
     * @return Course[]
     */
    public function findByDifficulty(string $difficulty): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.difficulty = :difficulty')
            ->setParameter('difficulty', $difficulty)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search courses by title or description
     *
     * @param string $searchTerm
     * @return Course[]
     */
    public function search(string $searchTerm): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.title LIKE :search')
            ->orWhere('c.description LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}