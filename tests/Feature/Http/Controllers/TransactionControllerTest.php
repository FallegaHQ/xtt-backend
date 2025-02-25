<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionControllerTest extends TestCase{
    use RefreshDatabase;

    protected User $user;
    protected User $user2;

    #[Test]
    public function shouldIndexReturnsUserTransactions(): void{
        // Create some transactions for the user
        $transactions = Transaction::factory()
                                   ->count(3)
                                   ->create([
                                                'user_id' => $this->user->id,
                                            ]);

        // Create transactions for another user that shouldn't be returned
        Transaction::factory()
                   ->count(2)
                   ->create([
                                'user_id' => $this->user2->id,
                            ]);

        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                                           'data' => [
                                               'count',
                                               'transactions',
                                           ],
                                           'code',
                                           'message',
                                           'timestamp',
                                       ]);

        // Check that we only get the correct number of transactions
        $responseData = $response->json('data.transactions');
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function shouldIndexWithFilters(): void{
        // Create transactions with different descriptions and dates
        Transaction::factory()
                   ->create([
                                'user_id'     => $this->user->id,
                                'description' => 'groceries',
                                'date'        => now()->subDays(5),
                            ]);

        Transaction::factory()
                   ->create([
                                'user_id'     => $this->user->id,
                                'description' => 'rent',
                                'date'        => now()->subDays(2),
                            ]);

        // Filter by description
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/transactions?description=groceries');

        $response->assertStatus(200);
        $responseData = $response->json('data.transactions');
        $this->assertCount(1, $responseData);
        $this->assertEquals('groceries', $responseData[0]['description']);

        // Filter by date
        $dateFilter = now()
            ->subDays(3)
            ->toDateString();
        $response   = $this->actingAs($this->user)
                           ->getJson("/api/v1/transactions?date=after:{$dateFilter}");

        $response->assertStatus(200);
        $responseData = $response->json('data.transactions');
        $this->assertCount(1, $responseData);
        $this->assertEquals('rent', $responseData[0]['description']);
    }

    #[Test]
    public function shouldStoreCreatesNewTransaction(): void{
        $transactionData = [
            'type'        => TransactionType::Debt->value,
            'amount'      => 50.75,
            'description' => 'Test transaction',
        ];

        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/transactions', $transactionData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                                           'id',
                                           'user',
                                           'type',
                                           'amount',
                                           'description',
                                           'date',
                                       ]);

        // Check the transaction was created in the database
        $this->assertDatabaseHas('transactions', [
            'user_id'     => $this->user->id,
            'type'        => TransactionType::Debt->value,
            'amount'      => 50.75,
            'description' => 'Test transaction',
        ]);
    }

    #[Test]
    public function shouldStoreValidatesRequiredFields(): void{
        // Missing type
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/transactions', [
                             'amount' => 50.75,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['type']);

        // Missing amount
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/transactions', [
                             'type' => TransactionType::Debt->value,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function shouldStoreValidatesAmountIsNumericAndPositive(): void{
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/transactions', [
                             'type'   => TransactionType::Debt->value,
                             'amount' => 'invalid',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);

        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/transactions', [
                             'type'   => TransactionType::Debt->value,
                             'amount' => 0,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function shouldStoreValidatesTransactionType(): void{
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/transactions', [
                             'type'   => 'invalid_type',
                             'amount' => 50.75,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['type']);
    }

    #[Test]
    public function shouldAuthenticationRequired(): void{
        // Try to access without authentication
        $response = $this->getJson('/api/v1/transactions');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/transactions', [
            'type'   => TransactionType::Other->value,
            'amount' => 50.75,
        ]);
        $response->assertStatus(401);
    }

    protected function setUp(): void{
        parent::setUp();

        // Create a user for testing
        $this->user  = User::factory()
                           ->create();
        $this->user2 = User::factory()
                           ->create();
    }
}
