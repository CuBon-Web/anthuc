<?php

namespace App\Http\Controllers;

use App\models\language\Language;
use Illuminate\Http\Request;
use Session;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $code = trim((string) $request->input('code'));
        $exists = $code !== '' && Language::where('code', $code)->where('status', 1)->exists();
        if ($exists) {
            Session::put('locale', $code);
            app()->setLocale($code);
        }

        return redirect()->back();
    }
}
