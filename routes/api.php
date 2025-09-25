<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressController;
 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Cebu address suggestions
Route::get('/addresses/cebu/suggest', [AddressController::class, 'suggest']);

// Video call API routes
Route::middleware('auth')->group(function () {
    Route::post('/video-call/answer', function (Request $request) {
        $request->validate([
            'caller_id' => 'required|integer',
            'trade_id' => 'required|integer',
            'action' => 'required|string|in:answer,decline'
        ]);
        
        // Broadcast the call response
        broadcast(new \App\Events\VideoCallResponse(
            $request->trade_id,
            $request->caller_id,
            auth()->id(),
            $request->action
        ));
        
        return response()->json(['status' => 'success']);
    });
});

 
