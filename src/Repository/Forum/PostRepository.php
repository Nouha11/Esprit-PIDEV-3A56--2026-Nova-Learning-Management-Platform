<?php

namespace App\Repository\Forum;

use App\Entity\Forum\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    

    /**
     * Search for posts by Title, Content, Author Name, or Author Email
     */
    
    public function adminSearch(string $query)
    {
        return $this->createQueryBuilder('p')
            // 1. Join the User table (p.author) and give it the alias 'a'
            ->leftJoin('p.author', 'a') 
            
            // 2. Search everywhere: Post Title OR Post Content OR User Email OR User Name
            ->andWhere('p.title LIKE :val OR p.content LIKE :val OR a.email LIKE :val OR a.username LIKE :val')
            
            // 3. Bind the value
            ->setParameter('val', '%' . $query . '%')
            
            // 4. Sort newest first
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


}
