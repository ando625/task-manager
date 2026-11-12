<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 画面用（routes/web.php）の tasks.index などと名前が重ならないよう、API 側は api.tasks.* にする。
// Laravel 12 からは同じ名前のルートがあると先に登録された API 側が使われ、画面のリンクが /api/tasks になるため
Route::apiResource('tasks',TaskController::class)->only(['index','show'])->names('api.tasks');