<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $table = "page_contents";
    protected $guarded = ['id'];
    public function rule()
    {
        
    }
    public function savePageContent($request)
    {
        $titleText = $request->title;
        if (is_array($titleText) && isset($titleText[0]['content'])) {
            $titleText = $titleText[0]['content'];
        }
        $slug = to_slug((string) $titleText);
        $payload = [
            'title' => encodeLanguageField($request->title),
            'content' => encodeLanguageField($request->content),
            'description' => encodeLanguageField($request->description),
            'status' => $request->status,
            'type' => $request->type,
            'image' => json_encode($request->image),
            'language' => 'vi',
        ];

        $quizId = $request->quiz_id;
        $query = null;
        if ($quizId !== null && $quizId !== '') {
            $query = PageContent::where('quiz_id', $quizId)
                ->orderByRaw("language = 'vi' desc")
                ->orderBy('id')
                ->first();
        }

        if ($query) {
            $query->fill($payload);
            if ($slug !== '') {
                $query->slug = $slug;
            }
            $query->save();
            PageContent::where('quiz_id', $quizId)->where('id', '!=', $query->id)->delete();
            return $query;
        }

        $nextId = max((int) PageContent::max('id'), (int) PageContent::max('quiz_id')) + 1;
        $query = new PageContent();
        $query->quiz_id = $nextId;
        $query->slug = $slug;
        $query->fill($payload);
        $query->save();

        return $query;
    }
}
