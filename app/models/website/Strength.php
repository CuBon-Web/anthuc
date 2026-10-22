<?php

namespace App\models\website;

use Illuminate\Database\Eloquent\Model;

class Strength extends Model
{
    protected $table = 'strengths';
    protected $fillable = ['title', 'description', 'image', 'icon', 'sort', 'status'];
}
