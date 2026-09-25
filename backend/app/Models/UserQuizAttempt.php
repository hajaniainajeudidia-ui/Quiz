<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserQuizAttempt extends Model
{
    //
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'total_questions',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'total_questions' => 'integer',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'attempt_id');
    }
}
