<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class BalanceController
{
    private $transactionRepository;

    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository();
    }

    public function get(): JsonResponse
    {
        return new JsonResponse([
            'balance' => $this->transactionRepository->getBalance(),
        ]);
    }
}