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

    /**
     * generic method for debit(-) and credit(+) transactions
     */
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

    /**
     * process credit transactions
     */
    public function credit(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $parameters = json_decode($request->getContent());
        $transaction_type = 'credit';

        $transaction = new Transaction();
        $transaction->setTitle($parameters->title);
        $transaction->setAmount($parameters->amount);
        $transaction->setTransactionType($transaction_type);

        $errors = $validator->validate($transaction);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, 442);
        }

        $newTransaction = $this->transactionRepository->makeTransaction($transaction);

        return new JsonResponse($newTransaction);
    }

    /**
     * process debit transactions
     */
    public function debit(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $parameters = json_decode($request->getContent());
        $transaction_type = 'debit';

        $transaction = new Transaction();
        $transaction->setTitle($parameters->title);
        $transaction->setAmount($parameters->amount);
        $transaction->setTransactionType($transaction_type);

        $errors = $validator->validate($transaction);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, 442);
        }

        if($parameters->amount > $this->transactionRepository->totalBalance()){
            return new JsonResponse("Account balance is too low", 400);
        }

        $newTransaction = $this->transactionRepository->makeTransaction($transaction);

        return new JsonResponse($newTransaction);
    }

    /**
     * get all debit bransactions
     */
    public function debitTransactions()
    {
        return new JsonResponse($this->transactionRepository->allDebits(),200);
    }

    /**
     * get all credit transactions
     */
    public function creditTransactions()
    {
        return new JsonResponse($this->transactionRepository->allCredits(),200);
    }

    public function all(): JsonResponse
    {
        return new JsonResponse($this->transactionRepository->getAll(),200);
    }
}
