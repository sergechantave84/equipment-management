<?php

namespace App\Repository;

use App\Entity\Equipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    public function listEquipment(int $nbLinePerPage, int $page, ?string $name, ?string $category)
    {
        $qb = $this->createQueryBuilder('eq')
                   ->where('eq.deleted = :deleted')
                   ->setParameter('deleted', false)
        ;
        if ($name) {
            $qb->andWhere('eq.name LIKE :term1')->setParameter('term1', '%' . $name . '%');
        }
        if ($category) {
            $qb->andWhere('eq.category LIKE :term2')->setParameter('term2', '%' . $category . '%');
        }

        return $qb->setFirstResult(($page - 1) * $nbLinePerPage)
                  ->setMaxResults($nbLinePerPage)
                  ->getQuery()
                  ->getResult()
        ;
    }
}
