<?php

namespace App\Repository;

use App\Model\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    private $cache;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
        $this->cache =  RedisAdapter::createConnection(
            'redis://cache'
        );
    }

    /**
     * make a debit or credit transaction
     */
    public function makeTransaction($transaction)
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();

        $this->cache->del('all_transactions','total_balance','total_credit','total_debit','all_credits','all_debits');

        $this->getEntityManager()->refresh($transaction);

        return [
            'id'=>$transaction->getId(),
            'title'=>$transaction->getTitle(),
            'amount'=>$transaction->getAmount(),
            'type'=>$transaction->getTransactionType(),
            'balance'=> round($this->totalBalance(),2),
            'createdAt' => $this->findOneBy(['transaction_id'=>$transaction->getId()])->getCreatedAt()->format(DATE_ATOM)
        ];
    }

    /**
     * get total credit transactions
     */
    public function totalCredit()
    {
        $credit = $this->cache->get('total_credit');

        if (!empty($credit)) {
            return $credit;
        }

        $credit = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as credit')
            ->where('t.transaction_type = :type')
            ->setParameter('type','credit')
            ->getQuery()
            ->getResult()[0]['credit'];

        $this->cache->set('total_credit', $credit); 

        return $credit;
    }

    /**
     * get total debit transactions
     */
    public function totalDebit()
    {
        $debit = $this->cache->get('total_debit');

        if (!empty($debit)) {
            return $debit;
        }

        $debit = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as debit')
            ->where('t.transaction_type = :type')
            ->setParameter('type','debit')
            ->getQuery()
            ->getResult()[0]['debit'];

        $this->cache->set('total_debit', $debit);

        return $debit;
    }

    
    /**
     * get total balance
     */
    public function totalBalance()
    {
        $balance = $this->cache->get('total_balance');

        if (!empty($balance)) {
            return $balance;
        }

        $balance = $this->totalCredit() - $this->totalDebit();

        $this->cache->set('total_balance', $balance);

        return $balance;
    }

    /**
     * all credit transactions
     */
    public function allCredits()
    {
        $credits = $this->cache->get('all_credits');

        if (!empty($credits)) {
            return json_decode($credits);
        }

       $credits = array_filter($this->getAll(), function ($val){
             return $val['type'] == 'credit';
        });
        
        $this->cache->set('all_credits', json_encode($credits));

        return $credits;
    }

    /**
     * all debit transactions
     */
    public function allDebits()
    {
        $debits = $this->cache->get('all_debits');

        if (!empty($debits)) {
            return json_decode($debits);
        }

        $debits = array_filter($this->getAll(), function ($val){
            return $val['type'] == 'debit';
        });
        
        $this->cache->set('all_debits', json_encode($debits));

        return $debits;
    }

    /**
     * generic transaction method
     */
    public function insert($transaction)
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();

        $this->cache->del('all_transactions', 'balance');

        $this->getEntityManager()->refresh($transaction);

        return [
            'id'=>$transaction->getId(),
            'title'=>$transaction->getTitle(),
            'amount'=>$transaction->getAmount(),
            'balance'=> round($this->getBalance(),2),
            'createdAt' => $this->findOneBy(['transaction_id'=>$transaction->getId()])->getCreatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * get generic balance
     * --mostly inacurate balance
     */
    public function getBalance()
    {
        $balance = $this->cache->get('balance');

        if (!empty($balance)) {
            return $balance;
        }

        $balance = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as balance')
            ->getQuery()
            ->getResult()[0]['balance'];

        $this->cache->set('balance', $balance);

        return $balance;
    }

    /**
     * all transactions
     */
    public function getAll()
    {
        $transactions = $this->cache->get('all_transactions');
        if (!empty($transactions)) {
            return json_decode($transactions);
        }

        $transactions = array_map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'title' => $transaction->getTitle(),
                'amount' => $transaction->getAmount(),
                'type' => $transaction->getTransactionType(),
                'createdAt' => $transaction->getCreatedAt()->format(DATE_ATOM),
            ];
        }, $this->findAll());

        $this->cache->set('all_transactions', json_encode($transactions));

        return $transactions;
    }
}
