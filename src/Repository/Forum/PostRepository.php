<?php

namespace App\Repository\Forum;

use App\Entity\Forum\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Search for posts by Title, Content, Author Name, or Author Email
     * Returns a Query object for the Paginator
     */
    public function adminSearch(string $query): Query
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a') 
            ->andWhere('p.title LIKE :val OR p.content LIKE :val OR a.email LIKE :val OR a.username LIKE :val')
            ->setParameter('val', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * Returns a Query object based on the filter (Popular or Unanswered)
     */
    public function findByFilter(?string $filter): Query
    {
        $qb = $this->createQueryBuilder('p');

        if ($filter === 'popular') {
            // Sort by Upvotes (Highest first)
            $qb->orderBy('p.upvotes', 'DESC');
        } 
        elseif ($filter === 'unanswered') {
            // Find posts with ZERO comments (SIZE counts the collection)
            $qb->andWhere('SIZE(p.comments) = 0')
               ->orderBy('p.createdAt', 'DESC');
        } 
        else {
            // Default: Newest first
            $qb->orderBy('p.createdAt', 'DESC');
        }

        return $qb->getQuery();
    }
}