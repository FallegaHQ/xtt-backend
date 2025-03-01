<?php

namespace Tests\Feature;

use App\DTOs\Response\ApiResponse;
use App\DTOs\Response\TransactionsResponse;
use App\Models\Balance;
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
        $transactionFactory = Transaction::factory()
                                         ->count(5);
        $balanceFactory     = Balance::factory()
                                     ->has($transactionFactory);
        $user               = User::factory()
                                  ->has($balanceFactory)
                                  ->create();

        $transactionsResponse   = new TransactionsResponse($user->balances->first()->transactions->collect());

        $result = ApiResponse::withData($transactionsResponse, meta: ['meta1' => "value"]);

        $data = json_decode(json_encode($result), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey("data", $data);
        self::assertArrayHasKey("code", $data);
        self::assertSame(200, $data["code"]);
        self::assertArrayHasKey("count", $data["data"]);
        self::assertSame(5, $data["data"]["count"]);
        self::assertCount(5, $data["data"]["transactions"]);
    }
}
