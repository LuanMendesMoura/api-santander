<?php

namespace App\Repository;

use App\Entity\Transacao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transacao>
 */
class TransacaoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transacao::class);
    }

    //    /**
    //     * @return Transacao[] Returns an array of Transacao objects
    //     */
    public function findByTransacao($usuarioDestino): ?Transacao
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t. = :usuarioDestino')
            ->setParameter('usuarioDestino', $usuarioDestino)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Transacao
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
