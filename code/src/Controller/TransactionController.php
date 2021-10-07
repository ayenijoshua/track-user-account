<?php

namespace App\Controller;

use App\Model\Transaction;
use App\Repository\TransactRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionController extends AbstractController
{
    public $transactionRepository;

    public function __construct(TransactRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function insert(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        //var_dump($parameters['title']);

        $transaction = $this->transactionRepository->insert($parameters);
        //var_dump($transaction);

        // $transaction = new Transaction(
        //     null,
        //     $parameters->title,
        //     $parameters->amount
        // );

        // $balance = $this->transactionRepository->getBalance() + $parameters->amount;

        // //$transaction = $this->transactionRepository->insert($transaction);

        // return new JsonResponse([
        //     'id' => $transaction->getId(),
        //     'title' => $parameters->title,
        //     'amount' => $parameters->amount,
        //     'createdAt' => $transaction->createdAt()->format(DATE_ATOM),
        //     'balance' => $balance,
        // ]);

        return new JsonResponse($transaction);
    }

    public function all(): JsonResponse
    {
        return new JsonResponse($this->transactionRepository->getAll(),200);
    }
}
