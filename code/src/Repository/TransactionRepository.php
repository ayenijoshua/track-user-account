<?php

namespace App\Repository;

use App\Model\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transact[]    findAll()
 * @method Transact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * make a debit or credit transaction
     */
    public function makeTransaction($transaction)
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();

        return [
            'id'=>$transaction->getId(),
            'title'=>$transaction->getTitle(),
            'amount'=>$transaction->getAmount(),
            'type'=>$transaction->getTransactionType(),
            'balance'=> round($this->totalBalance(),2) 
        ];
    }

    /**
     * get total credit transactions
     */
    public function totalCredit()
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as credit')
            ->where('t.transaction_type = :type')
            ->setParameter('type','credit')
            ->getQuery()
            ->getResult()[0]['credit'];
    }

    /**
     * get total debit transactions
     */
    public function totalDebit()
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as debit')
            ->where('t.transaction_type = :type')
            ->setParameter('type','debit')
            ->getQuery()
            ->getResult()[0]['debit'];
    }

    
    /**
     * get total balance
     */
    public function totalBalance()
    {
        return $this->totalCredit() - $this->totalDebit();
    }

    /**
     * all credit transactions
     */
    public function allCredits()
    {
       return array_filter($this->getAll(), function ($val){
             return $val['type'] == 'credit';
        });    
    }

    /**
     * all debit transactions
     */
    public function allDebits()
    {
       return array_filter($this->getAll(), function ($val){
             return $val['type'] == 'debit';
        });    
    }

    /**
     * generic transaction method
     */
    public function insert($transaction)
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();

        return [
            'id'=>$transaction->getId(),
            'title'=>$transaction->getTitle(),
            'amount'=>$transaction->getAmount(),
            'balance'=> round($this->getBalance(),2) 
        ];
    }

    /**
     * get generic balance
     * --mostly in-acurate balance
     */
    public function getBalance()
    {
         return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as balance')
            ->getQuery()
            ->getResult()[0]['balance'];
    }

    /**
     * all transactions
     */
    public function getAll()
    {
        return array_map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'title' => $transaction->getTitle(),
                'amount' => $transaction->getAmount(),
                'type' => $transaction->getTransactionType(),
                'createdAt' => $transaction->getCreatedAt()->format(DATE_ATOM),
            ];
        }, $this->findAll());
    }
}
