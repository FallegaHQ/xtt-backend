<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
     ->group(function(){
         Route::prefix('auth')
              ->group(function(){
                  // Public routes
                  Route::post(
                      'login',
                      [
                          AuthController::class,
                          'login',
                      ],
                  );
                  Route::post(
                      'register',
                      [
                          AuthController::class,
                          'register',
                      ],
                  );
                  Route::post(
                      'refresh',
                      [
                          AuthController::class,
                          'refresh',
                      ],
                  );

                  // Protected routes
                  Route::group(['middleware' => 'auth:api'], function(){
                      Route::post(
                          'logout',
                          [
                              AuthController::class,
                              'logout',
                          ],
                      );
                      Route::get(
                          'me',
                          [
                              AuthController::class,
                              'me',
                          ],
                      );
                  });
              });

         // Protected routes
         Route::group(['middleware' => 'auth:api'], function(){
             // Transactions
             Route::apiResource('transactions', TransactionController::class);
             Route::apiResource('balances', BalanceController::class);
         });
     });
