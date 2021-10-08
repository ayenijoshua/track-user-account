<?php

namespace App\Controller;

use App\Model\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionController extends AbstractController
{
    public $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function insert(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        $transaction = new Transaction();
        $transaction->setTitle($parameters->title);
        $transaction->setAmount($parameters->amount);

        $errors = $validator->validate($transaction);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, 442);
        }

        $newTransaction = $this->transactionRepository->insert($transaction);

        return new JsonResponse($newTransaction);
    }

    public function all(): JsonResponse
    {
        return new JsonResponse($this->transactionRepository->getAll(),200);
    }
}
