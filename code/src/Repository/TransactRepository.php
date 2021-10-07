<?php

namespace App\Repository;

use App\Entity\Transact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transact[]    findAll()
 * @method Transact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transact::class);
    }

    public function insert($parameters)
    {
        $transact = new Transact();
        $transact->setTitle($parameters->title);
        $transact->setAmount($parameters->amount);

        $this->getEntityManager()->persist($transact);
        $this->getEntityManager()->flush();
        
        return [
            'id'=>$transact->getId(),
            'title'=>$transact->getTitle(),
            'amount'=>$transact->getAmount(),
            'balance'=>$this->getBalance()
        ];
    }

    public function getBalance()
    {
         return $this->createQueryBuilder('t')
        ->select('SUM(t.amount) as balance')
        ->getQuery()
        ->getResult()[0]['balance'];
    }

    public function getAll()
    {
        return array_map(function (Transact $transaction) {
            return [
                'id' => $transaction->getId(),
                'title' => $transaction->getTitle(),
                'amount' => $transaction->getAmount(),
                'createdAt' => $transaction->getCreatedAt()->format(DATE_ATOM),
            ];
        }, $this->findAll());
    }

    // /**
    //  * @return Transact[] Returns an array of Transact objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transact
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
