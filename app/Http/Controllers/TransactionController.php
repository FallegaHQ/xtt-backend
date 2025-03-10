<?php

namespace App\Http\Controllers;

use App\DTOs\Models\Transaction as TransactionDTO;
use App\DTOs\Response\ApiResponse;
use App\DTOs\Response\TransactionsResponse;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Response;

class TransactionController extends Controller{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse{
        // Show all transactions for the authenticated user
        $transactions = Transaction::whereHas('balance', function($query){
            $query->where('user_id', auth()->user()->id);
        })
                                   ->filter($request->all())
                                   ->orderBy('date', 'desc')
                                   ->get();

        $transactionsResponse = new TransactionsResponse($transactions);

        return Response()->json(ApiResponse::withData($transactionsResponse));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse{
        $request->validate([
                               'type'       => [
                                   'required',
                                   new Enum(TransactionType::class),
                               ],
                               'amount'     => 'required|numeric|min:0.1',
                               'balance_id' => 'required|exists:balances,id',
                           ]);

        $balance = auth()
            ->user()
            ->balances()
            ->find($request->balance_id);

        if(!$balance){
            return response()->json(['error' => 'Balance not found for the user.'], 404);
        }

        // Create the transaction
        $transaction              = new Transaction();
        $transaction->type        = $request->type;
        $transaction->amount      = $request->amount;
        $transaction->description = $request->description ?? '';
        $transaction->date        = Carbon::now();
        $transaction->balance()
                    ->associate($balance);
        $transaction->save();

        return Response::json(new TransactionDTO($transaction));
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): void{
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): void{
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): void{
        //
    }
}
