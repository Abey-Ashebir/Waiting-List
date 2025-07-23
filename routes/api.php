<?php

use App\Http\Controllers\WaitingListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('waiting-list', [WaitingListController::class, 'index']);
    Route::post('waiting-list', [WaitingListController::class, 'store']);
    Route::put('waiting-list/{id}', [WaitingListController::class, 'update']);
    Route::delete('waiting-list/{id}', [WaitingListController::class, 'destroy']);
    
    Route::get('waiting-list/stats', [WaitingListController::class, 'stats']);
    Route::get('waiting-list/export', [WaitingListController::class, 'export']);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token]);
});