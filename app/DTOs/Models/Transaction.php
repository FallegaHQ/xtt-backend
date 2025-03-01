<?php

namespace App\DTOs\Models;

use App\Models\Transaction as TransactionModel;
use Carbon\Carbon;
use JsonSerializable;

readonly class Transaction implements JsonSerializable{
    private int    $id;
    //private User   $user;
    private string $type;
    private string $description;
    private Carbon $date;
    private float  $amount;

    public function __construct(TransactionModel $transaction){
        $this->id          = $transaction->id;
        //$this->user        = new User($transaction->user);
        $this->type        = $transaction->type->value;
        $this->description = $transaction->description;
        $this->date        = $transaction->date;
        $this->amount      = $transaction->amount;
    }

    public function jsonSerialize(): array{
        return [
            'id'          => $this->id,
            //'user'        => $this->user,
            'type'        => $this->type,
            'description' => $this->description,
            'date'        => $this->date->format('Y-m-d H:i:s'),
            'amount'      => number_format($this->amount, 2),
        ];
    }

}