<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller{
    public function __construct(){}

    public function register(Request $request): JsonResponse{
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::create(
            request(
                [
                    'name',
                    'email',
                    'password',
                ],
            ),
        );

        $token = auth()->attempt(
            request(
                [
                    'email',
                    'password',
                ],
            ),
        );

        return response()->json([
                                    'status'        => 'success',
                                    'message'       => 'User created successfully',
                                    'user'          => $user,
                                    'authorization' => [
                                        'token' => $token,
                                        'type'  => 'bearer',
                                    ],
                                ]);
    }

    public function login(Request $request): JsonResponse{
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if(!$token = Auth::attempt($credentials)){
            return response()->json([
                                        'status'  => 'error',
                                        'message' => 'Unauthorized',
                                    ],
                                    401);
        }

        return response()->json([
                                    'status'        => 'success',
                                    'user'          => Auth::user(),
                                    'authorization' => [
                                        'token' => $token,
                                        'type'  => 'bearer',
                                    ],
                                ]);
    }

    public function logout(): JsonResponse{
        Auth::logout();

        return response()->json([
                                    'status'  => 'success',
                                    'message' => 'Successfully logged out',
                                ]);
    }

    public function refresh(Request $request): JsonResponse{
        return response()->json([
                                    'status'        => 'success',
                                    'user'          => Auth::user(),
                                    'authorization' => [
                                        'token' => Auth::refresh(),
                                        'type'  => 'bearer',
                                    ],
                                ]);
    }

    public function me(): JsonResponse{
        return response()->json(Auth::user());
    }
}
