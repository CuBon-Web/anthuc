<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models\Librarys;
use App\models\MessContact;
use App\Services\CloudflareImageService;

class AllController extends Controller
{
    protected $apiToken;
    protected $accountId;
    protected $cloudflareService;

    public function __construct(CloudflareImageService $cloudflareService)
    {
        $this->cloudflareService = $cloudflareService;
    }
    public function uploadImage(Request $request)
    {
        if (!$request->hasFile('img')) {
            return response()->json(['data' => 'fail'], 500);
        }

        $image = $request->file('img');
        $response = $this->cloudflareService->uploadImage($image);

        if (empty($response['success']) || empty($response['result']['variants'][0])) {
            return response()->json([
                'data' => 'fail',
                'errors' => $response['errors'] ?? null,
            ], 500);
        }

        return response()->json([
            'messenge' => 'success',
            'path' => $response['result']['variants'][0],
        ], 200);
    }

    /**
     * Upload ảnh trực tiếp lên server (không qua Cloudflare).
     * Dùng cho banner và các chỗ cần lưu local.
     */
    public function uploadImageLocal(Request $request)
    {
        if (!$request->hasFile('img')) {
            return response()->json(['data' => 'fail', 'message' => 'Không có file ảnh'], 500);
        }

        $image = $request->file('img');
        if (!$image->isValid()) {
            return response()->json(['data' => 'fail', 'message' => 'File ảnh không hợp lệ'], 500);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'];
        $mime = $image->getMimeType();
        if (!in_array($mime, $allowedMimes, true)) {
            return response()->json([
                'data' => 'fail',
                'message' => 'Chỉ chấp nhận JPG, PNG, WebP hoặc GIF',
            ], 500);
        }

        $ext = strtolower($image->getClientOriginalExtension() ?: 'jpg');
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowedExt, true)) {
            $ext = 'jpg';
        }

        $title = trim((string) $request->input('title_post', 'banner'));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($title));
        $slug = trim($slug, '-') ?: 'banner';
        $filename = $slug . '-' . time() . '-' . mt_rand(1000, 9999) . '.' . $ext;

        $folder = public_path('uploads/banner');
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $image->move($folder, $filename);
        $path = '/uploads/banner/' . $filename;

        return response()->json([
            'messenge' => 'success',
            'path' => $path,
        ], 200);
    }
    public function uploadImageMulti(Request $request)
    {
        $uploadId = [];
        if($files = $request->file('file')){
            foreach($request->file('file') as $key => $file){
                $name = rand().$file->getClientOriginalName();
                $fielname = $file->move('uploads/imagesMuli/', $name);
                $uploadId[] = '/uploads/images/'.$name;
            }
        }
        return response()->json([
            'messenge' => 'success',
            'path' => $uploadId
        ],200);
    }
    public function fileStore(Request $request)
    {
        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);
         
        $insert['title'] = $generated_new_name;
        return response()->json([
            'messenge' => 'success',
            'path' => $insert
        ],200);
    }
    public function listMesscontact(Request $request)
    {
        $keyword = $request->keyword;
        if($keyword == ""){
            $data = MessContact::orderBy('id','DESC')->get();
        }else{
            $data = MessContact::where('title', 'LIKE', '%'.$keyword.'%')->orderBy('id','DESC')->get()->toArray();
        }
        return response()->json([
            'data' => $data,
            'message' => 'success'
        ]);
    }
}
