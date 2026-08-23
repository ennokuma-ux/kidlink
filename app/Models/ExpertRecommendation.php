<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertRecommendation extends Model
{
    use HasFactory;

    protected $table = 'expert_recommendations';

    protected $fillable = [
        'title',
        'message',
        'severity',
    ];
}
