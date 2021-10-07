<?php

namespace App\Controller;

use App\Repository\TransactRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BalanceController extends AbstractController
{
    private $transactionRepository;

    public function __construct(TransactRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getBalance(): JsonResponse
    {
        return new JsonResponse([
            'balance' => $this->transactionRepository->getBalance(),
        ]);
    }
}