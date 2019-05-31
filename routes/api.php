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
    $files = Storage::disk('public')->allfiles('upload/diy/backgrounds');
    $paths = array_map("Storage::url", $files);
    return response()->json($paths);
});

Route::get("/diy/depot", "API\DiyController@getDepots");

Route::get("/diy/models", function() {
    return response()->json(config("models"));
});

Route::post("/diy/save", function(Request $request) {
    $id = $request->input('id', 0);
    if ($id == 0) {
        return response()->json(['success' => false, 'msg' => 'user id is empty']);
    }

    if (!$request->file('file1') || !$request->file('file2')) {
        return response()->json(['success' => false, 'msg' => 'please upload 2 image files']);
    }

    //$content = $request->input('content', '');
    // $content = file_get_contents('http://odm.cicisoft.com/photo1.png');
    $content1 = file_get_contents($request->file('file1')->getRealPath());
    $content2 = file_get_contents($request->file('file2')->getRealPath());
    Log::info($content1);
    Log::info($content2);
    $fileName1 = 'diy/output/' . md5($content1) . '.png';
    $fileName2 = 'diy/output/' . md5($content2) . '.png';
    if (Storage::disk('public')->put($fileName1, $content1) &&
        Storage::disk('public')->put($fileName2, $content2)) {
        return response()->json(['success' => true, 'msg' => '上传成功', 'data' => [Storage::url($fileName1), Storage::url($fileName2)]]);
    } else {
        return response()->json(['success' => false, 'msg' => '上传失败']);
    }
});

Route::post("/diy/depot/upload", "API\DiyController@upload");