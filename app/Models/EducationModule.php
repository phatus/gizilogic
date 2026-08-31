<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationModule extends Model
{
    protected $fillable = [
        'title',
        'content',
        'substitution_recipe',
        'type',
        'target_nutrition',
    ];
}
