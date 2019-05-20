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

Route::get("/diy/depot", function() {
    $files = Storage::disk('public')->allfiles('upload/diy/depot');
    $paths = array_map("Storage::url", $files);
    return response()->json($paths);
});

Route::get("/diy/models", function() {
    return response()->json(config("models"));
});

Route::post("/diy/save", function(Request $request) {
    $id = $request->input('id', 0);
    if ($id == 0) {
        return response()->json(['success' => false, 'msg' => 'user id is empty']);
    }
    $content = $request->input('content', '');
    // $content = file_get_contents('http://odm.cicisoft.com/photo1.png');
    if ($content == '') {
        return response()->json(['success' => false, 'msg' => 'file content is empty']);
    }
    $fileName = 'diy/output/' . md5($content) . '.png';
    if (Storage::disk('public')->put($fileName, $content)) {
        return response()->json(['success' => true, 'msg' => '上传成功', 'data' => Storage::url($fileName)]);
    } else {
        return response()->json(['success' => false, 'msg' => '上传失败']);
    }
});