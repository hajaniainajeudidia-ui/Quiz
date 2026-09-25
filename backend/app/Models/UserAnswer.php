<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAnswer extends Model
{
    //
    use HasFactory, HasUuids;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_id',
    ];

    // Relations
    public function attempt()
    {
        return $this->belongsTo(UserQuizAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
