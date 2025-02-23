<?php

namespace App\DTOs\Response;

use App\DTOs\Models\Transaction;
use App\Models\Transaction as TransactionModel;
use Illuminate\Support\Collection;

class TransactionsResponse extends ResponseData{
    /**
     * @param Collection<TransactionModel> $transactions
     */
    public function __construct(Collection $transactions){
        /**
         * @var array{
         *     transactions: array<Transaction>,
         *     count: int
         * } $data
         */
        $data = [];
        $data['count'] = count($transactions);
        $data['transactions'] = [];
        foreach($transactions as $transaction){
            $data['transactions'][] = new Transaction($transaction);
        }
        $this->setData($data);
    }
}