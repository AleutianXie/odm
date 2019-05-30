<?php

namespace App\Http\Controllers\API;

use App\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class DiyController extends Controller
{
    public function upload(Request $request)
    {
        $user_id = $request->input('id', 0);
        if ($user_id == 0) {
            return response()->json(['success' => false, 'msg' => 'user id is empty']);
        }

        if (!$request->file('file')) {
            return response()->json(['success' => false, 'msg' => 'please upload image file']);
        }

        $content = file_get_contents($request->file('file')->getRealPath());
        // $content = file_get_contents('http://odm.cicisoft.com/photo1.png');
        $fileName = 'upload/diy/depot' . md5($content) . '.png';
        if (Storage::disk('public')->put($fileName, $content)) {
            $path = Storage::url($fileName);
            Depot::create(compact('user_id', 'path'));
            return response()->json(['success' => true, 'msg' => '上传成功', 'data' => $path]);
        } else {
            return response()->json(['success' => false, 'msg' => '上传失败']);
        }
    }

    public function getDepots(Request $request)
    {
        $id = $request->input('id', 0);
        if ($id == 0) {
            $files = Storage::disk('public')->allfiles('upload/diy/depot');
            $paths = array_map("Storage::url", $files);
            return response()->json(['success' => true, 'msg' => '获取成功', 'data' => $paths]);
        } else {
            $paths = Depot::where('user_id', $id)->pluck('path')->toArray();
            return response()->json(['success' => true, 'msg' => '获取成功', 'data' => $paths]);
        }
    }
}
