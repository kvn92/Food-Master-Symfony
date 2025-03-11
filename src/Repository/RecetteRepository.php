<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    public function findMostLikedRecette(): ?Recette
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.likeRecettes', 'l') // Jointure avec les likes
            ->where('r.isActive = :active') // Filtrer les recettes actives
            ->setParameter('active', true)
            ->groupBy('r.id') // Grouper par recette
            ->orderBy('COUNT(l.id)', 'DESC') // Trier par nombre de likes décroissant
            ->setMaxResults(1) // Ne récupérer qu'une seule recette
            ->getQuery()
            ->getOneOrNullResult();
    }
    //    /**
    //     * @return Recette[] Returns an array of Recette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recette
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
