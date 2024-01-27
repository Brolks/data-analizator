<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Группа маршрутов для API
Route::get('/', [FileUploadController::class, 'createForm']);
Route::get('/files', [FileUploadController::class, 'getFiles']); // Получение списка файлов
Route::post('/files', [FileUploadController::class, 'fileUpload']); // Загрузка файла
Route::get('/files/{id}', [FileUploadController::class, 'fileLoad']); // Получение информации о файле
Route::delete('/files/{id}', [FileUploadController::class, 'deleteFile']); // Удаление файла