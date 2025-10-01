<?php

namespace App\Repository;

use App\Entity\Carrousel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CarrouselRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carrousel::class);
    }

    /** Position max (0 si aucun enregistrement). */
    public function getMaxPosition(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COALESCE(MAX(c.position), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Récupère les items actifs triés par position ASC. */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :on')->setParameter('on', true)
            ->orderBy('c.position', 'ASC')
            ->getQuery()->getResult();
    }
}
