<?php

namespace Tests\Feature;

use App\DTOs\Response\ApiResponse;
use App\DTOs\Response\TransactionsResponse;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function json_decode;
use function json_encode;

class ApiResponseTest extends TestCase{
    #[Test]
    public function shouldReturnTransactionsList(): void{
        $user        = new User();
        $user->id    = 1;
        $user->name  = 'John Doe';
        $user->email = 'john@doe.com';

        $transaction         = new Transaction();
        $transaction->id     = 1;
        $transaction->amount = 100;
        $transaction->user()
                    ->associate($user);
        $transaction->type        = TransactionType::Debt;
        $transaction->description = "desc";
        $transaction->date        = new Carbon("22-01-2002");

        $transactionsCollection = new Collection([$transaction]);
        $transactionsResponse   = new TransactionsResponse($transactionsCollection);

        $result = ApiResponse::withData($transactionsResponse, meta: ['meta1' => "value"]);

        $data = json_decode(json_encode($result), true);

        self::assertArrayHasKey("data", $data);
        self::assertArrayHasKey("code", $data);
        self::assertSame(200, $data["code"]);
        self::assertArrayHasKey("count", $data["data"]);
        self::assertSame(1, $data["data"]["count"]);
        self::assertCount(1, $data["data"]["transactions"]);
    }
}
