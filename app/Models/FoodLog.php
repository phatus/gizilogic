<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodLog extends Model
{
    protected $fillable = [
        'user_id',
        'photo_path',
        'detection_results',
        'nutrition_status',
    ];

    protected function casts(): array
    {
        return [
            'detection_results' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
