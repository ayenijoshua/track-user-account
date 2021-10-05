<?php

namespace App\Controller;

use App\Model\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TransactionController
{
    private $transactionRepository;

    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository();
    }

    public function insert(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        $transaction = new Transaction(
            null,
            $parameters->title,
            $parameters->amount
        );

        $balance = $this->transactionRepository->getBalance() + $parameters->amount;

        $transaction = $this->transactionRepository->insert($transaction);

        return new JsonResponse([
            'id' => $transaction->getId(),
            'title' => $parameters->title,
            'amount' => $parameters->amount,
            'createdAt' => $transaction->createdAt()->format(DATE_ATOM),
            'balance' => $balance,
        ]);
    }

    public function all(): JsonResponse
    {
        return new JsonResponse(array_map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'title' => $transaction->getTitle(),
                'amount' => $transaction->getAmount(),
                'createdAt' => $transaction->createdAt()->format(DATE_ATOM),
            ];
        }, $this->transactionRepository->getAll()));
    }
}
