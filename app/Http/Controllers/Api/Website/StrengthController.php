<?php

namespace App\Http\Controllers\Api\Website;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models\website\Strength;

class StrengthController extends Controller
{
    public function createOrUpdate(Request $request)
    {
        if ($request->data) {
            Strength::truncate();
            foreach ($request->data as $index => $value) {
                $icon = trim((string) ($value['icon'] ?? ''));
                if ($icon !== '' && !preg_match('/^bi bi-[a-z0-9-]+$/', $icon)) {
                    $icon = '';
                }

                Strength::create([
                    'title' => encodeLanguageField($value['title'] ?? ''),
                    'description' => encodeLanguageField($value['description'] ?? ''),
                    'image' => $value['image'] ?? '',
                    'icon' => $icon,
                    'sort' => $index + 1,
                    'status' => $value['status'] ?? 1,
                ]);
            }
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function list()
    {
        $data = Strength::orderBy('sort')->orderBy('id')->get();

        return response()->json(['message' => 'success', 'data' => $data], 200);
    }
}
