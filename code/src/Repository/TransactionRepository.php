<?php

namespace App\Repository;

use App\Model\Transaction;
use DateTime;
use PDO;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class TransactionRepository
{
    private $pdo;
    private $cache;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=db;dbname=my_budget', 'root', 'root');
        $this->cache = RedisAdapter::createConnection(
            'redis://cache'
        );
    }

    public function getBalance(): float
    {
        $balance = $this->cache->get('balance');
        if (!empty($balance)) {
            return $balance;
        }

        $balance = (float)$this->pdo->query("SELECT SUM(amount) FROM transactions")->fetchColumn();

        $this->cache->set('balance', $balance);

        return $balance;
    }

    public function getAll(): array
    {
        $transactions = $this->cache->get('all_transactions');
        if (!empty($transactions)) {
            return $transactions;
        }

        $data = $this->pdo->query("SELECT * FROM transactions")->fetchAll(PDO::FETCH_ASSOC);

        if ($data == false) {
            return [];
        }

        $transactions = [];

        foreach ($data as $datum) {
            $transactions[] = new Transaction(
                $datum['transaction_id'],
                $datum['title'],
                $datum['amount'],
                new DateTime($datum['created_at'])
            );
        }

        $this->cache->set('all_transactions', $transactions);

        return $transactions;
    }

    public function insert(Transaction $transaction): Transaction
    {
        $this->pdo->exec("
            INSERT INTO transactions (`title`, `amount`)
            VALUES ('{$transaction->getTitle()}', {$transaction->getAmount()});
        ");

        $this->cache->del('all_transactions', 'balanse');

        return new Transaction(
            $this->pdo->lastInsertId(),
            $transaction->getTitle(),
            $transaction->getAmount(),
            DateTime::createFromFormat('Y-m-d H:i:s', $this->pdo->query("SELECT created_at FROM transactions")->fetchColumn())
        );
    }
}
