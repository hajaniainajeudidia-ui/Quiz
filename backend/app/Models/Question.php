<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    //
    use HasFactory, HasUuids;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'points',
        'order',
    ];

    protected $casts = [
        'points' => 'integer',
        'order' => 'integer',
    ];

    // Relations
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
