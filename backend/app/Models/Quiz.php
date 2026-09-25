<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    //
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'time_limit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(UserQuizAttempt::class);
    }
}
