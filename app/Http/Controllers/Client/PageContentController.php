<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models\PageContent;
use Session;

class PageContentController extends Controller
{
    public function detail($slug)
    {
    	$data['pagecontentdetail'] = PageContent::where([
            'slug' => $slug,
            'status' => 1,
        ])->orderByRaw("language = 'vi' desc")->orderBy('id')->first();
        if (!$data['pagecontentdetail']) {
            abort(404);
        }
    	return view('pageContent',$data);
    }
}
