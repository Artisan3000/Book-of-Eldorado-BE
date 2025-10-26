<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Students who have completed / attempted this quiz.
     * Requires `quiz_completions` pivot table.
     */
    public function completedBy(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'quiz_completions')
                    ->withPivot('attempted_at', 'score')
                    ->withTimestamps();
    }
}
