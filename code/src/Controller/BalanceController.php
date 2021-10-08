<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BalanceController extends AbstractController
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getBalance(): JsonResponse
    {
        return new JsonResponse([
            'balance' => $this->transactionRepository->getBalance() ?? 0,
        ]);
    }

    /**
     * get total account balance (total credit - total debits)
     */
    public function totalBalance(): JsonResponse
    {
        return new JsonResponse([
            'balance' => $this->transactionRepository->totalBalance() ?? 0,
        ]);
    }
}