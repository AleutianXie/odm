<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

Route::get("/diy/backgrounds", function() {
    $files = Storage::disk('public')->allfiles('upload/diy');
    $paths = array_map("Storage::url", $files);
    return response()->json($paths);
});

Route::get("/diy/models", function() {
    $models = [
        [
            'name' => 'iphone',
            'model' => ['5/SE', '6/7/8', '6/7/8 plus', 'X', 'X max'],
        ],
        [
            'name' => 'huawei',
            'model' => ['Mate 20 Pro', 'Mate X', 'Mate 20', 'P30'],
        ]
    ];
    return response()->json($models);
});